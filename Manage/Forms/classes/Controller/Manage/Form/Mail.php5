<?php
class Controller_Manage_Form_Mail extends CMF_Hydrogen_Controller{

	protected $modelForm;
	protected $modelMail;

	protected function __onInit(){
		$this->modelForm	= new Model_Form( $this->env );
		$this->modelMail	= new Model_Form_Mail( $this->env );
	}

	protected function checkId( $mailId ){
		if( !$mailId )
			throw new RuntimeException( 'No mail ID given' );
		if( !( $mail = $this->modelMail->get( $mailId ) ) )
			throw new DomainException( 'Invalid mail ID given' );
		return $mail;
	}

	protected function checkIsPost(){
		if( !$this->env->getRequest()->isMethod( 'POST' ) )
			throw new RuntimeException( 'Access denied: POST requests, only' );
	}

	public function add(){
		if( $this->env->getRequest()->has( 'save' ) ){
			$data		= $this->env->getRequest()->getAll();
			$mailId	= $this->modelMail->add( $data, FALSE );
			$this->restart( 'edit/'.$mailId, TRUE );
		}
	}

	public function edit( $mailId ){
		$mail	= $this->checkId( $mailId );
		if( $this->env->getRequest()->has( 'save' ) ){
			$data	= $this->env->getRequest()->getAll();
			$this->modelMail->edit( $mailId, $data, FALSE );
			$this->restart( 'edit/'.$mailId );
		}
		$this->addData( 'mail', $mail );
	}

	public function index(){
		$mails	= $this->modelMail->getAll( array(), array( 'title' => 'ASC' ) );
		$this->addData( 'mails', $mails );
	}

	public function remove( $mailId ){
		$this->checkId( $mailId );
		$this->modelMail->remove( $mailId );
		$this->restart( NULL, TRUE );
	}
}

