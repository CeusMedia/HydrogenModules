<?php
class Controller_Admin_Mail_Queue extends CMF_Hydrogen_Controller{

	protected $logic;

	public function __onInit(){
		$this->logic	= new Logic_Mail( $this->env );
		$path			= '';
		if( $this->env->getModules()->has( 'Resource_Frontend' ) )
			$path	= Logic_Frontend::getInstance( $this->env )->getPath();
		CMC_Loader::registerNew( 'php5', 'Mail_', $path.'classes/Mail/' );
	}

	public function html( $mailId ){
		$mail		= $this->logic->getQueuedMail( $mailId );
		$this->addData( 'mail', $mail );
	}

	public function enqueue(){
		$request	= $this->env->getRequest();
		$messenger	= $this->env->getMessenger();
		$language	= $this->env->getLanguage()->getLanguage();
		if( $request->has( 'add' ) ){
			if( !strlen( $class	= trim( $request->get( 'class' ) ) ) )
				$messenger->noteError( 'Mail class is missing.' );
			if( !strlen( $sender	= trim( $request->get( 'sender' ) ) ) )
				$messenger->noteError( 'Sender address is missing.' );
			if( !strlen( $receiver	= trim( $request->get( 'receiver' ) ) ) )
				$messenger->noteError( 'Receiver address is missing.' );
			if( !strlen( $subject	= trim( $request->get( 'subject' ) ) ) )
				$messenger->noteError( 'Mail subject is missing.' );
			if( !strlen( $body		= trim( $request->get( 'body' ) ) ) )
				$messenger->noteError( 'Mail body is missing.' );
			if( !$messenger->gotError() ){
				try{
					$receiver	= array( 'email' => $receiver );
					$mail	= new Mail_Test( $this->env, $request->getAll() );
					$mail->setSubject( $subject );
					$mail->setSender( $sender );

#xmp( $mail->mail->getBody() );
#die("!");

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
		$this->addData( 'class', $request->get( 'class' ) );
		$this->addData( 'subject', $request->get( 'subject' ) );
		$this->addData( 'body', $request->get( 'body' ) );
		$this->addData( 'sender', $request->get( 'sender' ) ? $request->get( 'sender' ) : "kriss@localhost" );
		$this->addData( 'receiver', $request->get( 'receiver' ) ? $request->get( 'receiver' ) : "dev@ceusmedia.de" );
	}

	public function filter( $reset = NULL ){
		$request	= $this->env->getRequest();
		$session	= $this->env->getSession();
		if( $reset ){
			$session->remove( 'filter_mail_status' );
			$session->remove( 'filter_mail_limit' );
			$session->remove( 'filter_mail_order' );
			$session->remove( 'filter_mail_direction' );
		}
		else{
			$session->set( 'filter_mail_status', $request->get( 'status' ) );
			$session->set( 'filter_mail_limit', $request->get( 'limit' ) );
			$session->set( 'filter_mail_order', $request->get( 'order' ) );
			$session->set( 'filter_mail_direction', $request->get( 'direction' ) );
		}
		$this->restart( NULL, TRUE );
	}

	public function index( $page = 0 ){
		$session	= $this->env->getSession();
		if( !$session->get( 'filter_mail_status' ) )
			$session->set( 'filter_mail_status', array( 0 ) );
		if( !$session->get( 'filter_mail_limit' ) )
			$session->set( 'filter_mail_limit', 10 );
		if( !$session->get( 'filter_mail_order' ) )
			$session->set( 'filter_mail_order', 'enqueuedAt' );
		if( !$session->get( 'filter_mail_direction' ) )
			$session->set( 'filter_mail_direction', 'DESC' );
		$filters	= $session->getAll( 'filter_mail_', TRUE );

		$page		= max( 0, (int) $page );
		$offset		= $page * $filters->get( 'limit' );
		$conditions	= array();
		if( $filters->get( 'status' ) )
			$conditions	= array( 'status' => $filters->get( 'status' ) );
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
	}

	public function send(){
		$count	= $this->logic->countQueue( array( 'status' => '<2' ) );
		if( $count ){
			$this->env->getMessenger()->noteNotice( "Mails in Queue: ".$this->logic->countQueue() );
			if( $this->logic->countQueue( array( 'status' => '<2' ) ) ){
				foreach( $this->logic->getQueuedMails( array( 'status' => '<2' ) ) as $mail ){
					try{
						$this->logic->sendQueuedMail( $mail->mailId );
						$this->env->getMessenger()->noteSuccess( "Mail #".$mail->mailId." sent ;-)" );
					}
					catch( Exception $e ){
						$this->env->getMessenger()->noteFailure( $e->getMessage() );
					}
				}
			}
		}
	}

	public function view( $mailId ){
		$mail	= $this->logic->getQueuedMail( $mailId );

		if( $mail->object ){
			switch( substr( $mail->object, 0, 2 ) ){
				case 'BZ':
					$mail->object	= bzdecompress( $mail->object );
					break;
				case 'GZ':
					$mail->object	= gzinflate( $mail->object );
					break;
			}
			$mail->object	= @unserialize( $mail->object );
		}
		$this->addData( 'mail', $mail );
	}
}
?>
