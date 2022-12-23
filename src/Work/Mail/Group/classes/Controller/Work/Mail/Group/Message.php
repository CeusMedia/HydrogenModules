<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\FS\File\Reader as FileReader;
use CeusMedia\Common\Net\HTTP\Response\Sender as HttpResponseSender;
use CeusMedia\Common\UI\HTML\Exception\Page as HtmlExceptionPage;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;
use CeusMedia\Mail\Message\Parser as MailParser;

class Controller_Work_Mail_Group_Message extends Controller
{
	protected Dictionary $request;
	protected Dictionary $session;
	protected MessengerResource $messenger;
	protected Logic_Mail_Group $logicGroup;
	protected Logic_Mail_Group_Message $logicMessage;
	protected Model_Mail_Group_Message $modelMessage;
	protected string $filterPrefix;

	public function checkId( $messageId )
	{
		return $this->logicMessage->checkId( $messageId );
	}

	public function filter( $reset = NULL )
	{
		if( $reset ){
			$this->session->remove( $this->filterPrefix.'groupId' );
		}
		$this->session->set( $this->filterPrefix.'groupId', $this->request->get( 'groupId' ) );
		$this->restart( NULL, TRUE );
	}

	public function html( $messageId )
	{
		try{
			$message	= $this->checkId( $messageId );
			$object		= $this->logicMessage->getMessageObject( $message );
			$content	= $object->getHTML()->getContent();

			$modules	= $this->env->getModules();
			if( $modules->has( 'UI_Bootstrap' ) ){
				$version	= $modules->get( 'UI_Bootstrap' )->config['version']->value;
				$pathThemes	= $this->env->getConfig()->get( 'path.themes' );
				$styleFile	= $pathThemes.'common/css/bootstrap/'.$version.'/css/bootstrap.min.css';
				$style		= FileReader::load( $styleFile );
				$style		= HtmlTag::create( 'style', $style );
				$content	= str_replace( '<head>', '<head>'.$style, $content );
			}
		}
		catch( Exception $e ){
			$content	= HtmlExceptionPage::render( $e );
		}
		$response	= $this->env->getResponse();
		$response->setBody( $content );
		HttpResponseSender::sendResponse( $response, NULL, TRUE, TRUE );
	}

	public function index( $page = 0 )
	{
		$filterGroupId	= $this->session->get( $this->filterPrefix.'groupId' );

		$limit		= 15;
		$indices	= [];
		if( $filterGroupId )
			$indices['mailGroupId']	= $filterGroupId;
		$orders		= ['createdAt' => 'ASC'];
		$limits		= [abs( $page ) * $limit, $limit];
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

/*	public function setStatus( $messageId, $status )
	{
		$server	= $this->checkId( $messageId );
		if( $server ){
			$this->modelMessage->edit( $messageId, [
				'status'		=> (int) $status,
				'modifiedAt'	=> time(),
			] );
		}
	}*/

/*	public function remove( $messageId ){
		$message	= $this->checkId( $messageId );
		if( $message ){
			$this->modelMessage->remove( $messageId );
			$this->restart( NULL, TRUE );
		}
	}*/

	public function parseAgainFromRaw( $messageId )
	{
		$message		= $this->checkId( $messageId );
		if( $message->status == Model_Mail_Group_Message::STATUS_NEW ){
			$rawMail	= bzdecompress(substr($message->raw,6));
			$parser		= new MailParser();
			$message->object		= 'BZIP2:'.bzcompress( serialize( $parser->parse( $rawMail ) ) );
			$this->modelMessage->edit( $messageId, ['object' => $message->object], FALSE );
		}
		$this->restart( 'view/'.$messageId );
	}

	public function view( $messageId )
	{
		$message		= $this->checkId( $messageId );
		$message->raw		= $this->logicMessage->getMessageRawMail( $message );
		$message->object	= $this->logicMessage->getMessageObject( $message );
		$this->addData( 'message', $message );
	}

	protected function __onInit(): void
	{
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->messenger	= $this->env->getMessenger();
		$this->logicGroup	= new Logic_Mail_Group( $this->env );
		$this->logicMessage	= new Logic_Mail_Group_Message( $this->env );
		$this->modelMessage	= new Model_Mail_Group_Message( $this->env );
		$this->filterPrefix	= 'filter_work_mail_group_message_';
	}
}
