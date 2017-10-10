<?php
abstract class Logic_Payment_Mangopay_Event_Payin extends Logic_Payment_Mangopay_Event{

	protected $modelPayin;

	public function __onInit(){
		parent::__onInit();
		$this->modelPayin	= new Model_Mangopay_Payin( $this->env );
	}
}
