<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Alg\Obj\Factory as ObjectFactory;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\Common\Net\HTTP\Response\Sender as HttpResponseSender;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;

class Controller_Admin_Payment_Mangopay_Event extends Controller
{
	public static bool $verbose	= TRUE;

	protected HttpRequest $request;
	protected Logic_Payment_Mangopay $mangopay;
	protected Model_Mangopay_Event $model;
	protected ?MessengerResource $messenger;
	protected Dictionary $moduleConfig;

	public function close( $eventId )
	{
		$event	= $this->checkEvent( $eventId, 'view/'.$eventId.'?page='.$this->request->get( 'page' ) );
		$this->model->edit( $eventId, [
			'status'	=> Model_Mangopay_Event::STATUS_CLOSED,
			'output'	=> $event->output.'<br/><strong>CLOSED MANUALLY</strong>',
			'handledAt'	=> time(),
		], FALSE );
		$this->restart( 'view/'.$eventId.'?page='.$this->request->get( 'page' ), TRUE );
	}

	public function index( $page = 0 )
	{
		$limit		= 10;
		$conditions = [];
		$orders		= ['eventId' => 'DESC'];
		$limits		= [$page * $limit, $limit];

		$total		= $this->model->count( $conditions );
		$events		= $this->model->getAll( $conditions, $orders, $limits );
		$this->addData( 'events', $events );
		$this->addData( 'eventTypes', $this->model->types );
		$this->addData( 'page', $page );
		$this->addData( 'pages', ceil( $total / $limit ) );
	}

	public function receive()
	{
		$response	= $this->env->getResponse();
		$eventId	= 0;
		try{
			if( $this->request->has( 'RessourceId' ) )
				$this->request->set( 'ResourceId', $this->request->get( 'RessourceId' ) );
			if( !strlen( $eventType = $this->request->get( 'EventType' ) ) )
				throw new InvalidArgumentException( 'Event type is missing' );
			if( !strlen( $resourceId = $this->request->get( 'ResourceId' ) ) )
				throw new InvalidArgumentException( 'Resource ID is missing' );
			if( !strlen( $date = $this->request->get( 'Date' ) ) )
				throw new InvalidArgumentException( 'Event date is missing' );

			$indices	= ['type' => $eventType, 'id' => $resourceId];
			if( $event = $this->model->getByIndices( $indices ) ){
				$this->sendMail( 'EventAgain', ['event' => $event] );
				throw new InvalidArgumentException( 'Event has been received before' );
			}
			if( !$this->verify( $eventType, $resourceId ) )
				throw new InvalidArgumentException( 'Event verification failed' );
			$eventId	= $this->model->add( [
				'status'		=> Model_Mangopay_Event::STATUS_RECEIVED,
				'id'			=> $resourceId,
				'type'			=> $eventType,
				'triggeredAt'	=> $date,
				'receivedAt'	=> time(),
				'output'		=> '',
				'handledAt'		=> 0,
			] );
			$response->setStatus( 200 );
			$response->setBody( '<h1>OK</h1><p>Event has been received and handled.</p>' );
		}
		catch( InvalidArgumentException $e ){
			$this->sendMail( 'EventFailed', ['eventId' => $eventId, 'exception' => $e] );
			$response->setStatus( 400 );
			$response->setBody( '<h1>Bad Request</h1><p>Insufficient data given. Event has not been handled.</p><p>Reason: '.$e->getMessage().'.</p>' );
		}
		catch( Exception $e ){
			$this->sendMail( 'EventFailed', ['eventId' => $eventId, 'exception' => $e] );
			$response->setStatus( 500 );
			$response->setBody( '<h1>Internal Server Error</h1><p>An error occured. Event has not been handled.</p><p>'.$e->getMessage().'.</p>' );
		}
		HttpResponseSender::sendResponse( $response );
		exit;
	}

	public function retry( $eventId )
	{
		$event	= $this->checkEvent( $eventId );
		$statuses	= [Model_Mangopay_Event::STATUS_FAILED, Model_Mangopay_Event::STATUS_HANDLED];
		if( !in_array( (int) $event->status, $statuses ) ){
			$this->messenger->noteError( 'Only failed or unsuccessful events can be reactivated.' );
			$this->restart( 'view/'.$eventId, TRUE );
		}
		$this->model->edit( $eventId, [
			'status'	=> Model_Mangopay_Event::STATUS_RECEIVED,
		] );
		$this->restart( 'view/'.$eventId.'?page='.$this->request->get( 'page' ), TRUE );
	}

	/**
	 *	@param		$type
	 *	@param		$data
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function sendMail( $type, $data )
	{
		if( !$this->moduleConfig->get( 'mail.hook' ) )
			return;
		$className	= 'Mail_Mangopay_'.$type;
		$arguments	= [$this->env, $data];
		$mail		= ObjectFactory::createObject( $className, $arguments );
		$receiver	= ['email' => $this->moduleConfig->get( 'mail.hook' )];
		$language	= $this->env->getLanguage()->getLanguage();
		return $this->env->getLogic()->get( 'Mail' )->sendMail( $mail, $receiver, $language );
	}

	public function view( $eventId ): void
	{
		$event	= $this->model->get( $eventId );
		if( !$event ){
			$this->messenger->noteError( 'Invalid event ID.' );
			$this->restart( '?page='.$this->request->get( 'page' ), TRUE );
		}
		$this->addData( 'event', $event );
		$this->addData( 'page', $this->request->get( 'page' ) );
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->request		= $this->env->getRequest();
		$this->messenger	= $this->env->getMessenger();
		$this->mangopay		= Logic_Payment_Mangopay::getInstance( $this->env );
		$this->model		= new Model_Mangopay_Event( $this->env );
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.resource_payment_mangopay.', TRUE );
	}

	protected function checkEvent( $eventId, string $failUrl = 'view/%s' )
	{
		$event	= $this->model->get( $eventId );
		if( $event )
			return $event;
		$this->messenger->noteError( 'Invalid event ID.' );
		$this->restart( sprintf( $failUrl, $eventId ), TRUE );
	}

	protected function verify( $eventType, $resourceId ): bool
	{
		if( preg_match( '@_CREATED$@', $eventType ) )
			$status	= 'CREATED';
		else if( preg_match( '@_FAILED$@', $eventType ) )
			$status	= 'FAILED';
		else if( preg_match( '@_SUCCEEDED$@', $eventType ) )
			$status	= 'SUCCEEDED';
		else return TRUE;														//  no handleable and verifiable event found
		$entity	= $this->mangopay->getEventResource( $eventType, $resourceId );
		if( $entity && $entity->Status === $status )
			return TRUE;
		$this->sendMail( 'EventUnverified', [
			'entity'	=> $entity,
			'event'		=> (object) [
				'eventType'		=> $eventType,
				'resourceId'	=> $resourceId,
			],
		] );
		return FALSE;
	}
}
