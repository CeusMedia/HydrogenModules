<?php

use CeusMedia\Common\Alg\Obj\Factory as ObjectFactory;
use CeusMedia\HydrogenFramework\Logic;

abstract class Logic_Payment_Mangopay_Event extends Logic
{
	protected $entity;
	protected $event;
	protected $logicMangopay;

	abstract public function handle();

	public function setEvent( $event )
	{
		$this->event	= $event;
		$this->entity	= $this->logicMangopay->getEventResource( $event->type, $event->id );
		return $this;
	}

	protected function __onInit(): void
	{
		parent::__onInit();
		$this->logicMangopay	= Logic_Payment_Mangopay::getInstance( $this->env );

		/*  -- MORE PERFORMANT VERSION  --  */
//		$this->env->logic->add( 'Logic_Payment_Mangopay' );
//		later get logic object by: $this->env->logic->paymentMangopay;
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
		$this->env->getCache()->remove( 'mangopay_'.$key );
	}
}
