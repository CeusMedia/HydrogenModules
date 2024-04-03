<?php

use CeusMedia\Common\Alg\Text\CamelCase;
use CeusMedia\Common\UI\OutputBuffer;

class Job_Stripe_Event extends Job_Abstract
{
	protected Model_Stripe_Event $modelEvent;

	public function handle()
	{
		$orders	= ['eventId' => 'ASC'];
		$events	= $this->modelEvent->getAllByIndex( 'status', Model_Stripe_Event::STATUS_RECEIVED, $orders );
		foreach( $events as $event ){
			print( 'Handling event '.$event->eventId.' ('.$event->type.') ... ' );
			try{
				$this->handleEvent( $event->eventId );
				$this->out( "OK" );
			}
			catch( Exception $e ){
				$this->out( "FAIL" );
				$this->out( 'Exception: '.$e->getMessage() );
				$this->out( $e->getTraceAsString() );
			}
		}
	}

	public function count(): void
	{
		$model	= new Model_Stripe_Event( $this->env );
		$count	= $model->countByIndex( 'status', Model_Stripe_Event::STATUS_RECEIVED );
		$this->out( 'Found '.$count.' unhandled events.' );
	}

	protected function __onInit(): void
	{
		$this->modelEvent	= new Model_Stripe_Event( $this->env );
	}

	protected function handleEvent( string $eventId ): int
	{
		$event	= $this->modelEvent->get( $eventId );
		if( !$event )
			throw new InvalidArgumentException( 'Invalid event id' );
		if( $event->status == Model_Stripe_Event::STATUS_CLOSED )
			throw new RuntimeException( 'Event already handled' );

		$key		= strtolower( str_replace( "_", " ", $event->type ) );
		$className	= str_replace( " ", "_", ucwords( 'Logic Payment Stripe Event '.$key ) );

		if( !class_exists( $className ) ){
			$status		= Model_Stripe_Event::STATUS_HANDLED;
			$output		= 'No handler available: '.$className;
		}
		else {
			$buffer		= new OutputBuffer();
			$logicKey	= CamelCase::convert( 'Payment Stripe Event '.$key, TRUE );
//			try{
			$logicEvent	= $this->env->logic->get( $logicKey );
			$logicEvent->setEvent( $event )->handle();
			$status		= Model_Stripe_Event::STATUS_CLOSED;
/*			}
			catch( Exception $e ){
				print( 'Exception: '.$e->getMessage().PHP_EOL );
				print( $e->getTraceAsString() );
				$status		= Model_Stripe_Event::STATUS_FAILED;
				$this->out( 'Exception: '.$e->getMessage() );
				$this->out( $e->getTraceAsString() );
			}
*/			$output		= $buffer->get( TRUE );
		}
		return $this->modelEvent->edit( $eventId, [
			'status'	=> $status,
			'output'	=> $output,
			'handledAt'	=> time(),
		], FALSE );
	}
}
