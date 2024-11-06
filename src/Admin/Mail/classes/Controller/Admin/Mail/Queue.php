<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Loader as ClassLoader;
use CeusMedia\Common\Net\HTTP\Download as HttpDownload;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\Common\Net\HTTP\Response\Sender as HttpResponseSender;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;
use CeusMedia\Mail\Message\Part;
use CeusMedia\Mail\Message\Part\Attachment as MailAttachment;
use CeusMedia\Mail\Message\Part\InlineImage as MailInlineImage;

class Controller_Admin_Mail_Queue extends Controller
{
	/** @var HttpRequest $request */
	protected HttpRequest $request;

	protected Dictionary $session;
	protected MessengerResource $messenger;
	protected Logic_Mail $logic;
	protected Model_Mail $model;
	protected string $filterPrefix	= 'filter_admin_mail_queue_';

	public function ajaxRenderDashboardPanel( string $panelId )
	{
		return $this->view->ajaxRenderDashboardPanel();
	}

	/**
	 *	Delivers attachment content.
	 *	By sending the attachments MIME type, the browser can decide, what to do.
	 *	Set delivery mode to 'download' to force download.
	 *	Exits after delivery.
	 *	@access		public
	 *	@param		int|string		$mailId			ID of mail of attachment
	 *	@param		integer			$attachmentNr	Index key attachment within mail
	 *	@todo		export locales
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function attachment( int|string $mailId, int $attachmentNr, $deliveryMode = NULL ): never
	{
		$libraries			= $this->logic->detectAvailableMailLibraries();
		$deliveryMode		= $deliveryMode == 'download' ? 'download' : 'view';
		$mail				= $this->logic->getMail( $mailId );
		$this->logic->detectMailLibraryFromMail( $mail );
		if( !( $libraries & $mail->usedLibrary ) ){
			$message	= 'Die beim Versand benutzte Bibliothek wird nicht mehr unterstützt.';
			$this->env->getMessenger()->noteError( $message );
			$this->restart( 'view/'.$mailId, TRUE );
		}

		/** @var array<Part> $mailObjectParts */
		$mailObjectParts	= $mail->objectInstance->mail->getParts();
		$attachments		= [];
		foreach( $mailObjectParts as $key => $part ){
			$isAttachment	= $part instanceof MailAttachment;
			$isInlineImage	= $part instanceof MailInlineImage;
			if( $isAttachment || $isInlineImage )
				$attachments[$key]	= $part;
		}

