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
//		$words	= (object) $this->env->getLanguage()->getWords( 'manage/my/user' );				//  load words
//		$this->context->registerTab( '', $words->tabs['user'], 0 );								//  register main tab
		$modelBill	= new Model_Billing_Bill( $this->env );
		$bill		= $modelBill->get( $this->payload['billId'] );
		$this->context->registerTab( 'edit/'.$this->payload['billId'], '<i class="fa fa-fw fa-edit"></i> Daten', 0 );
		$this->context->registerTab( 'breakdown/'.$this->payload['billId'], '<i class="fa fa-fw fa-pie-chart"></i> Aufteilung', 1 );
		$this->context->registerTab( 'transaction/'.$this->payload['billId'], '<i class="fa fa-fw fa-exchange"></i> Transaktionen', 2, $bill->status == 0 );
	}

	public function onBillingPersonRegisterTab(): void
	{
//		$words	= (object) $this->env->getLanguage()->getWords( 'manage/my/user' );				//  load words
//		$this->context->registerTab( '', $words->tabs['user'], 0 );								//  register main tab
		$this->context->registerTab( 'edit/'.$this->payload['personId'], '<i class="fa fa-fw fa-edit"></i> Daten', 0 );
//		$this->context->registerTab( 'transaction/'.$this->payload['personId'], '<i class="fa fa-fw fa-exchange"></i> Transaktionen', 1 );
		$this->context->registerTab( 'reserve/'.$this->payload['personId'], '<i class="fa fa-fw fa-plus-square-o"></i> Einnahmen / Rücklagen', 1 );
		$this->context->registerTab( 'expense/'.$this->payload['personId'], '<i class="fa fa-fw fa-minus-square-o"></i> Ausgaben', 2 );
		$this->context->registerTab( 'payin/'.$this->payload['personId'], '<i class="fa fa-fw fa-sign-in"></i> Einzahlungen', 3 );
		$this->context->registerTab( 'payout/'.$this->payload['personId'], '<i class="fa fa-fw fa-sign-out"></i> Auszahlungen', 4 );
		$this->context->registerTab( 'unbooked/'.$this->payload['personId'], '<i class="fa fa-fw fa-question-circle-o"></i> Ausstehend', 5 );
	}

	public function onBillingCorporationRegisterTab(): void
	{
//		$words	= (object) $this->env->getLanguage()->getWords( 'manage/my/user' );				//  load words
//		$this->context->registerTab( '', $words->tabs['user'], 0 );								//  register main tab
		$this->context->registerTab( 'edit/'.$this->payload['corporationId'], '<i class="fa fa-fw fa-edit"></i> Daten', 0 );
//		$this->context->registerTab( 'transaction/'.$this->payload['corporationId'], '<i class="fa fa-fw fa-exchange"></i> Transaktionen', 1 );
		$this->context->registerTab( 'reserve/'.$this->payload['corporationId'], '<i class="fa fa-fw fa-plus-square-o"></i> Einnahmen / Rücklagen', 1 );
		$this->context->registerTab( 'expense/'.$this->payload['corporationId'], '<i class="fa fa-fw fa-minus-square-o"></i> Ausgaben', 2 );
		$this->context->registerTab( 'payin/'.$this->payload['corporationId'], '<i class="fa fa-fw fa-sign-out"></i> Einzahlungen', 3 );
		$this->context->registerTab( 'payout/'.$this->payload['corporationId'], '<i class="fa fa-fw fa-sign-out"></i> Auszahlungen', 4 );
	}
}
