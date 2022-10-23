<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Controller;

class Controller_Work_Newsletter extends Controller
{
	/**	@var	Logic_Newsletter_Editor		$logic 		Instance of newsletter editor logic */
	protected $logic;
	protected $session;
	protected $request;
	protected $messenger;
	protected $moduleConfig;
	protected $limiter;
	protected $filterPrefix	= 'filter_work_newsletter_';

	public function add()
	{
		$words		= (object) $this->getWords( 'add' );
		if( $this->request->getMethod()->isPost() ){
			$data	= array(
				'creatorId'				=> $this->session->get( 'auth_user_id' ),
				'newsletterTemplateId'	=> $this->request->get( 'newsletterTemplateId' ),
			);
			if( ( $newsletterId = $this->request->get( 'newsletterId' ) ) ){
				$data	= (array) $this->logic->getNewsletter( $newsletterId );
				unset( $data['status'] );
				unset( $data['modifiedAt'] );
				unset( $data['sentAt'] );
			}
			else if( $this->request->get( 'newsletterTemplateId' ) ){
				$template	= $this->logic->getTemplate( $this->request->get( 'newsletterTemplateId' ) );
				$data		= array_merge( $data, array(
					'senderName'		=> $template->senderName,
					'senderAddress'		=> $template->senderAddress,
				) );
			}
			$data	= array_merge( $data, array(
				'creatorId'			=> (int) $this->session->get( 'auth_user_id' ),
				'title'				=> $this->request->get( 'title' ),
				'subject'			=> trim( $this->request->get( 'subject' ) ),
				'heading'			=> trim( $this->request->get( 'heading' ) ),
//				'senderName'		=> trim( $this->request->get( 'senderName' ) ),
//				'senderAddress'		=> trim( $this->request->get( 'senderAddress' ) ),
				'trackingCode'		=> trim( $this->request->get( 'trackingCode' ) ),
				'createdAt'			=> time(),
			) );
			if( !strlen( $data['subject'] ) )
				$data['subject']	= $data['title'];
			if( $this->logic->getNewsletters( ['title' => $data['title']] ) ){
				$this->messenger->noteError( $words->msgErrorTitleExists );
			}
			else{
				unset( $data['newsletterId'] );
				$newsletterId		= $this->logic->addNewsletter( $data );
				$this->messenger->noteSuccess( $words->msgSuccess );
				$this->setContentTab( $newsletterId, 1 );
				$this->restart( 'edit/'.$newsletterId, TRUE );
			}
		}
		$templates		= $this->logic->getTemplates( ['status' => '> 0'], ['title' => 'ASC'] );
		if( !$templates ){
			$this->messenger->noteNotice( 'Es ist noch keine verwendbare Vorlage vorhanden. Weiterleitung zu den Vorlagen.' );
			$this->restart( 'work/newsletter/template' );
		}

		$newsletters	= $this->logic->getNewsletters( [], ['title' => 'ASC'] );
		$newsletter		= (object) array(
			'newsletterTemplateId'	=> (int) $this->request->get( 'newsletterTemplateId' ),
			'newsletterId'			=> (int) $this->request->get( 'newsletterId' ),
			'creatorId'				=> (int) $this->session->get( 'auth_user_id' ),
			'title'					=> trim( $this->request->get( 'title' ) ),
			'senderAddress'			=> trim( $this->request->get( 'senderAddress' ) ),
			'senderName'			=> trim( $this->request->get( 'senderName' ) ),
			'heading'				=> trim( $this->request->get( 'heading' ) ),
			'subject'				=> trim( $this->request->get( 'subject' ) ),
			'trackingCode'			=> trim( $this->request->get( 'trackingCode' ) ),
		);
		$this->addData( 'templates', $templates );
		$this->addData( 'newsletters', $newsletters );
		$this->addData( 'newsletter', $newsletter );
	}

	public function dequeueLetter( $readerLetterId )
	{
		$letter		= $this->logic->getReaderLetter( $readerLetterId );
		$this->logic->dequeue( $readerLetterId );
		$this->restart( 'edit/'.$letter->newsletterId, TRUE );
	}

