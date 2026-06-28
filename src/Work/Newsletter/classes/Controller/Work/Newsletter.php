<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\Common\UI\HTML\Exception\Page as HtmlExceptionPage;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;

class Controller_Work_Newsletter extends Controller
{
	/**	@var	Logic_Newsletter_Editor		$logic 		Instance of newsletter editor logic */
	protected Logic_Newsletter_Editor $logic;
	protected Dictionary $session;
	protected HttpRequest $request;
	protected MessengerResource $messenger;
	protected Dictionary $moduleConfig;
	protected ?Logic_Limiter $limiter			= NULL;
	protected string $filterPrefix				= 'filter_work_newsletter_';
	protected string $frontendUrl;
	protected bool $useUserGroupRelations		= FALSE;

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function add(): void
	{
		if( $this->request->getMethod()->isPost() )
			$this->handleAddRequest();
		$this->prepareAddData();
	}

	/**
	 *	@param		string		$readerLetterId
	 *	@return		void
	 */
	public function dequeueLetter( string $readerLetterId ): void
	{
		$letter		= $this->logic->getReaderLetter( $readerLetterId );
		$this->logic->dequeue( $readerLetterId );
		$this->restart( 'edit/'.$letter->newsletterId, TRUE );
	}

	/**
	 *	@param		string		$newsletterId
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function edit( string $newsletterId ): void
	{
		$this->checkNewsletterId( $newsletterId );

		$currentTab	= (int) $this->session->get( 'work.newsletter.content.tab', 1 );
		if( 3 === $currentTab )
			if( !$this->env->getAcl()->has( 'work/newsletter', 'test' ) )
				$this->setContentTab( $newsletterId, '2' );
		if( 4 === $currentTab )
			if( !$this->env->getAcl()->has( 'work/newsletter', 'enqueue' ) )
				$this->setContentTab( $newsletterId, '3' );

		if( $this->request->getMethod()->isPost() && $this->request->has( 'save' ) )
			$this->handleEditRequest( $newsletterId );
		$this->prepareEditData( $newsletterId );
	}

	/**
	 *	@param		string		$newsletterId
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function editFull( string $newsletterId ): void
	{
		$this->checkNewsletterId( $newsletterId );
		$newsletter		= $this->logic->getNewsletter( $newsletterId );
		$template		= $this->logic->getTemplate( $newsletter->newsletterTemplateId );

		$this->addData( 'newsletterId', $newsletterId );
		$this->addData( 'newsletter', $newsletter );
		$this->addData( 'template', $template );
		$this->addData( 'styles', $this->logic->getTemplateAttributeList( $newsletter->newsletterTemplateId, 'styles' ) );
	}

	/**
	 *	@param		string		$newsletterId
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function enqueue( string $newsletterId ): void
	{
		$this->checkNewsletterId( $newsletterId );
		$words		= (object) $this->getWords( 'enqueue' );
		$readerIds	= $this->request->get( 'readerIds' );
		$creatorId	= $this->session->get( Logic_Authentication::$sessionKeyAuthUserId );			//  get current user

		//  calculate send time, if configured
		$toBeSentAt	= 0;
		$sendLater	= $this->request->has( 'sendLater' );										//  get checkbox status
		$sendAtDate	= trim( $this->request->get( 'sendAt_date', '' ) );					//  get input date
		$sendAtTime	= trim( $this->request->get( 'sendAt_time', '' ) );					//  get input time
		if( $sendLater && '' !== $sendAtDate && '' !== $sendAtTime )								//  checkbox enabled and date and time are set
			$toBeSentAt	= strtotime( $sendAtDate.' '.$sendAtTime.':00' );					//  calculate timestamp of given date and time

		//  create queue if not done yet in this session for this newsletter
		$queueId = (int) $this->session->get( 'queueId-'.$newsletterId, '' );
		if( 0 === $queueId ){																		//  no queue within this session yet
			$queueId	= $this->logic->createQueue( $newsletterId, $creatorId, $toBeSentAt );		//  create a new queue
			$this->session->set( 'queueId-'.$newsletterId, $queueId );								//  note queue in session
		}

		$negativeStatues	= [
			Model_Newsletter_Queue::STATUS_REJECTED,
			Model_Newsletter_Queue::STATUS_CANCELLED,
		];
		$newsletter	= $this->logic->getNewsletter( $newsletterId );					//  get newsletter data object for later
		$queue		= $this->logic->getQueue( $queueId );							//  get queue data object
		if( in_array( $queue->status, $negativeStatues ) ){							//  queue has been rejected or cancelled
			$queueId	= $this->logic->createQueue( $newsletterId, $creatorId );	//  create a new queue
			$this->session->set( 'queueId-'.$newsletterId, $queueId );				//  note queue in session
			$queue		= $this->logic->getQueue( $queueId );						//  get queue data object
		}

		if( !is_array( $readerIds ) || [] === $readerIds ){
			$this->messenger->noteError( 'No receivers selected.' );
			$this->restart( 'edit/'.$newsletterId, TRUE );
		}
		if( ['*'] === $readerIds ){
			$selectedGroupIds	= $this->request->get( 'groupIds', '' );
			if( '' === $selectedGroupIds )
				$this->restart( 'edit/'.$newsletterId );
			$groupIds	= explode( ',', $selectedGroupIds );
			$readerIds	= [];
			foreach( $groupIds as $groupId )
				foreach( $this->logic->getGroupReaders( $groupId ) as $reader )
					$readerIds[]	= $reader->newsletterReaderId;
		}

		$numberSent		= 0;
		$numberSkipped	= 0;
		foreach( $readerIds as $readerId )
			$this->logic->enqueue( $queueId, $readerId, $newsletterId, TRUE ) ? $numberSent++ : $numberSkipped++;	//
		if( $numberSent ){
			$this->messenger->noteSuccess( $words->msgSuccess, $numberSent );
			$this->logic->editNewsletter( $newsletterId, [
				'status'	=> Model_Newsletter::STATUS_SENT,
				'sentAt'	=> time()
			] );
			$this->logic->setTemplateStatus( $newsletter->newsletterTemplateId, Model_Newsletter_Template::STATUS_USED );
		}
		if( $numberSkipped )
			$this->messenger->noteNotice( $words->msgNoticeSkipped, $numberSkipped );
		if( $queue->status == Model_Newsletter_Queue::STATUS_DONE )
			$this->logic->setQueueStatus( $queueId, Model_Newsletter_Queue::STATUS_RUNNING );
//		$this->restart( 'edit/'.$newsletterId, TRUE );
		$this->restart( 'setContentTab/'.$newsletterId.'/5', TRUE );
	}

	public function filter( $reset = NULL ): void
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

	/**
	 *	@param		int		$page
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function index( int $page = 0 ): void
	{
		$templates		= $this->logic->getTemplates( ['status' => '> 0'], ['title' => 'ASC'] );
		$newsletters	= $this->logic->getNewsletters( [], ['title' => 'ASC'] );
		$this->addData( 'addTemplates', $templates );
		$this->addData( 'addNewsletters', $newsletters );

		$filterTitle	= trim( $this->session->get( $this->filterPrefix.'title', '' ) );
		$filterStatus	= trim( $this->session->get( $this->filterPrefix.'status', '' ) );
		$filterLimit	= (int) $this->session->get( $this->filterPrefix.'limit', 10 );

		$conditions	= [];
		if( '' !== $filterTitle )
			$conditions['title']	= '%'.$filterTitle.'%';
		if( '' !== $filterStatus )
			$conditions['status']	= $filterStatus;

		$logicAuth	= Logic_Authentication::getInstance( $this->env );
		if( $this->useUserGroupRelations && !$logicAuth->hasFullAccess() ){
			$logic		= Logic_GroupRelation::getInstance( $this->env );
			$entityIds	= $logic->getModuleEntityIdsFromCurrentGroups( 'Resource_Newsletter' );
			$conditions['newsletterId']	= array_merge( [0], $entityIds );
		}

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

	/**
	 *	@param		string		$format
	 *	@param		string		$newsletterId
	 *	@param		bool		$simulateOffline
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function preview( string $format, string $newsletterId, bool $simulateOffline = FALSE ): void
	{
		$this->checkNewsletterId( $newsletterId );

		$data	= [
			'newsletterId'	=> $newsletterId,
			'preview'		=> TRUE,
		];


		$mail		= new Mail_Newsletter( $this->env, $data );
		$response	= $this->env->getResponse();
		switch( strtolower( trim( $format ) ) ){
			case 'html':
				$content	= $mail->getContent( Mail_Abstract::CONTENT_TYPE_HTML_RENDERED );
				$response->setHeader( 'Content-Type', 'text/html' );
				break;
			case 'text':
			default:
				$content	= $mail->getContent( Mail_Abstract::CONTENT_TYPE_TEXT_RENDERED );
				$response->setHeader( 'Content-Type', 'text/plain' );
				break;
		}
		$response->setBody( $content );
		$response->send();
		die;



		$words		= (object) $this->getWords( 'preview' );
		$newsletter	= $this->logic->getNewsletter( $newsletterId );
//		$template	= $this->logic->getTemplate( $newsletter->newsletterTemplateId );
		$baseUrl	= $this->env->url;
		if( $this->env->getModules()->has( 'Resource_Frontend' ) )
			$baseUrl	= Logic_Frontend::getInstance( $this->env )->getUrl();

		$urlTrack	= $baseUrl.'info/newsletter/track/0';
		$data		= [
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
		];
		$mail	= new View_Helper_Newsletter_Mail( $this->env );
		$mail->setTemplateId( $newsletter->newsletterTemplateId );
		switch( strtolower( $format ) ){
			case 'text':
				$data['content']		= $newsletter->plain;
				$data['linkUnregister']	= '('.$words->alertDisabledInPreview.')';
				$data['linkView']		= '('.$words->alertDisabledInPreview.')';
				$mail->setData( $data );
				$content	= HtmlTag::create( 'xmp', $mail->render() );
				break;
			case 'html':
				$data['content']		= $newsletter->html;
				$mail->setMode( View_Helper_Newsletter_Mail::MODE_HTML );
				$mail->setData( $data );
				$content	= $mail->render();
				if( $simulateOffline )
					$content	= preg_replace( '@https?://@', "noschema://", $content );
				break;
			default:
				throw new InvalidArgumentException( 'Format "'.$format.'" is not supported' );
		}
		print( $content );
		exit;
	}

	/**
	 *	Resends a readers newsletter.
	 *	@access		public
	 *	@param		string		$readerLetterId		ID of former send reader newsletter
	 *	@return		void
	 *	@todo		extend by queue support, otherwise this wont work anymore
	 */
	public function sendLetter( string $readerLetterId ): void
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

