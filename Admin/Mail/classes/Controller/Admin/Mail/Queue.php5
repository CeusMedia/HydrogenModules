<?php
class Controller_Admin_Mail_Queue extends CMF_Hydrogen_Controller{

	protected $logic;

	public function __onInit(){
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->messenger	= $this->env->getMessenger();
		$this->logic		= new Logic_Mail( $this->env );
		$path				= '';
		if( $this->env->getModules()->has( 'Resource_Frontend' ) ){
			$path	= Logic_Frontend::getInstance( $this->env )->getPath();
			CMC_Loader::registerNew( 'php5', 'Mail_', $path.'classes/Mail/' );
		}
	}

	static public function ___onRegisterDashboardPanels( $env, $context, $module, $data ){
		if( !$env->getAcl()->has( 'admin/mail/queue', 'ajaxRenderDashboardPanel' ) )
			return;
		$context->registerPanel( 'admin-mail-queue', array(
			'url'			=> 'admin/mail/queue/ajaxRenderDashboardPanel',
			'title'			=> 'E-Mail-Queue',
			'heading'		=> 'E-Mail-Queue',
			'icon'			=> 'fa fa-fw fa-envelope',
			'rank'			=> 70,
			'refresh'		=> 10,
		) );
	}

	public function ajaxRenderDashboardPanel( $panelId ){
		return $this->view->ajaxRenderDashboardPanel();
	}

	public function cancel( $mailId ){
		$model	= new Model_Mail( $this->env );
		$mail	= $model->getMail( $mailId );
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

	public function enqueue(){
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

	public function filter( $reset = NULL ){
		if( $reset ){
			$this->session->remove( 'filter_mail_receiverAddress' );
			$this->session->remove( 'filter_mail_status' );
//			$this->session->remove( 'filter_mail_way' );
			$this->session->remove( 'filter_mail_limit' );
			$this->session->remove( 'filter_mail_order' );
			$this->session->remove( 'filter_mail_direction' );
		}
		else{
			$this->session->set( 'filter_mail_receiverAddress', $this->request->get( 'receiverAddress' ) );
			$this->session->set( 'filter_mail_status', $this->request->get( 'status' ) );
//			$this->session->set( 'filter_mail_way', $this->request->get( 'way' ) );
			$this->session->set( 'filter_mail_limit', $this->request->get( 'limit' ) );
			$this->session->set( 'filter_mail_order', $this->request->get( 'order' ) );
			$this->session->set( 'filter_mail_direction', $this->request->get( 'direction' ) );

			if( count( $states = $this->session->get( 'filter_mail_status' ) ) === 1 )
				if( $states[0] === '' )
					$this->session->remove( 'filter_mail_status' );

		}
		$this->restart( NULL, TRUE );
	}

	public function html( $mailId ){
		$this->addData( 'mail', $this->logic->getMail( (int) $mailId ) );
	}

	public function index( $page = 0 ){
//		if( !$this->session->get( 'filter_mail_status' ) )
//			$this->session->set( 'filter_mail_status', array( 0 ) );
		if( !$this->session->get( 'filter_mail_limit' ) )
			$this->session->set( 'filter_mail_limit', 10 );
		if( !$this->session->get( 'filter_mail_order' ) )
			$this->session->set( 'filter_mail_order', 'enqueuedAt' );
		if( !$this->session->get( 'filter_mail_direction' ) )
			$this->session->set( 'filter_mail_direction', 'DESC' );
		$filters	= $this->session->getAll( 'filter_mail_', TRUE );

		$page		= max( 0, (int) $page );
		$offset		= $page * $filters->get( 'limit' );
		$conditions	= array();
		if( $filters->get( 'receiverAddress' ) )
			$conditions['receiverAddress'] = '%'.$filters->get( 'receiverAddress' ).'%';
		if( $filters->get( 'status' ) )
			$conditions['status'] = $filters->get( 'status' );
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

	public function resend( $mailId ){
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

	public function send(){
		$count	= $this->logic->countQueue( array( 'status' => '<'.Model_Mail::STATUS_SENT ) );
		if( $count ){
			$this->env->getMessenger()->noteNotice( "Mails in Queue: ".$this->logic->countQueue() );
			if( $this->logic->countQueue( array( 'status' => '<'.Model_Mail::STATUS_SENT ) ) ){
				foreach( $this->logic->getQueuedMails( array( 'status' => '<'.Model_Mail::STATUS_SENT ) ) as $mail ){
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
		$this->restart( NULL, TRUE );
	}

	public function view( $mailId ){
		$mail			= $this->logic->getMail( $mailId );
		$mail->parts	= $this->logic->getMailParts( $mail );
		$this->addData( 'mail', $mail );
	}
}
?>
