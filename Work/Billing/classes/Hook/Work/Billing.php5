<?php

use CeusMedia\HydrogenFramework\Environment;

class Hook_Work_Billing extends CMF_Hydrogen_Hook
{
	public static function onPageApplyModules( Environment $env, $context, $module, $payload )
	{
		$context->js->addScriptOnReady( 'WorkBilling.init()' );
	}

	public static function onBillingBillRegisterTab( Environment $env, $context, $module, $data )
	{
//		$words	= (object) $env->getLanguage()->getWords( 'manage/my/user' );						//  load words
//		$context->registerTab( '', $words->tabs['user'], 0 );								//  register main tab
		$modelBill	= new Model_Billing_Bill( $env );
		$bill		= $modelBill->get( $data['billId'] );
		$context->registerTab( 'edit/'.$data['billId'], '<i class="fa fa-fw fa-edit"></i> Daten', 0 );
		$context->registerTab( 'breakdown/'.$data['billId'], '<i class="fa fa-fw fa-pie-chart"></i> Aufteilung', 1 );
		$context->registerTab( 'transaction/'.$data['billId'], '<i class="fa fa-fw fa-exchange"></i> Transaktionen', 2, $bill->status == 0 );
	}

	public static function onBillingPersonRegisterTab( Environment $env, $context, $module, $data )
	{
//		$words	= (object) $env->getLanguage()->getWords( 'manage/my/user' );						//  load words
//		$context->registerTab( '', $words->tabs['user'], 0 );								//  register main tab
		$context->registerTab( 'edit/'.$data['personId'], '<i class="fa fa-fw fa-edit"></i> Daten', 0 );
//		$context->registerTab( 'transaction/'.$data['personId'], '<i class="fa fa-fw fa-exchange"></i> Transaktionen', 1 );
		$context->registerTab( 'reserve/'.$data['personId'], '<i class="fa fa-fw fa-plus-square-o"></i> Einnahmen / Rücklagen', 1 );
		$context->registerTab( 'expense/'.$data['personId'], '<i class="fa fa-fw fa-minus-square-o"></i> Ausgaben', 2 );
		$context->registerTab( 'payin/'.$data['personId'], '<i class="fa fa-fw fa-sign-in"></i> Einzahlungen', 3 );
		$context->registerTab( 'payout/'.$data['personId'], '<i class="fa fa-fw fa-sign-out"></i> Auszahlungen', 4 );
		$context->registerTab( 'unbooked/'.$data['personId'], '<i class="fa fa-fw fa-question-circle-o"></i> Ausstehend', 5 );
	}

	public static function onBillingCorporationRegisterTab( Environment $env, $context, $module, $data )
	{
//		$words	= (object) $env->getLanguage()->getWords( 'manage/my/user' );						//  load words
//		$context->registerTab( '', $words->tabs['user'], 0 );								//  register main tab
		$context->registerTab( 'edit/'.$data['corporationId'], '<i class="fa fa-fw fa-edit"></i> Daten', 0 );
//		$context->registerTab( 'transaction/'.$data['corporationId'], '<i class="fa fa-fw fa-exchange"></i> Transaktionen', 1 );
		$context->registerTab( 'reserve/'.$data['corporationId'], '<i class="fa fa-fw fa-plus-square-o"></i> Einnahmen / Rücklagen', 1 );
		$context->registerTab( 'expense/'.$data['corporationId'], '<i class="fa fa-fw fa-minus-square-o"></i> Ausgaben', 2 );
		$context->registerTab( 'payin/'.$data['corporationId'], '<i class="fa fa-fw fa-sign-out"></i> Einzahlungen', 3 );
		$context->registerTab( 'payout/'.$data['corporationId'], '<i class="fa fa-fw fa-sign-out"></i> Auszahlungen', 4 );
	}
}
