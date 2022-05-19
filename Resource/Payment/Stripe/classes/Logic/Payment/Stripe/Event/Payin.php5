<?php
abstract class Logic_Payment_Stripe_Event_Payin extends Logic_Payment_Stripe_Event
{
	protected $modelPayin;

	protected function __onInit()
	{
		parent::__onInit();
		$this->modelPayin	= new Model_Stripe_Payin( $this->env );
	}
}