	public function edit( $newsletterId )
	{
		$this->checkNewsletterId( $newsletterId );
		$words		= (object) $this->getWords( 'edit' );
		if( $this->request->has( 'save' ) ){
			$newsletter	= $this->logic->getNewsletter( $newsletterId );
//			if( (int) $newsletter->status !== Model_Newsletter::STATUS_NEW ){
//				$this->messenger->noteError( 'Changes denied since already sent.' );
//				$this->restart( './work/newsletter' );
//			}
			$data		= $this->request->getAll();
			if( isset( $data['subject'] ) && isset( $data['title'] ) )
				if( !strlen( $data['subject'] ) && strlen( $data['title'] ) )
					$data['subject']	= $data['title'];

			if( isset( $data['html'] ) && strlen( $data['html'] ) ){
				$data['html']	= View_Helper_TinyMce::tidyHtml( $data['html'] );
				if( $newsletter->generatePlain ){
					$data['plain']	= $this->logic->convertHtmlToText( $data['html'] );
				}
			}
			if( $this->session->get( 'work.newsletter.content.tab' ) == 2 ){
				if( !$this->request->get( 'generatePlain' ) )
					$data['generatePlain']	= 0;
				else
					$data['plain']	= $this->logic->convertHtmlToText( $newsletter->html );
			}
			if( !isset( $data['status'] ) )
				$data['status']	= Model_Newsletter::STATUS_NEW;
			$this->logic->editNewsletter( $newsletterId, $data );
			$this->messenger->noteSuccess( $words->msgSuccess );
			$url	= './work/newsletter/edit/'.$newsletterId;
			$this->restart( $this->request->has( 'forwardTo' ) ? $this->request->get( 'forwardTo' ) : $url );
		}
		$newsletter		= $this->logic->getNewsletter( $newsletterId );
		$template		= $this->logic->getTemplate( $newsletter->newsletterTemplateId );
		$templates		= $this->logic->getTemplates( ['status' => '> 0'], ['title' => 'ASC'] );
		$groups			= [];
		foreach( $this->logic->getGroups( ['status' => 1], ['title' => 'ASC'] ) as $group ){
			$group->readers	= $this->logic->getGroupReaders( $group->newsletterGroupId );
			$groups[$group->newsletterGroupId]	= $group;
		}

		$model			= new Model_Newsletter_Reader_Group( $this->env );
		$groupIds		= $this->request->get( 'groupIds' );
		if( !$groupIds )
			$groupIds	= [];

		$readers		= [];
		if( $groupIds ){
			foreach( $groupIds as $groupId )
				foreach( $this->logic->getGroupReaders( $groupId ) as $reader )
					$readers[$reader->newsletterReaderId]	= $reader;
		}

		$queues		= $this->logic->getQueuesOfNewsletter( $newsletterId );

		$letterQueue	= $this->logic->getReaderLetters( array(
			'newsletterId'	=> $newsletterId,
			'status'		=> 0
		) );
		$letterHistory	= $this->logic->getReaderLetters( array(
			'newsletterId'	=> $newsletterId,
			'status'		=> '!= 0'
		) );

		$isUsed	= $newsletter->status >= Model_Newsletter::STATUS_SENT;
		$this->addData( 'isUsed', $isUsed );
		$this->addData( 'newsletterId', $newsletterId );
		$this->addData( 'newsletters', $this->logic->getNewsletters() );
		$this->addData( 'newsletter', $newsletter );
		$this->addData( 'templates', $templates );
		$this->addData( 'template', $template );
		$this->addData( 'groups', $groups );
		$this->addData( 'groupIds', $groupIds );
		$this->addData( 'readers', $readers );
		$this->addData( 'queues', $queues );
		$this->addData( 'letterQueue', $letterQueue );
		$this->addData( 'letterHistory', $letterHistory );
		$this->addData( 'styles', $this->logic->getTemplateAttributeList( $newsletter->newsletterTemplateId, 'styles' ) );
		$this->addData( 'askForReady', $this->request->has( 'askForReady' ) );
	}

	public function editFull( $newsletterId )
	{
		$this->checkNewsletterId( $newsletterId );
		$newsletter		= $this->logic->getNewsletter( $newsletterId );
		$template		= $this->logic->getTemplate( $newsletter->newsletterTemplateId );

		$this->addData( 'newsletterId', $newsletterId );
		$this->addData( 'newsletter', $newsletter );
		$this->addData( 'template', $template );
		$this->addData( 'styles', $this->logic->getTemplateAttributeList( $newsletter->newsletterTemplateId, 'styles' ) );
	}

