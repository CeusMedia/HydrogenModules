<?php
class View_Work_Billing_Corporation extends CMF_Hydrogen_View{
	public function add(){}
	public function edit(){}
	public function index(){}

	public static function ___onRegisterTab( $env, $context, $module, $data ){
//		$words	= (object) $env->getLanguage()->getWords( 'manage/my/user' );						//  load words
//		$context->registerTab( '', $words->tabs['user'], 0 );								//  register main tab
		$context->registerTab( 'edit/'.$data['corporationId'], 'Daten', 0 );
		$context->registerTab( 'transaction/'.$data['corporationId'], 'Transaktionen', 1 );
		$context->registerTab( 'reserve/'.$data['corporationId'], 'Rücklagen', 2 );
		$context->registerTab( 'payout/'.$data['corporationId'], 'Auszahlungen', 3 );
	}

	public static function renderTabs( CMF_Hydrogen_Environment_Abstract $env, $corporationId, $current = 0 ){
		$tabs	= new View_Helper_Navigation_Bootstrap_Tabs( $env );
		$tabs->setBasePath( './work/billing/corporation/' );
		$data	= array( 'corporationId' => $corporationId );
		$env->getModules()->callHook( "WorkBilling/Corporation", "registerTabs", $tabs, $data );
		return $tabs->renderTabs( $current );
	}
}
?>
