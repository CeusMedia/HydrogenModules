<?php

use CeusMedia\HydrogenFramework\Controller;

class Controller_Admin_Mail_Queue extends Controller
{
	protected $request;
	protected $session;
	protected $messenger;
	protected $logic;
	protected $model;
	protected $filterPrefix	= 'filter_admin_mail_queue_';

	public function ajaxRenderDashboardPanel( $panelId )
	{
		return $this->view->ajaxRenderDashboardPanel();
	}

	/**
	 *	Delivers attachment content.
	 *	By sending the attachments MIME type, the browser can decide, what to do.
	 *	Set delivery mode to 'download' to force download.
	 *	Exits after delivery.
	 *	@access		public
	 *	@param		integer		$mailId			ID of mail of attachment
	 *	@param		integer		$attachmentNr	Index key attachment within mail
	 *	@todo		export locales
	 */
	public function attachment( $mailId, $attachmentNr, $deliveryMode = NULL )
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
		$mailObjectParts	= $mail->object->instance->mail->getParts();
		$attachments		= [];
		foreach( $mailObjectParts as $key => $part ){
			if( $mail->usedLibrary === Logic_Mail::LIBRARY_MAIL_V2 ){
				$isAttachment	= $part instanceof \CeusMedia\Mail\Message\Part\Attachment;
				$isInlineImage	= $part instanceof \CeusMedia\Mail\Message\Part\InlineImage;
				if( $isAttachment || $isInlineImage )
					$attachments[$key]	= $part;
			}
			else if( $mail->usedLibrary === Logic_Mail::LIBRARY_MAIL_V1 ){
				$isAttachment	= $part instanceof \CeusMedia\Mail\Part\Attachment;
				$isInlineImage	= $part instanceof \CeusMedia\Mail\Part\InlineImage;
				if( $isAttachment || $isInlineImage )
					$attachments[$key]	= $part;
			}
			else if( $mail->usedLibrary === Logic_Mail::LIBRARY_COMMON ){
				if( $part instanceof Net_Mail_Attachment )
					$attachments[$key]	= $part;
			}
		}

