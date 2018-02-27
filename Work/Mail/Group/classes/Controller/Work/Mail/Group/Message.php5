<?php
class Controller_Work_Mail_Group_Message extends CMF_Hydrogen_Controller{

	protected $request;
	protected $session;
	protected $messenger;
	protected $logic;
	protected $modelMessage;

	public function __onInit(){
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->messenger	= $this->env->getMessenger();
		$this->logicGroup	= new Logic_Mail_Group( $this->env );
		$this->logicMessage	= new Logic_Mail_Group_Message( $this->env );
		$this->modelMessage	= new Model_Mail_Group_Message( $this->env );
		$this->filterPrefix	= 'filter_work_mail_group_message_';
	}

	public function checkId( $messageId ){
		return $this->logicMessage->checkId( $messageId );
	}

	public function filter( $reset = NULL ){
		if( $reset ){
			$this->session->remove( $this->filterPrefix.'groupId' );
		}
		$this->session->set( $this->filterPrefix.'groupId', $this->request->get( 'groupId' ) );
		$this->restart( NULL, TRUE );
	}

	public function index( $page = 0 ){
		$filterGroupId	= $this->session->get( $this->filterPrefix.'groupId' );

		$limit		= 15;
		$indices	= array();
		if( $filterGroupId )
			$indices['mailGroupId']	= $filterGroupId;
		$orders		= array( 'createdAt' => 'ASC' );
		$limits		= array( abs( $page ) * $limit, $limit );
		$total		= $this->modelMessage->count( $indices );
		$messages	= $this->modelMessage->getAll( $indices, $orders, $limits );
		foreach( $messages as $message )
			$message->object	= $this->logicMessage->getMessageObject( $message );
		$this->addData( 'messages', $messages );
		$this->addData( 'groups', $this->logicGroup->getActiveGroups() );
		$this->addData( 'filterGroupId', $filterGroupId );
		$this->addData( 'page', (int) $page );
		$this->addData( 'pages', ceil( $total / $limit ) );
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

	public function view( $messageId ){
		$message		= $this->checkId( $messageId );
		$message->raw		= $this->logicMessage->getRawMailFromMessage( $message );
		$message->object	= $this->logicMessage->getMessageObject( $message );
		$this->addData( 'message', $message );
	}
}