	/**
	 *	@param		string		$newsletterId
	 *	@param		string		$tabKey
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function setContentTab( string $newsletterId, string $tabKey ): void
	{
		$this->checkNewsletterId( $newsletterId );
		$this->session->set( 'work.newsletter.content.tab', $tabKey );
		$this->restart( './work/newsletter/edit/'.$newsletterId );
	}

	/**
	 *	@param		string		$newsletterId
	 *	@param		$status
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function setStatus( string $newsletterId, $status ): void
	{
		$this->checkNewsletterId( $newsletterId );

		$words		= (object) $this->getWords( 'edit' );
		if( !$this->logic->checkNewsletterId( $newsletterId ) ){
			$this->messenger->noteError( $words->msgErrorInvalidId, $newsletterId );
			$this->restart( './work/newsletter' );
		}
		if( !in_array( (int) $status, [-1, 0, 1, 2], TRUE ) ){
			$this->messenger->noteError( 'Invalid status.', $newsletterId );
			$this->restart( './work/newsletter' );
		}
		$this->logic->editNewsletter( $newsletterId, ['status' => (int) $status] );
		$this->messenger->noteSuccess( $words->msgSuccess );
		$urlForwardTo	= $this->request->get( 'forwardTo' );
		$this->restart( $urlForwardTo ?: 'edit/'.$newsletterId, TRUE );
	}

	/**
	 *	@param		string		$newsletterId
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function test( string $newsletterId ): void
	{
		$this->checkNewsletterId( $newsletterId );

		$w			= (object) $this->getWords( 'test' );
		$readerIds	= $this->request->get( 'readerIds' );
		if( !is_array( $readerIds ) || !count( $readerIds ) ){
			$this->messenger->noteError( 'No receivers selected.' );
			$this->restart( 'edit/'.$newsletterId, TRUE );
		}
		foreach( $readerIds as $readerId )
			$this->logic->sendTestLetter( $newsletterId, $readerId );
		$this->messenger->noteSuccess( $w->msgSuccess, count( $readerIds ) );
/*		$this->logic->editNewsletter( $newsletterId, [										//  update newsletter
			'status' 	=> Model_Newsletter_Template::STATUS_READY,								//  ... by setting to READY
			'modifiedAt' => time()																//  ... and not change time
		] );*/
		$this->restart( 'edit/'.$newsletterId.'?askForReady', TRUE );
//		$this->restart( 'setContentTab/'.$newsletterId.'/4', TRUE );
	}

	/**
	 *	@param		string		$readerLetterId
	 *	@return		void
	 */
	public function view( string $readerLetterId ): void
	{
		try{
			$letter		= $this->logic->getReaderLetter( $readerLetterId );
			$newsletter	= $this->logic->getNewsletter( $letter->newsletterId );
			try{
				$helper		= new View_Helper_Newsletter( $this->env, $newsletter->newsletterTemplateId );
				$data		= $helper->prepareReaderDataForLetter( $readerLetterId );
				$mail		= $helper->renderNewsletterHtml( $letter->newsletterId, $letter->newsletterReaderId, $data );
				print( $mail );
				die;
			}
			catch( Exception $e ){
				HtmlExceptionPage::display( $e );
				exit;
			}
		}
		catch( Exception ){
			$this->messenger->noteError( 'Der gewählte Newsletter existiert nicht mehr. Weiterleitung zur Übersicht.' );
			$this->restart( NULL, TRUE );
		}
	}

	/**
	 *	@param		string		$newsletterId
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function remove( string $newsletterId ): void
	{
		$this->checkNewsletterId( $newsletterId );
		$this->logic->removeNewsletter( $newsletterId );
		$this->messenger->noteSuccess( 'Kampagne entfernt.' );
		$this->restart( NULL, TRUE );
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->logic		= Logic_Newsletter_Editor::getInstance( $this->env );
		$this->session		= $this->env->getSession();
		$this->request		= $this->env->getRequest();
		$this->messenger	= $this->env->getMessenger();
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.work_newsletter.', TRUE );
		$this->addData( 'moduleConfig', $this->moduleConfig );
		$this->addData( 'tabbedLinks', $this->moduleConfig->get( 'tabbedLinks' ) );

		$this->frontendUrl		= $this->env->url;
		if( $this->env->getModules()->has( 'Resource_Frontend' ) )
			$this->frontendUrl	= Logic_Frontend::getInstance( $this->env )->getUrl();
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

		$this->useUserGroupRelations	= $this->moduleConfig->get( 'useUserGroupRelations', FALSE );
		$this->addData( 'useUserGroupRelations', $this->useUserGroupRelations );
		$this->addData( 'canManageGroupRelations', $this->env->getAcl()->has( 'manage/group', 'relation' ) );
	}

	/**
	 *	@param		int|string		$newsletterId
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function checkNewsletterId( int|string $newsletterId ): void
	{
		if( !$this->logic->checkNewsletterId( $newsletterId ) ){
			$words		= (object) $this->getWords( 'edit' );
			$this->messenger->noteError( $words->msgErrorInvalidId, $newsletterId );
			$this->restart( NULL, TRUE );
		}

		if( !$this->logic->hasGroupAccessToNewsletter( $newsletterId ) ){
			$words		= (object) $this->getWords( 'edit' );
			$this->messenger->noteError( $words->msgErrorInvalidId, $newsletterId );
			$this->restart( NULL, TRUE );
		}
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function handleAddRequest(): void
	{
		$currentUserId	= (int) $this->session->get( Logic_Authentication::$sessionKeyAuthUserId );
		$words			= (object) $this->getWords( 'add' );

		$newsletterTemplateId	= $this->request->get( 'newsletterTemplateId', '' );
		$sourceNewsletterId		= $this->request->get( 'newsletterId', '' );

		$data	= [];																					//  start to prepare data
		if( 0 !== (int) $sourceNewsletterId ){																//  from source newsletter
			$data	= (array) $this->logic->getNewsletter( $sourceNewsletterId );						//  take all data from former newsletter
			unset( $data['status'], $data['modifiedAt'], $data['sentAt'], $data['trackingCode'] );		//  ... but reset status, timestamps and tracking code
		}
		else if( '' !== $newsletterTemplateId ){														//  from newsletter template
			$template	= $this->logic->getTemplate( $newsletterTemplateId );							//  load template
			$data		= [																				//  extend prepared data by newsletter template data
				'senderName'	=> $template->senderName,												//  ... carry sender name
				'senderAddress'	=> $template->senderAddress,											//  ... carry sender address
				'html'			=> str_replace( '[#app.url#]', '', $template->html ),
				'plain'			=> str_replace( '[#app.url#]', '', $template->plain ),
			];
		}

		$data	= array_merge( $data, [																	//  extend prepared data by input of modal form
			'newsletterTemplateId'	=> $newsletterTemplateId,
			'creatorId'			=> $currentUserId,
			'title'				=> $this->request->get( 'title' ),									//  title is MANDATORY
			'subject'			=> trim( $this->request->get( 'subject', '' ) ),
			'heading'			=> trim( $this->request->get( 'heading', '' ) ),
//			'senderName'		=> trim( $this->request->get( 'senderName', '' ) ),
//			'senderAddress'		=> trim( $this->request->get( 'senderAddress', '' ) ),
			'trackingCode'		=> trim( $this->request->get( 'trackingCode', '' ) ),
			'createdAt'			=> time(),																//  current timestamp
		] );
		if( '' === $data['subject'] )																	//  no subject set
			$data['subject']	= $data['title'];														//  take title as subject
		if( $this->logic->getNewsletters( ['title' => $data['title']] ) ){								//  already got newsletters with this title
			$this->messenger->noteError( $words->msgErrorTitleExists );									//  note error
			return;																						//  ... and quit without redirect to GET
		}
		/** @var Resource_Database $db */
		$db 	= $this->env->getDatabase();
		/** @var Resource_Database_Connection $dbc */
		$dbc	= $db->getConnection();

		$dbc->beginTransaction();
		
		unset( $data['newsletterId'] );																	//  clear newsletter ID in prepared data
		$newsletterId		= $this->logic->addNewsletter( $data );										//  save model data to database

		if( $this->useUserGroupRelations ){																//  apply group relations
			$logicRelation	= Logic_GroupRelation::getInstance( $this->env );							//  get logic for group relations to module entities
			$entityModuleId	= 'Resource_Newsletter';													//  entity relation module key
			if( 0 !== (int) $sourceNewsletterId )														//  from source newsletter
				$relatedGroups	= $logicRelation->getGroups( $entityModuleId, $sourceNewsletterId );	//  ... copy group relations
			else																						//  from request
				$relatedGroups	= $this->request->get( 'relationGroupIds' );						//  ...  by form data
			if( [] === $relatedGroups ){
				$this->messenger->noteError( 'Mindestens eine Gruppenzuweisung ist notwendig.' );
				$dbc->rollBack();
				$this->restart( 'work/newsletter/add' );
			}
			foreach( $relatedGroups as $relatedGroup )														//  iterate groups to relate
				$logicRelation->addModuleEntityRelation( $relatedGroup, $entityModuleId, $newsletterId );	//  add relation between newsletter and group
		}
		$dbc->commit();

		$this->messenger->noteSuccess( $words->msgSuccess );
		$this->setContentTab( $newsletterId, 1 );
		$this->restart( 'edit/'.$newsletterId, TRUE );
	}

	/**
	 *	@param		string		$newsletterId
	 *	@return		void
	 *	@throws		InvalidArgumentException			if newsletter is not exising and $throwException is TRUE
	 *	@throws		Exception			Premailer exceptions
	 */
	protected function handleEditRequest( string $newsletterId ): void
	{
		if( !$this->request->has( 'save' ) )
			$this->restart( 'edit/'.$newsletterId );

		$words		= (object) $this->getWords( 'edit' );
		$newsletter	= $this->logic->getNewsletter( $newsletterId );
		$currentTab	= (int) $this->session->get( 'work.newsletter.content.tab', 1 );

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
		if( 2 === $currentTab ){
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

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function prepareAddData(): void
	{
		$templates	= $this->logic->getTemplates( ['status' => '> 0'], ['title' => 'ASC'] );
		if( [] === $templates ){
			$this->messenger->noteNotice( 'Es ist noch keine verwendbare Vorlage vorhanden. Weiterleitung zu den Vorlagen.' );
			$this->restart( 'work/newsletter/template' );
		}

		$newsletters	= $this->logic->getNewsletters( [], ['title' => 'ASC'] );
		$newsletter		= (object) [
			'newsletterTemplateId'	=> (int) $this->request->get( 'newsletterTemplateId' ),
			'newsletterId'			=> (int) $this->request->get( 'newsletterId' ),
			'creatorId'				=> (int) $this->session->get( Logic_Authentication::$sessionKeyAuthUserId ),
			'title'					=> trim( $this->request->get( 'title' ) ),
			'senderAddress'			=> trim( $this->request->get( 'senderAddress' ) ),
			'senderName'			=> trim( $this->request->get( 'senderName' ) ),
			'heading'				=> trim( $this->request->get( 'heading' ) ),
			'subject'				=> trim( $this->request->get( 'subject' ) ),
			'trackingCode'			=> trim( $this->request->get( 'trackingCode' ) ),
		];
		$this->addData( 'templates', $templates );
		$this->addData( 'newsletters', $newsletters );
		$this->addData( 'newsletter', $newsletter );
	}

	/**
	 *	@param		string		$newsletterId
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function prepareEditData( string $newsletterId ): void
	{
		$newsletter		= $this->logic->getNewsletter( $newsletterId );
		$template		= $this->logic->getTemplate( $newsletter->newsletterTemplateId );
		$templates		= $this->logic->getTemplates( ['status' => '>= '.Model_Newsletter_Template::STATUS_READY], ['title' => 'ASC'] );

		//  newsletter groups for testing and sending
		$groups			= [];
		$conditions		= ['status' => Model_Newsletter_Group::STATUS_USABLE];
		foreach( $this->logic->getGroups( $conditions, ['title' => 'ASC'] ) as $group ){
			$group->readers	= $this->logic->getGroupReaders( $group->newsletterGroupId );
			$groups[$group->newsletterGroupId]	= $group;
		}

		$groupIds		= $this->request->get( 'groupIds', [] );
		if( 1 === count( $groups ) && [] === $groupIds )
			$groupIds	= [current( $groups )->newsletterGroupId];

		$testers		= [];
		$readers		= [];
		$nrReaders		= 0;
		if( $groupIds ){
			foreach( $groupIds as $groupId )
				$nrReaders += $this->logic->countGroupReaders( $groupId );
			foreach( $groupIds as $groupId ){
				foreach( $this->logic->getGroupReaders( $groupId ) as $reader ){
					if( $nrReaders <= 50 )
						$readers[$reader->newsletterReaderId]	= $reader;
					if( $reader->tester )
						$testers[$reader->newsletterReaderId]	= $reader;
				}
			}
		}

		$queues			= $this->logic->getQueuesOfNewsletter( $newsletterId );

		$letterQueue	= [];
		$letterHistory	= [];
		$nrLetters	= $this->logic->countReaderLetters( ['newsletterId' => $newsletterId] );
		if( $nrLetters <= 50 ){
			$letterQueue	= $this->logic->getReaderLetters( [
				'newsletterId'	=> $newsletterId,
				'status'		=> Model_Newsletter_Reader_Letter::STATUS_ENQUEUED,
			] );
			$letterHistory	= $this->logic->getReaderLetters( [
				'newsletterId'	=> $newsletterId,
				'status'		=> '!= '.Model_Newsletter_Reader_Letter::STATUS_ENQUEUED,
			] );
		}

		$isUsed	= $newsletter->status >= Model_Newsletter::STATUS_SENT;
		$this->addData( 'isUsed', $isUsed );
		$this->addData( 'newsletterId', $newsletterId );
		$this->addData( 'newsletters', $this->logic->getNewsletters() );
		$this->addData( 'newsletter', $newsletter );
		$this->addData( 'templates', $templates );
		$this->addData( 'template', $template );
		$this->addData( 'groups', $groups );
		$this->addData( 'groupIds', $groupIds );
		$this->addData( 'nrReaders', $nrReaders );
		$this->addData( 'readers', $readers );
		$this->addData( 'testers', $testers );
		$this->addData( 'queues', $queues );
		$this->addData( 'nrLetters', $nrLetters );
		$this->addData( 'letterQueue', $letterQueue );
		$this->addData( 'letterHistory', $letterHistory );
		$this->addData( 'styles', $this->logic->getTemplateAttributeList( $newsletter->newsletterTemplateId, 'styles' ) );
		$this->addData( 'askForReady', $this->request->has( 'askForReady' ) );

		$this->addData( 'canRemove', $this->env->getAcl()->has( 'work/newsletter', 'remove' ) );
//		$this->addData( 'canRemove', $this->env->getAcl()->has( 'work/newsletter', 'remove' ) );
	}
}