		if( !array_key_exists( $attachmentNr, $attachments ) ){
			$message	= 'Die ID des Anhangs ist ungültig.';
			$this->env->getMessenger()->noteError( $message );
			$this->restart( 'view/'.$mailId, TRUE );
		}
		$item	= $attachments[$attachmentNr];
		if( $deliveryMode === 'download' ){
			Net_HTTP_Download::sendString( $item->getContent(), $item->getFileName() );
		}
		else{
			$response	= $this->env->getResponse();
			$headers	= array(
				'Cache-Control'				=> 'private, max-age=0, must-revalidate',
				'Pragma'					=> 'public',
				'Content-Transfer-Encoding'	=> 'binary',
				'Content-Disposition'		=> 'inline; filename="'.$item->getFileName().'"',
				'Content-Type'				=> $item->getMimeType(),
			);
			foreach( $headers as $key => $value )
				$response->addHeaderPair( $key, $value );
			$response->setBody( $item->getContent() );
			Net_HTTP_Response_Sender::sendResponse( $response );
		}
		exit;
	}

	public function bulk()
	{
		$type	= $this->request->get( 'type' );
		$ids	= preg_split( '/\s*,\s*/', $this->request->get( 'ids' ) );
		switch( $type ){
			case 'abort':
				$count	= $this->bulkAbort( $ids );
				break;
			case 'retry':
				$count	= $this->bulkRetry( $ids );
				break;
			case 'remove':
				$count	= $this->bulkRemove( $ids );
				break;
		}
		$this->restart( NULL, TRUE );
	}

	public function cancel( $mailId )
	{
		$model	= new Model_Mail( $this->env );
		$mail	= $model->get( $mailId );
		if( !$mail ){
			$this->env->getMessenger->noteError( 'Invalid mail ID' );
			$this->restart( NULL, TRUE );
		}
		if( $mail->status > 1 ){
			$this->env->getMessenger->noteError( 'Mail already sent' );
			$this->restart( NULL, TRUE );
		}
		$model->edit( $mailId, array(
			'status'	=> Model_Mail::STATUS_ABORTED,
		) );
		$this->restart( 'view/'.$mailId, TRUE );
	}

	public function enqueue()
	{
		$language	= $this->env->getLanguage()->getLanguage();
		if( $this->request->has( 'add' ) ){
			if( !strlen( $class	= trim( $this->request->get( 'class' ) ) ) )
				$messenger->noteError( 'Mail class is missing.' );
			if( !strlen( $sender	= trim( $this->request->get( 'sender' ) ) ) )
				$messenger->noteError( 'Sender address is missing.' );
			if( !strlen( $receiver	= trim( $this->request->get( 'receiver' ) ) ) )
				$messenger->noteError( 'Receiver address is missing.' );
			if( !strlen( $subject	= trim( $this->request->get( 'subject' ) ) ) )
				$messenger->noteError( 'Mail subject is missing.' );
			if( !strlen( $body		= trim( $this->request->get( 'body' ) ) ) )
				$messenger->noteError( 'Mail body is missing.' );
			if( !$messenger->gotError() ){
				try{
					$receiver	= array( 'email' => $receiver );
					$mail	= new Mail_Test( $this->env, $this->request->getAll() );
					$mail->setSubject( $subject );
					$mail->setSender( $sender );
					$this->logic->appendRegisteredAttachments( $mail, $language );
					if( 1 )
						$mail->sendTo( $receiver );
					else
						$this->logic->handleMail( $mail, $receiver, $language );
					$messenger->noteSuccess( "Mail sent ;-)" );
				}
				catch( Exception $e ){
					$messenger->noteFailure( $e->getMessage() );
				}
			}
			$this->restart( 'enqueue', TRUE );
		}
		$this->addData( 'classes', $this->logic->getMailClassNames() );
		$this->addData( 'class', $this->request->get( 'class' ) );
		$this->addData( 'subject', $this->request->get( 'subject' ) );
		$this->addData( 'body', $this->request->get( 'body' ) );
		$this->addData( 'sender', $this->request->get( 'sender' ) ? $this->request->get( 'sender' ) : "kriss@localhost" );
		$this->addData( 'receiver', $this->request->get( 'receiver' ) ? $this->request->get( 'receiver' ) : "dev@ceusmedia.de" );
	}

	public function filter( $reset = NULL )
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
			$this->session->set( $this->filterPrefix.'status', $this->request->get( 'status' ) );
			$this->session->set( $this->filterPrefix.'mailClass', $this->request->get( 'mailClass' ) );
			$this->session->set( $this->filterPrefix.'dateStart', $this->request->get( 'dateStart' ) );
			$this->session->set( $this->filterPrefix.'dateEnd', $this->request->get( 'dateEnd' ) );
			$this->session->set( $this->filterPrefix.'timeStart', $this->request->get( 'timeStart' ) );
			$this->session->set( $this->filterPrefix.'timeEnd', $this->request->get( 'timeEnd' ) );