	public function enqueue( $newsletterId )
	{
		$this->checkNewsletterId( $newsletterId );
		$words		= (object) $this->getWords( 'enqueue' );
		$readerIds	= $this->request->get( 'readerIds' );
		$creatorId	= $this->session->get( 'auth_user_id' );						//  get current user

		if( !( $queueId = $this->session->get( 'queueId-'.$newsletterId ) ) ){		//  no queue within this session yet
			$queueId	= $this->logic->createQueue( $newsletterId, $creatorId );	//  create a new queue
			$this->session->set( 'queueId-'.$newsletterId, $queueId );				//  note queue in session
		}

		$negativeStatues	= array(
			Model_Newsletter_Queue::STATUS_REJECTED,
			Model_Newsletter_Queue::STATUS_CANCELLED,
		);
		$newsletter	= $this->logic->getNewsletter( $newsletterId );					//  get newsletter data object for later
		$queue		= $this->logic->getQueue( $queueId );							//  get queue data object
		if( in_array( $queue->status, $negativeStatues ) ){							//  queue has been rejected or cancelled
			$queueId	= $this->logic->createQueue( $newsletterId, $creatorId );	//  create a new queue
			$this->session->set( 'queueId-'.$newsletterId, $queueId );				//  note queue in session
			$queue		= $this->logic->getQueue( $queueId );						//  get queue data object
		}

		if( !is_array( $readerIds ) || !count( $readerIds ) ){
			$this->messenger->noteError( 'No receivers selected.' );
			$this->restart( 'edit/'.$newsletterId, TRUE );
		}


		$numberSent		= 0;
		$numberSkipped	= 0;
		foreach( $readerIds as $readerId )
			$this->logic->enqueue( $queueId, $readerId, $newsletterId, TRUE ) ? $numberSent++ : $numberSkipped++;	//
		if( $numberSent ){
			$this->messenger->noteSuccess( $words->msgSuccess, $numberSent );
			$this->logic->editNewsletter( $newsletterId, array( 'status' => Model_Newsletter::STATUS_SENT, 'sentAt' => time() ) );
			$this->logic->setTemplateStatus( $newsletter->newsletterTemplateId, Model_Newsletter_Template::STATUS_USED );
		}
		if( $numberSkipped )
			$this->messenger->noteNotice( $words->msgNoticeSkipped, $numberSkipped );
		if( $queue->status == Model_Newsletter_Queue::STATUS_DONE )
			$this->logic->setQueueStatus( $queueId, Model_Newsletter_Queue::STATUS_RUNNING );
//		$this->restart( 'edit/'.$newsletterId, TRUE );
		$this->restart( 'setContentTab/'.$newsletterId.'/5', TRUE );
	}

	public function filter( $reset = NULL )
	{
		if( $reset ){
			$this->session->remove( $this->filterPrefix.'title' );
			$this->session->remove( $this->filterPrefix.'status' );
			$this->session->remove( $this->filterPrefix.'limit' );
		}
		$this->session->set( $this->filterPrefix.'title', $this->request->get( 'title' ) );
		$this->session->set( $this->filterPrefix.'status', $this->request->get( 'status' ) );
		$this->session->set( $this->filterPrefix.'limit', $this->request->get( 'limit' ) );
		$this->restart( NULL, TRUE );
	}

	public function index( $page = 0 )
	{
		$templates		= $this->logic->getTemplates( ['status' => '> 0'], ['title' => 'ASC'] );
		$newsletters	= $this->logic->getNewsletters( [], ['title' => 'ASC'] );
		$this->addData( 'addTemplates', $templates );
		$this->addData( 'addNewsletters', $newsletters );

		$filterTitle	= $this->session->get( $this->filterPrefix.'title' );
		$filterStatus	= $this->session->get( $this->filterPrefix.'status' );
		$filterLimit	= $this->session->get( $this->filterPrefix.'limit' );

		$conditions	= [];
		if( strlen( $filterTitle ) )
			$conditions['title']	= '%'.$filterTitle.'%';
		if( strlen( $filterStatus ) )
			$conditions['status']	= $filterStatus;


		$orders		= ['newsletterId' => 'DESC'];
		$limits		= [$page * $filterLimit, $filterLimit];
		$this->addData( 'newsletters', $this->logic->getNewsletters( $conditions, $orders, $limits ) );
		$this->addData( 'page', $page );
		$this->addData( 'pages', ceil( $this->logic->countNewsletters( $conditions ) / $filterLimit ) );
		$this->addData( 'total', ceil( $this->logic->countNewsletters() / $filterLimit ) );
		$this->addData( 'filterTitle', $filterTitle );
		$this->addData( 'filterStatus', $filterStatus );
		$this->addData( 'filterLimit', $filterLimit );
	}

