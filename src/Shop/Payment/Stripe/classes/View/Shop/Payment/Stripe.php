<?php

use CeusMedia\HydrogenFramework\View;

class View_Shop_Payment_Stripe extends View
{
	public function checkout(): void
	{
	}

	public function index(): void
	{
	}

	public function perCreditCard(): void
	{
		$this->env->getPage()->js->addModuleFile( 'Module.Shop.Payment.Stripe.js' );
		$this->env->getPage()->css->common->addUrl( 'module.shop.payment.stripe.css' );
	}

	protected function __onInit(): void
	{
		$this->env->getPage()->js->addUrl( 'https://js.stripe.com/v3/' );
	}
}
