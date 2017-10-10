<?php
abstract class Logic_Payment_Mangopay_Event extends CMF_Hydrogen_Logic{

	protected $logicMangopay;
	protected $event;

	public function __onInit(){
		parent::__onInit();
		$this->logicMangopay	= Logic_Payment_Mangopay::getInstance( $this->env );

		/*  -- MORE PERFORMANT VERSION  --  */
//		$this->env->logic->add( 'Logic_Payment_Mangopay' );
//		later get logic object by: $this->env->logic->paymentMangopay;
	}

	public function setEvent( $event ){
		$this->event	= $event;
		return $this;
	}

	abstract public function handle();

	protected function sendMail( $mailClass, $data, $receiver, $language = NULL ){
		$className	= 'Mail_'.$mailClass;
		if( !class_exists( $className ) )
			throw new RuntimeException( 'Mail class "'.$className.'" is not existing' );

		$arguments	= array( $this->env, $data );
		$mail		= Alg_Object_Factory::createObject( $className, $arguments );
		$this->env->logic->mail->sendMail( $mail, $receiver, $language );
	}
}
