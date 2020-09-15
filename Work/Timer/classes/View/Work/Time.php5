<?php
class View_Work_Time extends CMF_Hydrogen_View {

/*	protected function __onInit(){
		$this->modelTimer		= new Model_Work_Timer( $this->env );
		$this->modelProject		= new Model_Project( $this->env );
	}
*/
	public static function ___onRegisterTab( CMF_Hydrogen_Environment $env, $context, $module, $data ){
		$words	= (object) $env->getLanguage()->getWords( 'work/time' );						//  load words
		$context->registerTab( '', $words->tabs['dashboard'], 0 );								//  register main tab
//		$context->registerTab( 'archive', $words->tabs['archive'], 1 );								//  register main tab
//		$context->registerTab( 'report', $words->tabs['report'], 2 );								//  register main tab
	}

/*	static public function ___onRenderDashboardPanels( CMF_Hydrogen_Environment $env, $context, $module, $data ){
		$helper		= new View_Helper_Work_Time_Dashboard_My( $env );
		$context->registerPanel( 'work-timer-my', 'Letzte Aktivität', $helper->render(), '1col-fixed', 10 );

		$helper		= new View_Helper_Work_Time_Dashboard_Others( $env );
		$context->registerPanel( 'work-timer-others', 'Aktivitäten Anderer', $helper->render(), '3col-flex', 10 );
	}*/

	public function add(){}

	public function ajaxRenderDashboardPanel(){
	}

	public function edit(){}

	public function index(){}

	public static function renderTabs( CMF_Hydrogen_Environment $env, $current = 0 ){
		$tabs	= new View_Helper_Navigation_Bootstrap_Tabs( $env );
		$tabs->setBasePath( './work/time/' );
		$env->getModules()->callHook( "WorkTime", "registerTabs", $tabs/*, $data*/ );						//  call tabs to be registered
		return $tabs->renderTabs( $current );
	}
}
?>
