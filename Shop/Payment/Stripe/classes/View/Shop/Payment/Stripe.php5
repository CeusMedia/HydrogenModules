<?php
class View_Shop_Payment_Stripe extends CMF_Hydrogen_View{

	public function __onInit(){
		$this->env->getPage()->js->addUrl( 'https://js.stripe.com/v3/' );
	}

	public function checkout(){}

	public function index(){}

	public function perCreditCard(){
		$this->env->getPage()->js->addModuleFile( 'Module.Shop.Payment.Stripe.js' );
		$this->env->getPage()->css->common->addUrl( 'module.shop.payment.stripe.css' );
	}
}
?>
