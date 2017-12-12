<?php
class Controller_Work_Mail_Group_Server extends CMF_Hydrogen_Controller{

	protected $modelServer;

	public function __onInit(){
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->messenger	= $this->env->getMessenger();
		$this->modelServer		= new Model_Mail_Group_Server( $this->env );
	}

	public function add(){
		if( $this->request->has( 'save' ) ){
			$title		= trim( $this->request->get( 'title' ) );
			$imapHost	= trim( $this->request->get( 'imap_host' ) );
			$imapPort	= trim( $this->request->get( 'imap_port' ) );
			$smtpHost	= trim( $this->request->get( 'smtp_host' ) );
			$smtpPort	= trim( $this->request->get( 'smtp_port' ) );
			$this->modelServer->add( array(
				'status'		=> $this->request->get( 'status' ),
				'title'			=> $title,
				'imapHost'		=> $imapHost,
				'imapPort'		=> $imapPort,
				'smtpHost'		=> $smtpHost,
				'smtpPort'		=> $smtpPort,
				'createdAt'		=> time(),
				'modifiedAt'	=> time(),
			) );
			$this->restart( NULL, TRUE );
		}
	}

	public function checkId( $serverId ){
		$server	= $this->modelServer->get( $serverId );
		if( $server )
			return $server;
		if( $strict )
			throw new RangeException( 'Invalid server ID: '.$serverId );
		return NULL;
	}

	public function edit( $serverId ){
		$server	= $this->checkId( $serverId );
		if( $this->request->has( 'save' ) ){
			$title		= trim( $this->request->get( 'title' ) );
			$imapHost	= trim( $this->request->get( 'imap_host' ) );
			$imapPort	= trim( $this->request->get( 'imap_port' ) );
			$smtpHost	= trim( $this->request->get( 'smtp_host' ) );
			$smtpPort	= trim( $this->request->get( 'smtp_port' ) );
			$this->modelServer->edit( $serverId, array(
				'status'		=> $this->request->get( 'status' ),
				'title'			=> $title,
				'imapHost'		=> $imapHost,
				'imapPort'		=> $imapPort,
				'smtpHost'		=> $smtpHost,
				'smtpPort'		=> $smtpPort,
				'modifiedAt'	=> time(),
			) );
			$this->restart( NULL, TRUE );
		}
		$this->addData( 'server', $server );
	}

	public function index(){
		$indices	= array();
		$orders		= array( 'title' => 'ASC' );
		$limits		= array();
		$servers	= $this->modelServer->getAll( $indices, $orders,$limits );
		$this->addData( 'servers', $servers );
	}

	public function setStatus( $serverId, $status ){
		$server	= $this->checkId( $serverId );
		if( $server ){
			$this->modelServer->edit( $serverId, array(
				'status'		=> (int) $status,
				'modifiedAt'	=> time(),
			) );
		}
	}

	public function remove(){
		$server	= $this->checkId( $serverId );
		if( $server ){
			$this->modelServer->remove( $serverId );
			$this->restart( NULL, TRUE );
		}
	}
}
