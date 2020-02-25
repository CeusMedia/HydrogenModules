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

	public function html( $messageId ){
		try{
			$message	= $this->checkId( $messageId );
			$object		= $this->logicMessage->getMessageObject( $message );
			$content	= $object->getHTML()->getContent();

			$modules	= $this->env->getModules();
			if( $modules->has( 'UI_Bootstrap' ) ){
				$version	= $modules->get( 'UI_Bootstrap' )->config['version']->value;
				$pathThemes	= $this->env->getConfig()->get( 'path.themes' );
				$styleFile	= $pathThemes.'common/css/bootstrap/'.$version.'/css/bootstrap.min.css';
				$style		= FS_File_Reader::load( $styleFile );
				$style		= UI_HTML_Tag::create( 'style', $style );
				$content	= str_replace( '<head>', '<head>'.$style, $content );
			}
		}
		catch( Exception $e ){
			$content	= UI_HTML_Exception_Page::render( $e );
		}
		$response	= $this->env->getResponse();
		$response->setBody( $content );
		Net_HTTP_Response_Sender::sendResponse( $response, NULL, TRUE, TRUE );
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

/*	public function remove( $messageId ){
		$message	= $this->checkId( $messageId );
		if( $message ){
			$this->modelMessage->remove( $messageId );
			$this->restart( NULL, TRUE );
		}
	}*/

	public function parseAgainFromRaw( $messageId ){
		$message		= $this->checkId( $messageId );
		if( $message->status == Model_Mail_Group_Message::STATUS_NEW ){
			$rawMail	= bzdecompress(substr($message->raw,6));
			$parser		= new \CeusMedia\Mail\Message\Parser();
			$message->object		= 'BZIP2:'.bzcompress( serialize( $parser->parse( $rawMail ) ) );
			$this->modelMessage->edit( $messageId, array( 'object' => $message->object ), FALSE );
		}
		$this->restart( 'view/'.$messageId );
	}

	public function view( $messageId ){
		$message		= $this->checkId( $messageId );
		$message->raw		= $this->logicMessage->getMessageRawMail( $message );
		$message->object	= $this->logicMessage->getMessageObject( $message );
		$this->addData( 'message', $message );
	}
}
