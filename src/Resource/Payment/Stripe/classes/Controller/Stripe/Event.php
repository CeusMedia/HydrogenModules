<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Alg\Obj\Factory as ObjectFactory;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\Common\Net\HTTP\Response\Sender as HttpResponseSender;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;

class Controller_Stripe_Event extends Controller
{
//	public static $verbose	= TRUE;
	protected Logic_Payment_Stripe $stripe;
	protected Model_Stripe_Event $model;
	protected HttpRequest $request;
	protected MessengerResource $messenger;
	protected Dictionary $moduleConfig;

	/**
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function receive(): void
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
				'status'		=> Model_Stripe_Event::STATUS_RECEIVED,
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

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->request		= $this->env->getRequest();
		$this->messenger	= $this->env->getMessenger();
		/** @noinspection PhpFieldAssignmentTypeMismatchInspection */
		$this->stripe		= Logic_Payment_Stripe::getInstance( $this->env );
		$this->model		= new Model_Stripe_Event( $this->env );
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.resource_payment_stripe.', TRUE );
	}

	protected function sendMail( string $type, $data )
	{
		if( !$this->moduleConfig->get( 'mail.hook' ) )
			return;
		$className	= 'Mail_Stripe_'.$type;
		$arguments	= [$this->env, $data];
		$mail		= ObjectFactory::createObject( $className, $arguments );
		$receiver	= ['email' => $this->moduleConfig->get( 'mail.hook' )];
		$language	= $this->env->getLanguage()->getLanguage();
		return $this->env->getLogic()->get( 'Mail' )->sendMail( $mail, $receiver, $language );
	}

	protected function verify( string $eventType, $resourceId ): bool
	{
		if( str_ends_with( $eventType, '_CREATED' ) )
			$status	= 'CREATED';
		else if( str_ends_with( $eventType, '_FAILED' ) )
			$status	= 'FAILED';
		else if( str_ends_with( $eventType, '_SUCCEEDED' ) )
			$status	= 'SUCCEEDED';
		else return TRUE;														//  no handleable and verifiable event found
		$entity	= $this->stripe->getEventResource( $eventType, $resourceId );
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
