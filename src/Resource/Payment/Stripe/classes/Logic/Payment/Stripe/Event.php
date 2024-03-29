<?php

use CeusMedia\Common\Alg\Obj\Factory as ObjectFactory;
use CeusMedia\HydrogenFramework\Logic;

abstract class Logic_Payment_Stripe_Event extends Logic
{
	protected $entity;
	protected $event;
	protected $logicStripe;

	abstract public function handle();

	public function setEvent( $event )
	{
		$this->event	= $event;
		$this->entity	= $this->logicStripe->getEventResource( $event->type, $event->id );
		return $this;
	}

	protected function __onInit(): void
	{
		parent::__onInit();
		$this->logicStripe		= Logic_Payment_Stripe::getInstance( $this->env );

		/*  -- MORE PERFORMANT VERSION  --  */
//		$this->env->logic->add( 'Logic_Payment_Stripe' );
//		later get logic object by: $this->env->logic->paymentStripe;
	}

	protected function sendMail( $mailClass, $data, $receiver, $language = NULL )
	{
		$className	= 'Mail_'.$mailClass;
		if( !class_exists( $className ) )
			throw new RuntimeException( 'Mail class "'.$className.'" is not existing' );

		$arguments	= [$this->env, $data];
		$mail		= ObjectFactory::createObject( $className, $arguments );
		$this->env->logic->mail->sendMail( $mail, $receiver, $language );
	}

	protected function uncache( $key )
	{
		$this->env->getCache()->remove( 'stripe_'.$key );
	}
}
