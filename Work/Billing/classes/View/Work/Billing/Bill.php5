<?php
class View_Work_Billing_Bill extends CMF_Hydrogen_View{

	public function __onInit(){}
	public function add(){}
	public function edit(){}
	public function index(){}

	public static function ___onRegisterTab( $env, $context, $module, $data ){
//		$words	= (object) $env->getLanguage()->getWords( 'manage/my/user' );						//  load words
//		$context->registerTab( '', $words->tabs['user'], 0 );								//  register main tab
		$modelBill	= new Model_Billing_Bill( $env );
		$bill		= $modelBill->get( $data['billId'] );
		$context->registerTab( 'edit/'.$data['billId'], '<i class="fa fa-fw fa-edit"></i> Daten', 0 );
		$context->registerTab( 'breakdown/'.$data['billId'], '<i class="fa fa-fw fa-sitemap"></i> Aufteilung', 1 );
		$context->registerTab( 'transaction/'.$data['billId'], '<i class="fa fa-fw fa-exchange"></i> Transaktionen', 2, $bill->status == 0 );
	}

	public static function renderTabs( CMF_Hydrogen_Environment_Abstract $env, $billId, $current = 0 ){
		$tabs	= new View_Helper_Navigation_Bootstrap_Tabs( $env );
		$tabs->setBasePath( './work/billing/bill/' );
		$data	= array( 'billId' => $billId );
		$env->getModules()->callHook( "WorkBilling/Bill", "registerTabs", $tabs, $data );
		return $tabs->renderTabs( $current );
	}
}
?>
