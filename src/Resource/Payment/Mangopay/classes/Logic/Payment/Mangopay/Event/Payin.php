<?php
abstract class Logic_Payment_Mangopay_Event_Payin extends Logic_Payment_Mangopay_Event
{
	protected Model_Mangopay_Payin $modelPayin;

	protected function __onInit(): void
	{
		parent::__onInit();
		$this->modelPayin	= new Model_Mangopay_Payin( $this->env );
	}
}
