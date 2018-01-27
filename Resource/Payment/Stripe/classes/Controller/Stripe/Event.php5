<?php
class Controller_Stripe_Event extends CMF_Hydrogen_Controller{

//	public static $verbose	= TRUE;

	public function __onInit(){
		$this->request		= $this->env->getRequest();
		$this->messenger	= $this->env->getMessenger();
		$this->stripe		= Logic_Payment_Stripe::getInstance( $this->env );
		$this->model		= new Model_Stripe_Event( $this->env );
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.resource_payment_stripe.', TRUE );
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
			if( $event = $this->model->getByIndices( $indices ) ){
				$this->sendMail( 'EventAgain', array( 'event' => $event ) );
				throw new InvalidArgumentException( 'Event has been received before' );
			}
			if( !$this->verify( $eventType, $resourceId ) )
				throw new InvalidArgumentException( 'Event verification failed' );
			$eventId	= $this->model->add( array(
				'status'		=> Model_Stripe_Event::STATUS_RECEIVED,
				'id'			=> $resourceId,
				'type'			=> $eventType,
				'triggeredAt'	=> $date,
				'receivedAt'	=> time(),
				'output'		=> '',
				'handledAt'		=> 0,
			) );
			$response->setStatus( 200 );
			$response->setBody( '<h1>OK</h1><p>Event has been received and handled.</p>' );
		}
		catch( InvalidArgumentException $e ){
			$this->sendMail( 'EventFailed', array( 'eventId' => $eventId, 'exception' => $e ) );
			$response->setStatus( 400 );
			$response->setBody( '<h1>Bad Request</h1><p>Insufficient data given. Event has not been handled.</p><p>Reason: '.$e->getMessage().'.</p>' );
		}
		catch( Exception $e ){
			$this->sendMail( 'EventFailed', array( 'eventId' => $eventId, 'exception' => $e ) );
			$response->setStatus( 500 );
			$response->setBody( '<h1>Internal Server Error</h1><p>An error occured. Event has not been handled.</p><p>'.$e->getMessage().'.</p>' );
		}
		Net_HTTP_Response_Sender::sendResponse( $response );
		exit;
	}

	protected function sendMail( $type, $data ){
		if( !$this->moduleConfig->get( 'mail.hook' ) )
			return;
		$className	= 'Mail_Stripe_'.$type;
		$arguments	= array( $this->env, $data );
		$mail		= Alg_Object_Factory::createObject( $className, $arguments );
		$receiver	= array( 'email' => $this->moduleConfig->get( 'mail.hook' ) );
		$language	= $this->env->getLanguage()->getLanguage();
		return $this->env->logic->mail->sendMail( $mail, $receiver, $language );
	}

	protected function verify( $eventType, $resourceId ){
		if( preg_match( '@_CREATED$@', $eventType ) )
			$status	= 'CREATED';
		else if( preg_match( '@_FAILED$@', $eventType ) )
			$status	= 'FAILED';
		else if( preg_match( '@_SUCCEEDED$@', $eventType ) )
			$status	= 'SUCCEEDED';
		else return TRUE;														//  no handleable and verifyable event found
		$entity	= $this->stripe->getEventResource( $eventType, $resourceId );
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
}
?>