	public function preview( $format, $newsletterId, $simulateOffline = FALSE )
	{
		$this->checkNewsletterId( $newsletterId );
		$words		= (object) $this->getWords( 'preview' );
		$newsletter	= $this->logic->getNewsletter( $newsletterId );
		$template	= $this->logic->getTemplate( $newsletter->newsletterTemplateId );
		$baseUri	= $this->env->url;
		if( $this->env->getModules()->has( 'Resource_Frontend' ) )
			$baseUri	= Logic_Frontend::getInstance( $this->env )->getUri();

		$urlOptOut	= $baseUri.'info/newsletter/unregister';
		$urlTrack	= $baseUri.'info/newsletter/track/0';
		$data		= array(
			'nr'				=> $newsletter->newsletterId,
			'title'				=> $newsletter->heading,
			'content'			=> $newsletter->plain,
			'salutation'		=> $words->salutation,
			'prefix'			=> $words->prefix,
			'firstname'			=> $words->firstname,
			'surname'			=> $words->surname,
			'registerDate'		=> $words->registerDate,
			'registerTime'		=> $words->registerTime,
			'linkTracking'		=> $urlTrack,
			'linkUnregister'	=> "javascript:alert('".$words->alertDisabledInPreview."')",
			'linkView'			=> "javascript:alert('".$words->alertDisabledInPreview."')",
			'preview'			=> TRUE,
		);
		$mail	= new View_Helper_Newsletter_Mail( $this->env );
		$mail->setTemplateId( $newsletter->newsletterTemplateId );
		switch( strtolower( $format ) ){
			case 'text':
				$data['content']		= $newsletter->plain;
				$data['linkUnregister']	= '('.$words->alertDisabledInPreview.')';
				$data['linkView']		= '('.$words->alertDisabledInPreview.')';
				$mail->setMode( View_Helper_Newsletter_Mail::MODE_PLAIN );
				$mail->setData( $data );
				$content	= HtmlTag::create( 'xmp', $mail->render() );
				break;
			case 'html':
				$data['content']		= $newsletter->html;
				$mail->setMode( View_Helper_Newsletter_Mail::MODE_HTML );
				$mail->setData( $data );
				$content	= $mail->render( $data );
				if( $simulateOffline )
					$content	= preg_replace( '@https?://@', "noschema://", $content );
				break;
			default:
				throw new InvalidArgumentException( 'Format "'.$format.'" is not supported' );
		}
		print( $content );
		exit;
		return $content;
	}

	/**
	 *	Resends a readers newletter.
	 *	@access		public
	 *	@param		integer		$readerLetterId		ID of former send reader newsletter
	 *	@return		void
	 *	@todo		extend by queue support, otherwise this wont work anymore
	 */
	public function sendLetter( $readerLetterId )
	{
		$letter		= $this->logic->getReaderLetter( $readerLetterId );
		if( !$letter )
			$this->messenger->noteError( 'Invalid letter ID.' );
		else {
			$reader		= $this->logic->getReader( $letter->newsletterReaderId );
			$this->logic->enqueue( $letter->newsletterQueueId, $letter->newsletterReaderId, $letter->newsletterId, TRUE );
			$this->messenger->noteSuccess( 'Newsletter sent to '.$reader->firstname.' '.$reader->surname.' <cite>&lt;'.$reader->email.'&gt;</cite>.' );
		}
		$this->restart( 'edit/'.$letter->newsletterId, TRUE );
	}

	public function setContentTab( $newsletterId, $tabKey )
	{
		$this->checkNewsletterId( $newsletterId );
		$this->session->set( 'work.newsletter.content.tab', $tabKey );
		$this->restart( './work/newsletter/edit/'.$newsletterId );
	}

	public function setStatus( $newsletterId, $status )
	{
		$this->checkNewsletterId( $newsletterId );
		$urlForwardTo	= $this->request->get( 'forwardTo' );

		$words		= (object) $this->getWords( 'edit' );
		if( !$this->logic->checkNewsletterId( $newsletterId ) ){
			$this->messenger->noteError( $words->msgErrorInvalidId, $newsletterId );
			$this->restart( './work/newsletter' );
		}
		if( !in_array( (int) $status, [-1, 0, 1, 2] ) ){
			$this->messenger->noteError( 'Invalid status.', $newsletterId );
			$this->restart( './work/newsletter' );
		}
		$this->logic->editNewsletter( $newsletterId, array( 'status' => (int) $status ) );
		$this->messenger->noteSuccess( $words->msgSuccess );
		$urlForwardTo	= $this->request->get( 'forwardTo' );
		$this->restart( $urlForwardTo ? $urlForwardTo : 'edit/'.$newsletterId, TRUE );
	}

