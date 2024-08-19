<?php

use CeusMedia\Common\Alg\Obj\Factory as ObjectFactory;
use CeusMedia\HydrogenFramework\Logic;

abstract class Logic_Payment_Stripe_Event extends Logic
{
	protected $entity;
	protected $event;
	protected Logic_Payment_Stripe $logicStripe;

	abstract public function handle();

	public function setEvent( $event ): self
	{
		$this->event	= $event;
		$this->entity	= $this->logicStripe->getEventResource( $event->type, $event->id );
		return $this;
	}

	protected function __onInit(): void
	{
		parent::__onInit();
		/** @noinspection PhpFieldAssignmentTypeMismatchInspection */
		$this->logicStripe		= Logic_Payment_Stripe::getInstance( $this->env );

		/*  -- MORE PERFORMANT VERSION  --  */
//		$this->env->logic->add( 'Logic_Payment_Stripe' );
//		later get logic object by: $this->env->logic->paymentStripe;
	}

	protected function sendMail( string $mailClass, $data, object $receiver, ?string $language = NULL ): bool
	{
		$className	= 'Mail_'.$mailClass;
		if( !class_exists( $className ) )
			throw new RuntimeException( 'Mail class "'.$className.'" is not existing' );

		$arguments	= [$this->env, $data];
		$mail		= ObjectFactory::createObject( $className, $arguments );
		/** @var Logic_Mail $logic */
		$logic		= $this->env->getLogic()->get( 'Mail' );
		return $logic->sendMail( $mail, $receiver );
	}

	protected function uncache( string $key ): void
	{
		$this->env->getCache()->remove( 'stripe_'.$key );
	}
}
