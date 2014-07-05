<?php
class View_Work_Bill extends CMF_Hydrogen_View{

	public function __onInit(){
		parent::__onInit();
		$page			= $this->env->getPage();
		$session		= $this->env->getSession();
		$monthsLong		= array_values( (array) $this->getWords( 'months' ) );
		$monthsShort	= array_values( (array) $this->getWords( 'months-short' ) );
		$page->js->addScript( 'var monthNames = '.json_encode( $monthsLong).';' );
		$page->js->addScript( 'var monthNamesShort = '.json_encode( $monthsShort).';' );
	}

	public function add(){}
	public function edit(){}
	public function index(){}
	public function remove(){}
	public function graph(){}

	public static function ___onRegisterTab( $env, $context, $module, $data ){
		$words	= (object) $env->getLanguage()->getWords( 'work/bill' );							//  load words
		$context->registerTab( '', $words->tabs['list'], 0 );										//  register main tab
		$context->registerTab( '', $words->tabs['graph'], 5 );										//  register graph tab
	}

	public static function renderTabs( CMF_Hydrogen_Environment_Abstract $env, $current = 0 ){
		$tabs	= new View_Helper_Navigation_Bootstrap_Tabs( $env );
		$tabs->setBasePath( './work/bill/' );
		$env->getModules()->callHook( "Work:Bills", "registerTabs", $tabs/*, $data*/ );						//  call tabs to be registered
		return $tabs->renderTabs( $current );
	}

}
?>