	public function test( $newsletterId )
	{
		$this->checkNewsletterId( $newsletterId );
		$w			= (object) $this->getWords( 'test' );
		$readerIds	= $this->request->get( 'readerIds' );
		if( !is_array( $readerIds ) || !count( $readerIds ) ){
			$this->messenger->noteError( 'No receivers selected.' );
			$this->restart( 'edit/'.$newsletterId, TRUE );
		}
		$readerLetterIds	= [];
		foreach( $readerIds as $readerId )
			$this->logic->sendTestLetter( $newsletterId, $readerId );
		$this->messenger->noteSuccess( $w->msgSuccess, count( $readerIds ) );
/*		$this->logic->editNewsletter( $newsletterId, array(										//  update newsletter
			'status' 	=> Model_Newsletter_Template::STATUS_READY,								//  ... by setting to READY
			'modifiedAt' => time()																//  ... and not change time
		) );*/
		$this->restart( 'edit/'.$newsletterId.'?askForReady', TRUE );
//		$this->restart( 'setContentTab/'.$newsletterId.'/4', TRUE );
	}

	public function view( $readerLetterId )
	{
		try{
			$letter		= $this->logic->getReaderLetter( $readerLetterId );
			$newsletter	= $this->logic->getNewsletter( $letter->newsletterId );
			$helper		= new View_Helper_Newsletter( $this->env, $newsletter->newsletterTemplateId );
			$data		= $helper->prepareReaderDataForLetter( $readerLetterId, TRUE );
			$mail		= $helper->renderNewsletterHtml( $letter->newsletterId, $letter->newsletterReaderId, $data );
			print( $mail );
			die;
		}
		catch( Exception $e ){
			UI_HTML_Exception_Page::display( $e );
			die;
			$this->messenger->noteError( 'Der gewählte Newsletter existiert nicht mehr. Weiterleitung zur Übersicht.' );
			$this->restart( NULL, TRUE );
		}
	}

	public function remove( $newsletterId )
	{
		$this->checkNewsletterId( $newsletterId );
		$this->logic->removeNewsletter( $newsletterId );
		$this->messenger->noteSuccess( 'Kampagne entfernt.' );
		$this->restart( NULL, TRUE );
	}

	protected function __onInit()
	{
		$this->logic		= new Logic_Newsletter_Editor( $this->env );
		$this->session		= $this->env->getSession();
		$this->request		= $this->env->getRequest();
		$this->messenger	= $this->env->getMessenger();
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.work_newsletter.', TRUE );
		$this->addData( 'moduleConfig', $this->moduleConfig );
		$this->addData( 'tabbedLinks', $this->moduleConfig->get( 'tabbedLinks' ) );

		$this->frontendUrl		= $this->env->url;
		if( $this->env->getModules()->has( 'Resource_Frontend' ) )
			$this->frontendUrl	= Logic_Frontend::getInstance( $this->env )->getUri();
		$this->addData( 'frontendUrl', $this->frontendUrl );

		if( $this->env->getModules()->has( 'Resource_Limiter' ) )
			$this->limiter	= Logic_Limiter::getInstance( $this->env );
		$this->addData( 'limiter', $this->limiter );

		if( !count( $this->logic->getTemplates( ['status' => '> 0'] ) ) ){
			$this->messenger->noteNotice( '<b>Keine verwendbare Vorlage vorhanden.</b><br/>Bitte zuerst eine Vorlage erstellen und auf "bereit" stellen!' );
			$this->restart( 'template', TRUE );
		}

		if( $this->session->get( $this->filterPrefix.'limit' ) < 1 )
			$this->session->set( $this->filterPrefix.'limit', 10 );
	}

	protected function checkNewsletterId( $newsletterId )
	{
		if( !$this->logic->checkNewsletterId( $newsletterId, FALSE ) ){
			$words		= (object) $this->getWords( 'edit' );
			$this->messenger->noteError( $words->msgErrorInvalidId, $newsletterId );
			$this->restart( NULL, TRUE );
		}
	}
}
