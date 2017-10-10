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
			$this->restart( '?page='.$this->request->get( 'page' ), TRUE );
		}
		else{
			$this->model->edit( $eventId, array(
				'status'	=> Model_Mangopay_Event::STATUS_CLOSED,
				'output'	=> $event->output.'<br/><strong>CLOSED MANUALLY</strong>',
				'handledAt'	=> time,
			), FALSE );
			$this->restart( 'view/'.$eventId.'?page='.$this->request->get( 'page' ), TRUE );
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
		$this->restart( 'view/'.$eventId.'?page='.$this->request->get( 'page' ), TRUE );
	}

	protected function handleEvent( $eventId ){
		$event	= $this->model->get( $eventId );
		if( !$event )
			throw new InvalidArgumentException( 'Invalid event id' );
		if( $event->status == Model_Mangopay_Event::STATUS_CLOSED )
			throw new RuntimeException( 'Event already handled' );


		$key		= strtolower( str_replace( "_", " ", $event->type ) );
		$className	= str_replace( " ", "_", ucwords( 'Logic Payment Mangopay Event '.$key ) );

		if( !class_exists( $className ) ){
			$status		= Model_Mangopay_Event::STATUS_HANDLED;
			$output		= 'No handler available: '.$className;
		}
		else {
			$buffer		= new UI_OutputBuffer();
			$logicKey	= Alg_Text_CamelCase::convert( 'Payment Mangopay Event '.$key, TRUE, TRUE );
			try{
				$logicEvent	= $this->env->logic->get( $logicKey );
				$logicEvent->setEvent( $event )->handle();
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
			if( !strlen( $eventType = $this->request->get( 'EventType' ) ) )
				throw new InvalidArgumentException( 'Event type is missing' );
			if( !strlen( $resourceId = $this->request->get( 'ResourceId' ) ) )
				throw new InvalidArgumentException( 'Resource ID is missing' );
			if( !strlen( $date = $this->request->get( 'Date' ) ) )
				throw new InvalidArgumentException( 'Event date is missing' );

			$indices	= array( 'type' => $eventType, 'id' => $resourceId );
			if( 0 && $event = $this->model->getByIndices( $indices ) ){
				$this->sendMail( 'EventAgain', array( 'event' => $event ) );
				throw new InvalidArgumentException( 'Event has been received before' );
			}
			if( !$this->verify( $eventType, $resourceId ) )
				throw new InvalidArgumentException( 'Event verification failed' );
			$eventId	= $this->model->add( array(
				'status'		=> Model_Mangopay_Event::STATUS_RECEIVED,
				'id'			=> $resourceId,
				'type'			=> $eventType,
				'triggeredAt'	=> $date,
				'receivedAt'	=> time(),
				'output'		=> '',
				'handledAt'		=> 0,
			) );
			$this->handleEvent( $eventId );
			$response->setStatus( 200 );
			$response->setBody( '<h1>OK</h1><p>Event has been received and handled.</p>' );
		}
		catch( InvalidArgumentException $e ){
			$this->sendMail( 'EventFailed', array( 'eventId' => $eventId, 'exception' => $e ) );
			$response->setStatus( 400 );
			$response->setBody( '<h1>Bad Request</h1><p>Insufficient data given.</p>' );
		}
		catch( Exception $e ){
			$this->sendMail( 'EventFailed', array( 'eventId' => $eventId, 'exception' => $e ) );
			if( $eventId ){
				$this->model->edit( $eventId, array(
 					'status'	=> Model_Mangopay_Event::STATUS_FAILED,
					'output'	=> $e->getTraceAsString(),
					'handledAt'	=> time(),
				), FALSE );
			}
			$response->setStatus( 500 );
			$response->setBody( '<h1>Internal Server Error</h1><p>An error occured. Event has not been handled.</p>' );
		}
		Net_HTTP_Response_Sender::sendResponse( $response );
		exit;
	}

	protected function verify( $eventType, $resourceId ){
		return TRUE;
		$status = NULL;
		switch( $eventType ){
			case 'PAYIN_NORMAL_CREATED':
			case 'PAYOUT_NORMAL_CREATED':
			case 'TRANSFER_NORMAL_CREATED':
				$status	= 'CREATED';
				break;
			case 'PAYIN_NORMAL_FAILED':
			case 'PAYOUT_NORMAL_FAILED':
			case 'TRANSFER_NORMAL_FAILED':
				$status	= 'FAILED';
				break;
			case 'PAYIN_NORMAL_SUCCEEDED':
			case 'PAYOUT_NORMAL_SUCCEEDED':
			case 'TRANSFER_NORMAL_SUCCEEDED':
				$status	= 'SUCCEEDED';
				break;
			default:
				return TRUE;
		}
		if( !$status )								//  no handlable event found
			return TRUE;							//  return TRUE for now
		$entity	= $this->mangopay->getEventResource( $eventType, $resourceId );
		if( $entity && $entity->Status === $status )
			return TRUE;
		$this->sendMail( 'EventUnverfied', array(
			'entity'	=> $entity,
			'event'		=> (object) array(
				'eventType'		=> $eventType,
				'resourceId'	=> $resourceId,
			),
		) );
		return FALSE;
	}

	protected function sendMail( $type, $data ){
		if( !$this->moduleConfig->get( 'mail.hook' ) )
			return;
		$className	= 'Mail_Mangopay_'.$type;
		$arguments	= array( $this->env, $data );
		$mail		= Alg_Object_Factory::createObject( $className, $arguments );
		$receiver	= array( 'email' => $this->moduleConfig->get( 'mail.hook' ) );
		$language	= $this->env->getLanguage()->getLanguage();
		return $this->env->logic->mail->sendMail( $mail, $receiver, $language );
	}

	public function view( $eventId, $run = NULL ){

//		print_m( $this->mangopay->getPayin( 34702094 ) );die;

		$event	= $this->model->get( $eventId );
		if( !$event ){
			$this->messenger->noteError( 'Invalid event ID.' );
			$this->restart( '?page='.$this->request->get( 'page' ), TRUE );
		}
		$this->addData( 'event', $event );
		$this->addData( 'page', $this->request->get( 'page' ) );
	}
}
?>
