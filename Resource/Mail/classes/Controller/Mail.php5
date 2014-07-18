<?php
class Controller_Mail extends CMF_Hydrogen_Controller{

	protected $logic;

	public function __onInit(){
		$this->logic	= new Logic_Mail( $this->env );
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

	public function index( $offset = NULL ){
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

		$conditions	= array();
		if( $filters->get( 'status' ) )
			$conditions	= array( 'status' => $filters->get( 'status' ) );
		$orders		= array( $filters->get( 'order' ) => $filters->get( 'direction' ) );
		$limits		= array( min( 0, (int) $offset ), $filters->get( 'limit' ) );
		$mails		= $this->logic->getQueuedMails( $conditions, $orders, $limits );
		$this->addData( 'mails', $mails );
		$this->addData( 'filters', $filters );
	}

	public function enqueue(){
		if( $this->request->get( 'enqueue' ) ){
			$aaa	= $this->request->get( 'aaa' );
			try{
				$receiver	= array(
					'email'		=> 'kriss@ceusmedia.de',
					'username'	=> 'kriss'
				);
				$mail	= new Mail_Test( $this->env );
				$mail->setSender( 'dev@ceusmedia.de' );
			//  $mail->sendTo( $receiver );
				$logic->handleMail( $mail, $receiver );
				$this->env->getMessenger()->noteSuccess( "Mail sent ;-)" );
			}
			catch( Exception $e ){
				$this->env->getMessenger()->noteFailure( $e->getMessage() );
			}
//			$this->restart( NULL, TRUE );
		}
	}

	public function send(){
		$logic	= new Logic_Mail( $this->env );
		$count	= $logic->countQueue( array( 'status' => '<2' ) );
		if( $count ){
			$this->env->getMessenger()->noteNotice( "Mails in Queue: ".$logic->countQueue() );
			if( $logic->countQueue( array( 'status' => '<2' ) ) ){
				foreach( $logic->getQueuedMails( array( 'status' => '<2' ) ) as $mail ){
					try{
						$logic->sendQueuedMail( $mail->mailId );
						$this->env->getMessenger()->noteSuccess( "Mail #".$mail->mailId." sent ;-)" );
					}
					catch( Exception $e ){
						$this->env->getMessenger()->noteFailure( $e->getMessage() );
					}
				}
			}
		}
	}
}
?>