		if( !array_key_exists( $attachmentNr, $attachments ) ){
			$message	= 'Die ID des Anhangs ist ungültig.';
			$this->env->getMessenger()->noteError( $message );
			$this->restart( 'view/'.$mailId, TRUE );
		}
		$item	= $attachments[$attachmentNr];
		if( 'download' === $deliveryMode ){
			HttpDownload::sendString( $item->getContent(), $item->getFileName() );
		}
		else{
			$response	= $this->env->getResponse();
			$headers	= [
				'Cache-Control'				=> 'private, max-age=0, must-revalidate',
				'Pragma'					=> 'public',
				'Content-Transfer-Encoding'	=> 'binary',
				'Content-Disposition'		=> 'inline; filename="'.$item->getFileName().'"',
				'Content-Type'				=> $item->getMimeType(),
			];
			foreach( $headers as $key => $value )
				$response->addHeaderPair( $key, $value );
			$response->setBody( $item->getContent() );
			HttpResponseSender::sendResponse( $response );
		}
		exit;
	}

	/**
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function bulk(): void
	{
		$type	= $this->request->get( 'type' );
		$ids	= preg_split( '/\s*,\s*/', $this->request->get( 'ids' ) );
		switch( $type ){
			case 'abort':
				$this->bulkAbort( $ids );
				break;
			case 'retry':
				$this->bulkRetry( $ids );
				break;
			case 'remove':
				$this->bulkRemove( $ids );
				break;
		}
		$this->restart( NULL, TRUE );
	}

	/**
	 *	@param		int|string		$mailId		Mail ID
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function cancel( int|string $mailId ): void
	{
		$model	= new Model_Mail( $this->env );
		$mail	= $model->get( $mailId );
		if( !$mail ){
			$this->env->getMessenger()->noteError( 'Invalid mail ID' );
			$this->restart( NULL, TRUE );
		}
		if( $mail->status > 1 ){
			$this->env->getMessenger()->noteError( 'Mail already sent' );
			$this->restart( NULL, TRUE );
		}
		$model->edit( $mailId, [
			'status'	=> Model_Mail::STATUS_ABORTED,
		] );
		$this->restart( 'view/'.$mailId, TRUE );
	}

	/**
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function enqueue(): void
	{
		$language	= $this->env->getLanguage()->getLanguage();
		$class		= trim( $this->request->get( 'class' ) );
		$sender		= trim( $this->request->get( 'sender' ) );
		$receiver	= trim( $this->request->get( 'receiver' ) );
		$subject	= trim( $this->request->get( 'subject' ) );
		$body		= trim( $this->request->get( 'body' ) );
		if( $this->request->has( 'add' ) ){
			if( !strlen( $class ) )
				$this->messenger->noteError( 'Mail class is missing.' );
			if( !strlen( $sender ) )
				$this->messenger->noteError( 'Sender address is missing.' );
			if( !strlen( $receiver ) )
				$this->messenger->noteError( 'Receiver address is missing.' );
			if( !strlen( $subject ) )
				$this->messenger->noteError( 'Mail subject is missing.' );
			if( !strlen( $body ) )
				$this->messenger->noteError( 'Mail body is missing.' );
			if( !$this->messenger->gotError() ){
				try{
					$receiver	= (object) ['email' => $receiver];
					$mail	= new Mail_Test( $this->env, $this->request->getAll() );
					$mail->setSubject( $subject );
					$mail->setSender( $sender );
					$this->logic->appendRegisteredAttachments( $mail, $language );
					if( 1 )
						$mail->sendTo( $receiver );
					else
						$this->logic->handleMail( $mail, $receiver, $language );
					$this->messenger->noteSuccess( "Mail sent ;-)" );
				}
				catch( Exception $e ){
					$this->messenger->noteFailure( $e->getMessage() );
				}
			}
			$this->restart( 'enqueue', TRUE );
		}
		$this->addData( 'classes', $this->logic->getMailClassNames() );
		$this->addData( 'class', $class );
		$this->addData( 'subject', $subject );
		$this->addData( 'body', $body );
		$this->addData( 'sender', $sender );
		$this->addData( 'receiver', $receiver );
	}

	public function filter( $reset = NULL ): void
	{
		if( $reset ){
			foreach( $this->session->getAll( $this->filterPrefix ) as $key => $value )
				$this->session->remove( $this->filterPrefix.$key );
/*			$this->session->remove( $this->filterPrefix.'receiverAddress' );
			$this->session->remove( $this->filterPrefix.'status' );
//			$this->session->remove( $this->filterPrefix.'way' );
			$this->session->remove( $this->filterPrefix.'limit' );
			$this->session->remove( $this->filterPrefix.'order' );
			$this->session->remove( $this->filterPrefix.'direction' );*/
		}
		else{
			$this->session->set( $this->filterPrefix.'subject', $this->request->get( 'subject' ) );
			$this->session->set( $this->filterPrefix.'receiverAddress', $this->request->get( 'receiverAddress' ) );
			$this->session->set( $this->filterPrefix.'status', $this->request->get( 'status', [] ) );
			$this->session->set( $this->filterPrefix.'mailClass', $this->request->get( 'mailClass' ) );
			$this->session->set( $this->filterPrefix.'dateStart', $this->request->get( 'dateStart' ) );
			$this->session->set( $this->filterPrefix.'dateEnd', $this->request->get( 'dateEnd' ) );
			$this->session->set( $this->filterPrefix.'timeStart', $this->request->get( 'timeStart', '' ) );
			$this->session->set( $this->filterPrefix.'timeEnd', $this->request->get( 'timeEnd', '' ) );
//			$this->session->set( $this->filterPrefix.'way', $this->request->get( 'way' ) );
			$this->session->set( $this->filterPrefix.'limit', (int) $this->request->get( 'limit' ) );
			$this->session->set( $this->filterPrefix.'order', $this->request->get( 'order' ) );
			$this->session->set( $this->filterPrefix.'direction', $this->request->get( 'direction' ) );

			if( count( $states = $this->session->get( $this->filterPrefix.'status' ) ) === 1 )
				if( $states[0] === '' )
					$this->session->remove( $this->filterPrefix.'status' );

		}
		$this->restart( NULL, TRUE );
	}

	/**
	 *	@param		int|string		$mailId		Mail ID
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function html( int|string $mailId ): void
	{
		$this->addData( 'mail', $this->logic->getMail( (int) $mailId ) );
	}

	public function index( int $page = 0 ): void
	{
//		if( !$this->session->get( $this->filterPrefix.'status' ) )
//			$this->session->set( $this->filterPrefix.'status', [0] );
		if( !$this->session->get( $this->filterPrefix.'limit' ) )
			$this->session->set( $this->filterPrefix.'limit', 10 );
		if( !$this->session->get( $this->filterPrefix.'order' ) )
			$this->session->set( $this->filterPrefix.'order', 'enqueuedAt' );
		if( !$this->session->get( $this->filterPrefix.'direction' ) )
			$this->session->set( $this->filterPrefix.'direction', 'DESC' );
		$filters	= $this->session->getAll( $this->filterPrefix, TRUE );
		$dateStart	= $filters->get( 'dateStart' );
		$dateEnd	= $filters->get( 'dateEnd' );

		$conditions	= [];

		if( $filters->get( 'subject' ) )
			$conditions['subject'] = '%'.$filters->get( 'subject' ).'%';
		if( $filters->get( 'receiverAddress' ) )
			$conditions['receiverAddress'] = '%'.$filters->get( 'receiverAddress' ).'%';
		if( $filters->get( 'status' ) )
			$conditions['status'] = $filters->get( 'status' );
		if( $filters->get( 'mailClass' ) )
			$conditions['mailClass'] = $filters->get( 'mailClass' );
		if( $dateStart && $dateEnd )
			$conditions['enqueuedAt']	= '>< '.strtotime( $dateStart ).' & '.( strtotime( $dateEnd ) + 24 * 3600 - 1);
		else if( $dateStart )
			$conditions['enqueuedAt']	= '>= '.strtotime( $dateStart );
		else if( $dateEnd )
			$conditions['enqueuedAt']	= '<= '.( strtotime( $dateEnd ) + 24 * 36000 - 1);

		$page		= max( 0, $page );
		$total		= $this->logic->countQueue( $conditions );
		$maxPage	= ceil( $total / $filters->get( 'limit' ) ) - 1;
		if( $page > $maxPage )
			$page	= $maxPage;
		$offset		= $page * $filters->get( 'limit' );

		$orders		= [$filters->get( 'order' ) => $filters->get( 'direction' )];
		$limits		= [$offset, (int) $filters->get( 'limit' )];
		$mails		= $this->logic->getQueuedMails( $conditions, $orders, $limits );
		$total		= $this->logic->countQueue( $conditions );
		$this->addData( 'mails', $mails );
		$this->addData( 'offset', $offset );
		$this->addData( 'page', $page );
		$this->addData( 'total', $total );
		$this->addData( 'limit', $filters->get( 'limit' ) );
		$this->addData( 'filters', $filters );

//		$mailClasses	= array_values( $this->logic->getMailClassNames() );
		$mailClasses	= array_keys( $this->logic->getUsedMailClassNames() );
//		print_m( $mailClasses );die;
		$this->addData( 'mailClasses', $mailClasses );
	}

	protected function checkAjaxRequest(): void
	{
		parent::checkAjaxRequest(); // TODO: Change the autogenerated stub
	}

	/**
	 *	@param		$mailId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function remove( $mailId ): void
	{
		$this->logic->removeMail( $mailId );
		if( ( $page = $this->request->get( 'page' ) ) )
			$this->restart( $page, TRUE );
		$this->restart( NULL, TRUE );
	}

	/**
	 *	@param		int|string		$mailId		Mail ID
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function resend( int|string $mailId ): void
	{
		$model	= new Model_Mail( $this->env );
		$mail	= $model->get( $mailId );
		if( !$mail ){
			$this->env->getMessenger()->noteError( 'Invalid mail ID' );
			$this->restart( NULL, TRUE );
		}
/*		if( $mail->status > 1 ){
			$this->env->getMessenger->noteError( 'Mail already sent' );
			$this->restart( NULL, TRUE );
		}*/
		$model->edit( $mailId, [
			'status'	=> Model_Mail::STATUS_NEW,
		] );
		$this->restart( 'view/'.$mailId, TRUE );
	}

	/**
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function send(): void
	{
		$count	= $this->logic->countQueue( ['status' => '< '.Model_Mail::STATUS_SENT] );
		if( $count ){
			$this->messenger->noteNotice( "Mails in Queue: ".$this->logic->countQueue() );
			if( $this->logic->countQueue( ['status' => '< '.Model_Mail::STATUS_SENT] ) ){
				foreach( $this->logic->getQueuedMails( ['status' => '< '.Model_Mail::STATUS_SENT] ) as $mail ){
					try{
						$this->logic->sendQueuedMail( $mail->mailId );
						$this->messenger->noteSuccess( "Mail #".$mail->mailId." sent ;-)" );
					}
					catch( Exception $e ){
						$this->messenger->noteFailure( $e->getMessage() );
					}
				}
			}
		}
		$this->restart( NULL, TRUE );
	}

	/**
	 *	@param		int|string		$mailId		Mail ID
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function view( int|string $mailId ): void
	{
		try{
			$mail			= $this->logic->getMail( $mailId );
			$mail->parts	= $this->logic->getMailParts( $mail );
			$this->logic->detectMailLibraryFromMail( $mail );

			$this->addData( 'mail', $mail );
			$this->addData( 'libraries', $this->logic->detectAvailableMailLibraries() );
			$this->addData( 'page', $this->request->get( 'page' ) );
		}
		catch( Exception $e ){
			$this->messenger->noteFailure( $e->getMessage() );
			if( ( $page = $this->request->get( 'page' ) ) )
				$this->restart( $page, TRUE );
			$this->restart( NULL, TRUE );
		}
	}

	//  --  PROTECTED  --  //

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->messenger	= $this->env->getMessenger();
		$this->logic		= Logic_Mail::getInstance( $this->env );
		$this->model		= new Model_Mail( $this->env );

		if( $this->env->getModules()->has( 'Resource_Frontend' ) ){
			$path	= Logic_Frontend::getInstance( $this->env )->getPath();
			ClassLoader::create( 'php', $path.'classes/Mail/', 'Mail_' )->register();
		}
		if( !is_array( $this->session->get( $this->filterPrefix.'status' ) ) )
			$this->session->set( $this->filterPrefix.'status', []);
	}

	/**
	 *	@param		array<int|string>		$mailIds		List of mail IDs
	 *	@return		int
	 */
	protected function bulkAbort( array $mailIds ): int
	{
		if( !count( $mailIds ) )
			return 0;
		$mailIds	= $this->model->getAll( [
			'mailId'	=> $mailIds,
			'status'	=> [
				Model_Mail::STATUS_FAILED,
				Model_Mail::STATUS_RETRY,
				Model_Mail::STATUS_NEW,
			]], [], [], ['mailId'] );
		$data	= [
			'status'		=> Model_Mail::STATUS_ABORTED,
			'modifiedAt'	=> time(),
		];
		return $this->model->editByIndices( ['mailId' => $mailIds], $data );
	}

	/**
	 *	@param		array<int|string>		$mailIds		List of mail IDs
	 *	@return		int
	 */
	protected function bulkRetry( array $mailIds ): int
	{
		if( !count( $mailIds ) )
			return 0;
		$mailIds	= $this->model->getAll( [
			'mailId'	=> $mailIds,
			'status'	=> [
				Model_Mail::STATUS_ABORTED,
				Model_Mail::STATUS_FAILED,
			]], [], [], ['mailId'] );
		$data	= [
			'status'		=> Model_Mail::STATUS_RETRY,
			'modifiedAt'	=> time(),
		];
		return $this->model->editByIndices( ['mailId' => $mailIds], $data );
	}

	/**
	 *	@param		array<int|string>		$mailIds		List of mail IDs
	 *	@return		int
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function bulkRemove( array $mailIds ): int
	{
		if( !count( $mailIds ) )
			return 0;
		return $this->model->removeByIndices( ['mailId' => $mailIds] );
	}
}
