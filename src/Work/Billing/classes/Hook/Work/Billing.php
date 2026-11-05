<?php

use CeusMedia\HydrogenFramework\Hook;

class Hook_Work_Billing extends Hook
{
	public function onPageApplyModules(): void
	{
		$this->context->js->addScriptOnReady( 'WorkBilling.init()' );
	}

	public function onBillingBillRegisterTab(): void
	{
		$tabs	= $this->env->getLanguage()->getSection( 'work/billing', 'tabs' );
//		$words	= (object) $this->env->getLanguage()->getWords( 'manage/my/user' );				//  load words
//		$this->context->registerTab( '', $words->tabs['user'], 0 );								//  register main tab
		$modelBill	= new Model_Billing_Bill( $this->env );
		$bill		= $modelBill->get( $this->payload['billId'] );
		$this->context->registerTab( 'edit/'.$this->payload['billId'], '<i class="fa fa-fw fa-edit"></i> '.$tabs['data'], 0 );
		$this->context->registerTab( 'breakdown/'.$this->payload['billId'], '<i class="fa fa-fw fa-pie-chart"></i> '.$tabs['breakdown'], 1 );
		$this->context->registerTab( 'transaction/'.$this->payload['billId'], '<i class="fa fa-fw fa-exchange"></i> '.$tabs['transactions'], 2, $bill->status == 0 );
	}

	public function onBillingPersonRegisterTab(): void
	{
		$tabs	= $this->env->getLanguage()->getSection( 'work/billing', 'tabs' );
//		$words	= (object) $this->env->getLanguage()->getWords( 'manage/my/user' );				//  load words
//		$this->context->registerTab( '', $words->tabs['user'], 0 );								//  register main tab
		$this->context->registerTab( 'edit/'.$this->payload['personId'], '<i class="fa fa-fw fa-edit"></i> '.$tabs['data'], 0 );
//		$this->context->registerTab( 'transaction/'.$this->payload['personId'], '<i class="fa fa-fw fa-exchange"></i> '.$tabs['transactions'], 1 );
		$this->context->registerTab( 'reserve/'.$this->payload['personId'], '<i class="fa fa-fw fa-plus-square-o"></i> '.$tabs['reserves'], 1 );
		$this->context->registerTab( 'expense/'.$this->payload['personId'], '<i class="fa fa-fw fa-minus-square-o"></i> '.$tabs['expenses'], 2 );
		$this->context->registerTab( 'payin/'.$this->payload['personId'], '<i class="fa fa-fw fa-sign-in"></i> '.$tabs['payins'], 3 );
		$this->context->registerTab( 'payout/'.$this->payload['personId'], '<i class="fa fa-fw fa-sign-out"></i> '.$tabs['payouts'], 4 );
		$this->context->registerTab( 'unbooked/'.$this->payload['personId'], '<i class="fa fa-fw fa-question-circle-o"></i> '.$tabs['unbooked'], 5 );
	}

	public function onBillingCorporationRegisterTab(): void
	{
		$tabs	= $this->env->getLanguage()->getSection( 'work/billing', 'tabs' );
//		$words	= (object) $this->env->getLanguage()->getWords( 'manage/my/user' );				//  load words
//		$this->context->registerTab( '', $words->tabs['user'], 0 );								//  register main tab
		$this->context->registerTab( 'edit/'.$this->payload['corporationId'], '<i class="fa fa-fw fa-edit"></i> '.$tabs['data'], 0 );
//		$this->context->registerTab( 'transaction/'.$this->payload['corporationId'], '<i class="fa fa-fw fa-exchange"></i> '.$tabs['transactions'], 1 );
		$this->context->registerTab( 'reserve/'.$this->payload['corporationId'], '<i class="fa fa-fw fa-plus-square-o"></i> '.$tabs['reserves'], 1 );
		$this->context->registerTab( 'expense/'.$this->payload['corporationId'], '<i class="fa fa-fw fa-minus-square-o"></i> '.$tabs['expenses'], 2 );
		$this->context->registerTab( 'payin/'.$this->payload['corporationId'], '<i class="fa fa-fw fa-sign-out"></i> '.$tabs['payins'], 3 );
		$this->context->registerTab( 'payout/'.$this->payload['corporationId'], '<i class="fa fa-fw fa-sign-out"></i> '.$tabs['payouts'], 4 );
	}
}
