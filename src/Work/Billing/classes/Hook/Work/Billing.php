<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Work_Billing extends Hook
{
	public static function onPageApplyModules( Environment $env, object $context, object $module, array & $payload )
	{
		$context->js->addScriptOnReady( 'WorkBilling.init()' );
	}

	public static function onBillingBillRegisterTab( Environment $env, object $context, object $module, array & $payload )
	{
//		$words	= (object) $env->getLanguage()->getWords( 'manage/my/user' );						//  load words
//		$context->registerTab( '', $words->tabs['user'], 0 );								//  register main tab
		$modelBill	= new Model_Billing_Bill( $env );
		$bill		= $modelBill->get( $payload['billId'] );
		$context->registerTab( 'edit/'.$payload['billId'], '<i class="fa fa-fw fa-edit"></i> Daten', 0 );
		$context->registerTab( 'breakdown/'.$payload['billId'], '<i class="fa fa-fw fa-pie-chart"></i> Aufteilung', 1 );
		$context->registerTab( 'transaction/'.$payload['billId'], '<i class="fa fa-fw fa-exchange"></i> Transaktionen', 2, $bill->status == 0 );
	}

	public static function onBillingPersonRegisterTab( Environment $env, object $context, object $module, array & $payload )
	{
//		$words	= (object) $env->getLanguage()->getWords( 'manage/my/user' );						//  load words
//		$context->registerTab( '', $words->tabs['user'], 0 );								//  register main tab
		$context->registerTab( 'edit/'.$payload['personId'], '<i class="fa fa-fw fa-edit"></i> Daten', 0 );
//		$context->registerTab( 'transaction/'.$payload['personId'], '<i class="fa fa-fw fa-exchange"></i> Transaktionen', 1 );
		$context->registerTab( 'reserve/'.$payload['personId'], '<i class="fa fa-fw fa-plus-square-o"></i> Einnahmen / Rücklagen', 1 );
		$context->registerTab( 'expense/'.$payload['personId'], '<i class="fa fa-fw fa-minus-square-o"></i> Ausgaben', 2 );
		$context->registerTab( 'payin/'.$payload['personId'], '<i class="fa fa-fw fa-sign-in"></i> Einzahlungen', 3 );
		$context->registerTab( 'payout/'.$payload['personId'], '<i class="fa fa-fw fa-sign-out"></i> Auszahlungen', 4 );
		$context->registerTab( 'unbooked/'.$payload['personId'], '<i class="fa fa-fw fa-question-circle-o"></i> Ausstehend', 5 );
	}

	public static function onBillingCorporationRegisterTab( Environment $env, object $context, object $module, array & $payload )
	{
//		$words	= (object) $env->getLanguage()->getWords( 'manage/my/user' );						//  load words
//		$context->registerTab( '', $words->tabs['user'], 0 );								//  register main tab
		$context->registerTab( 'edit/'.$payload['corporationId'], '<i class="fa fa-fw fa-edit"></i> Daten', 0 );
//		$context->registerTab( 'transaction/'.$payload['corporationId'], '<i class="fa fa-fw fa-exchange"></i> Transaktionen', 1 );
		$context->registerTab( 'reserve/'.$payload['corporationId'], '<i class="fa fa-fw fa-plus-square-o"></i> Einnahmen / Rücklagen', 1 );
		$context->registerTab( 'expense/'.$payload['corporationId'], '<i class="fa fa-fw fa-minus-square-o"></i> Ausgaben', 2 );
		$context->registerTab( 'payin/'.$payload['corporationId'], '<i class="fa fa-fw fa-sign-out"></i> Einzahlungen', 3 );
		$context->registerTab( 'payout/'.$payload['corporationId'], '<i class="fa fa-fw fa-sign-out"></i> Auszahlungen', 4 );
	}
}
