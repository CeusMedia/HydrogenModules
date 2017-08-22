<?php
class View_Work_Billing_Person extends CMF_Hydrogen_View{
	public function add(){}
	public function edit(){}
	public function index(){}

	public static function ___onRegisterTab( $env, $context, $module, $data ){
//		$words	= (object) $env->getLanguage()->getWords( 'manage/my/user' );						//  load words
//		$context->registerTab( '', $words->tabs['user'], 0 );								//  register main tab
		$context->registerTab( 'edit/'.$data['personId'], 'Daten', 0 );
		$context->registerTab( 'transaction/'.$data['personId'], 'Transaktionen', 1 );
		$context->registerTab( 'expense/'.$data['personId'], 'Ausgaben', 2 );
		$context->registerTab( 'payin/'.$data['personId'], 'Einzahlungen', 3 );
		$context->registerTab( 'payout/'.$data['personId'], 'Auszahlungen', 4 );
		$context->registerTab( 'unbooked/'.$data['personId'], 'Ausstehend', 5 );
	}

	public static function renderTabs( CMF_Hydrogen_Environment_Abstract $env, $personId, $current = 0 ){
		$tabs	= new View_Helper_Navigation_Bootstrap_Tabs( $env );
		$tabs->setBasePath( './work/billing/person/' );
		$data	= array( 'personId' => $personId );
		$env->getModules()->callHook( "WorkBilling/Person", "registerTabs", $tabs, $data );
		return $tabs->renderTabs( $current );
	}
}
?>