//			$this->session->set( $this->filterPrefix.'way', $this->request->get( 'way' ) );
			$this->session->set( $this->filterPrefix.'limit', $this->request->get( 'limit' ) );
			$this->session->set( $this->filterPrefix.'order', $this->request->get( 'order' ) );
			$this->session->set( $this->filterPrefix.'direction', $this->request->get( 'direction' ) );

			if( count( $states = $this->session->get( $this->filterPrefix.'status' ) ) === 1 )
				if( $states[0] === '' )
					$this->session->remove( $this->filterPrefix.'status' );

		}
		$this->restart( NULL, TRUE );
	}

	public function html( $mailId )
	{
		$this->addData( 'mail', $this->logic->getMail( (int) $mailId ) );
	}

	public function index( $page = 0 )
	{
//		if( !$this->session->get( $this->filterPrefix.'status' ) )
//			$this->session->set( $this->filterPrefix.'status', array( 0 ) );
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

		$page		= max( 0, (int) $page );
		$total		= $this->logic->countQueue( $conditions );
		$maxPage	= ceil( $total / $filters->get( 'limit' ) ) - 1;
		if( $page > $maxPage )
			$page	= $maxPage;
		$offset		= $page * $filters->get( 'limit' );

		$orders		= array( $filters->get( 'order' ) => $filters->get( 'direction' ) );
		$limits		= array( $offset, $filters->get( 'limit' ) );
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

	public function remove( $mailId )
	{
		$this->logic->removeMail( $mailId );
		if( ( $page = $this->request->get( 'page' ) ) )
			$this->restart( $page, TRUE );
		$this->restart( NULL, TRUE );
	}

	public function resend( $mailId )
	{
		$model	= new Model_Mail( $this->env );
		$mail	= $model->get( $mailId );
		if( !$mail ){
			$this->env->getMessenger->noteError( 'Invalid mail ID' );
			$this->restart( NULL, TRUE );
		}
/*		if( $mail->status > 1 ){
			$this->env->getMessenger->noteError( 'Mail already sent' );
			$this->restart( NULL, TRUE );
		}*/
		$model->edit( $mailId, array(
			'status'	=> Model_Mail::STATUS_NEW,
		) );
		$this->restart( 'view/'.$mailId, TRUE );
	}

	public function send()
	{
		$count	= $this->logic->countQueue( array( 'status' => '< '.Model_Mail::STATUS_SENT ) );
		if( $count ){
			$this->messenger->noteNotice( "Mails in Queue: ".$this->logic->countQueue() );
			if( $this->logic->countQueue( array( 'status' => '< '.Model_Mail::STATUS_SENT ) ) ){
				foreach( $this->logic->getQueuedMails( array( 'status' => '< '.Model_Mail::STATUS_SENT ) ) as $mail ){
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

	public function view( $mailId )
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

	protected function __onInit()
	{
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->messenger	= $this->env->getMessenger();
		$this->logic		= Logic_Mail::getInstance( $this->env );
		$this->model		= new Model_Mail( $this->env );

		$path				= '';
		if( $this->env->getModules()->has( 'Resource_Frontend' ) ){
			$path	= Logic_Frontend::getInstance( $this->env )->getPath();
			CMC_Loader::registerNew( 'php5', 'Mail_', $path.'classes/Mail/' );
		}
		if( !is_array( $this->session->get( $this->filterPrefix.'status' ) ) )
			$this->session->set( $this->filterPrefix.'status', array());
	}

	protected function bulkAbort( $mailIds )
	{
		if( !count( $mailIds ) )
			return 0;
		$mailIds	= $this->model->getAll( array(
			'mailId'	=> $mailIds,
			'status'	=> array(
				Model_Mail::STATUS_FAILED,
				Model_Mail::STATUS_RETRY,
				Model_Mail::STATUS_NEW,
			) ), array(), array(), array( 'mailId' ) );
		$data	= array(
			'status'		=> Model_Mail::STATUS_ABORTED,
			'modifiedAt'	=> time(),
		);
	print_m( $mailIds );
	print_m( $data );
	die;
//		return $this->model->editByIndices( array( 'mailId' => $mailIds ), $data );
	}

	protected function bulkRetry( $mailIds )
	{
		if( !count( $mailIds ) )
			return 0;
		$mailIds	= $this->model->getAll( array(
			'mailId'	=> $mailIds,
			'status'	=> array(
				Model_Mail::STATUS_ABORTED,
				Model_Mail::STATUS_FAILED,
			) ), array(), array(), array( 'mailId' ) );
		$data	= array(
			'status'		=> Model_Mail::STATUS_RETRY,
			'modifiedAt'	=> time(),
		);
	print_m( $mailIds );
	print_m( $data );
	die;
//		return $this->model->editByIndices( array( 'mailId' => $mailIds ), $data );
	}

	protected function bulkRemove( $mailIds )
	{
		if( !count( $mailIds ) )
			return 0;
		$this->model->removeByIndices( array( 'mailId' => $mailIds ) );
	}
}
