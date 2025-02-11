<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Net\HTTP\Request;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;

class Controller_Work_Mail_Group_Server extends Controller
{
	protected Request $request;
	protected Dictionary $session;
	protected MessengerResource $messenger;
	protected Model_Mail_Group_Server $modelServer;

	public function add(): void
	{
		if( $this->request->has( 'save' ) ){
			$title		= trim( $this->request->get( 'title' ) );
			$imapHost	= trim( $this->request->get( 'imap_host' ) );
			$imapPort	= trim( $this->request->get( 'imap_port' ) );
			$smtpHost	= trim( $this->request->get( 'smtp_host' ) );
			$smtpPort	= trim( $this->request->get( 'smtp_port' ) );
			$this->modelServer->add( [
				'status'		=> $this->request->get( 'status' ),
				'title'			=> $title,
				'imapHost'		=> $imapHost,
				'imapPort'		=> $imapPort,
				'smtpHost'		=> $smtpHost,
				'smtpPort'		=> $smtpPort,
				'createdAt'		=> time(),
				'modifiedAt'	=> time(),
			] );
			$this->restart( NULL, TRUE );
		}
	}

	public function checkId( $serverId, bool $strict = TRUE )
	{
		$server	= $this->modelServer->get( $serverId );
		if( $server )
			return $server;
		if( $strict )
			throw new RangeException( 'Invalid server ID: '.$serverId );
		return NULL;
	}

	public function edit( $serverId ): void
	{
		$server	= $this->checkId( $serverId );
		if( $this->request->has( 'save' ) ){
			$title		= trim( $this->request->get( 'title' ) );
			$imapHost	= trim( $this->request->get( 'imap_host' ) );
			$imapPort	= trim( $this->request->get( 'imap_port' ) );
			$smtpHost	= trim( $this->request->get( 'smtp_host' ) );
			$smtpPort	= trim( $this->request->get( 'smtp_port' ) );
			$this->modelServer->edit( $serverId, [
				'status'		=> $this->request->get( 'status' ),
				'title'			=> $title,
				'imapHost'		=> $imapHost,
				'imapPort'		=> $imapPort,
				'smtpHost'		=> $smtpHost,
				'smtpPort'		=> $smtpPort,
				'modifiedAt'	=> time(),
			] );
			$this->restart( NULL, TRUE );
		}
		$this->addData( 'server', $server );
	}

	public function index(): void
	{
		$indices	= [];
		$orders		= ['title' => 'ASC'];
		$limits		= [];
		$servers	= $this->modelServer->getAll( $indices, $orders,$limits );
		$this->addData( 'servers', $servers );
	}

	public function remove( $serverId ): void
	{
		$server	= $this->checkId( $serverId );
		if( $server ){
			$this->modelServer->remove( $serverId );
			$this->restart( NULL, TRUE );
		}
	}

	public function setStatus( $serverId, $status ): void
	{
		$server	= $this->checkId( $serverId );
		if( $server ){
			$this->modelServer->edit( $serverId, [
				'status'		=> (int) $status,
				'modifiedAt'	=> time(),
			] );
		}
	}

	protected function __onInit(): void
	{
		$this->request			= $this->env->getRequest();
		$this->session			= $this->env->getSession();
		$this->messenger		= $this->env->getMessenger();
		$this->modelServer		= new Model_Mail_Group_Server( $this->env );
	}
}
