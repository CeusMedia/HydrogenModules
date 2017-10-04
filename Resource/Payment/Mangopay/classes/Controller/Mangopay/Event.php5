<?php
class Controller_Mangopay_Event extends CMF_Hydrogen_Controller{

	public static $verbose	= TRUE;

	public function __onInit(){
		$this->request		= $this->env->getRequest();
		$this->messenger	= $this->env->getMessenger();
		$this->mangopay		= Logic_Payment_Mangopay::getInstance( $this->env );
		$this->model		= new Model_Mangopay_Event( $this->env );
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.resource_payment_mangopay.', TRUE );
	}

	public function close( $eventId ){
		$event	= $this->model->get( $eventId );
		if( !$event ){
			$this->messenger->noteError( 'Invalid event ID.' );
			$this->restart( NULL, TRUE );
		}
		else{
			$this->model->edit( $eventId, array(
				'status'	=> Model_Mangopay_Event::STATUS_CLOSED,
				'output'	=> $event->output.'<br/><strong>CLOSED MANUALLY</strong>',
				'handledAt'	=> time,
			), FALSE );
			$this->restart( 'view/'.$eventId, TRUE );
		}
	}

	public function handle( $eventId ){
		try{
			$this->handleEvent( $eventId );
			$event	= $this->model->get( $eventId );
			if( $event->status == Model_Mangopay_Event::STATUS_CLOSED )
				$this->messenger->noteSuccess( 'Event handled.' );
			else if( $event->status == Model_Mangopay_Event::STATUS_HANDLED )
				$this->messenger->noteNotice( 'Event handled but not closed.' );
			else if( $event->status == Model_Mangopay_Event::STATUS_FAILED )
				$this->messenger->noteError( 'Handling event failed.' );
		}
		catch( Exception $e ){
			$this->messenger->noteError( 'Error: '.$e->getMessage() );
		}
		$this->restart( 'view/'.$eventId, TRUE );
	}

	protected function handleEvent( $eventId ){
		$event	= $this->model->get( $eventId );
		if( !$event )
			throw new InvalidArgumentException( 'Invalid event id' );
		if( $event->status == Model_Mangopay_Event::STATUS_CLOSED )
			throw new RuntimeException( 'Event already handled' );
		$key	= strtolower( str_replace( "_", " ", $event->type ) );
		$method = Alg_Text_CamelCase::convert( 'handle '.$key, TRUE, TRUE );
		if( !method_exists( $this, $method ) ){
			$status		= Model_Mangopay_Event::STATUS_HANDLED;
			$output		= 'No handler available: '.$method;
		}
		else {
			$buffer		= new UI_OutputBuffer();
			$arguments	= array( $event->type, $event->id, $event->triggeredAt );
			$callable	= array( $this, $method );
			try{
				call_user_func_array( $callable, $arguments );
				$status		= Model_Mangopay_Event::STATUS_CLOSED;
			}
			catch( Exception $e ){
				$status		= Model_Mangopay_Event::STATUS_FAILED;
			}
			$output		= $buffer->get( TRUE );
		}
		return $this->model->edit( $eventId, array(
			'status'	=> $status,
			'output'	=> $output,
			'handledAt'	=> time(),
		), FALSE );
	}

	protected function handleTest( $eventType, $resourceId, $date ){
		remark( "Test!");
		remark( "ResourceId: ".$resourceId );
		remark( "Date: ".$date );
	}

	public function index( $page = 0 ){
		$limit		= 10;
		$conditions = array();
		$orders		= array( 'eventId' => 'DESC' );
		$limits		= array( $page * $limit, $limit );

		$total		= $this->model->count( $conditions );
		$events		= $this->model->getAll( $conditions, $orders, $limits );
		$this->addData( 'events', $events );
		$this->addData( 'eventTypes', $this->model->types );
		$this->addData( 'page', $page );
		$this->addData( 'pages', ceil( $total / $limit ) );
	}

	public function receive(){
		$response	= $this->env->getResponse();
		$eventId	= 0;
		try{
			if( $this->request->has( 'RessourceId' ) )
				$this->request->set( 'ResourceId', $this->request->get( 'RessourceId' ) );
			if( !$this->request->has( 'EventType' ) )
				throw new InvalidArgumentException( 'Event type is missing' );
			if( !$this->request->has( 'ResourceId' ) )
				throw new InvalidArgumentException( 'Resource ID is missing' );
			if( !$this->request->has( 'Date' ) )
				throw new InvalidArgumentException( 'Event date is missing' );
			$eventId	= $this->model->add( array(
				'status'		=> Model_Mangopay_Event::STATUS_RECEIVED,
				'id'			=> $this->request->get( 'ResourceId' ),
				'type'			=> $this->request->get( 'EventType' ),
				'triggeredAt'	=> $this->request->get( 'Date' ),
				'receivedAt'	=> time(),
				'handledAt'		=> 0,
			) );
			$this->handleEvent( $eventId );
		}
		catch( InvalidArgumentException $e ){
			$this->sendMail( 'EventFailed', array( 'eventId' => $eventId, 'exception' => $e ) );
			$response->setStatus( 400 );
			$response->setBody( '<h1>Bad Request</h1><p>Insufficient data given.</p>' );
		}
		catch( Exception $e ){
			$output		= $buffer->get( TRUE );
			$this->sendMail( 'EventFailed', array( 'eventId' => $eventId, 'exception' => $e ) );
			if( $eventId ){
				$this->model->edit( $eventId, array(
 					'status'	=> Model_Mangopay_Event::STATUS_FAILED,
					'output'	=> $output,
					'handledAt'	=> time(),
				), FALSE );
			}
			$response->setStatus( 500 );
			$response->setBody( '<h1>Internal Server Error</h1><p>An error occured. Event has not been handled.</p>' );
		}
		Net_HTTP_Response_Sender::sendResponse( $response );
		exit;
	}

	protected function sendMail( $type, $data ){
		if( !$this->moduleConfig->get( 'mail.hook' ) )
			return;
		$className	= 'Mail_Mangopay_'.$type;
		$arguments	= array( $this->env, $data );
		$mail		= Alg_Object_Factory::createObject( $className, $arguments );
		$receiver	= array( 'email' => $this->moduleConfig->get( 'mail.hook' ) );
		$logic		= new Logic_Mail( $this->env );
		$language	= $this->env->getLanguage()->getLanguage();
		return $logic->handleMail( $mail, $receiver, $language );
	}

	public function view( $eventId, $run = NULL ){
		$event	= $this->model->get( $eventId );
		if( !$event ){
			$this->messenger->noteError( 'Invalid event ID.' );
			$this->restart( NULL, TRUE );
		}
		$this->addData( 'event', $event );
	}
}
?>
