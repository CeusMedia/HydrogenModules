<?php
class View_Manage_IP_Lock extends CMF_Hydrogen_View{

	public function add(){}

	public function edit(){}

	public function index(){}

	public static function ___onRegisterTab( CMF_Hydrogen_Environment $env, $context, $module, $data ){
		$words  = (object) $env->getLanguage()->getWords( 'manage/ip/lock' );							//  load words
		$context->registerTab( '', $words->tabs['index'], 0 );											//  register main tab
		$context->registerTab( 'filter', $words->tabs['filter'], 3 );									//  register filter tab
		$context->registerTab( 'reason', $words->tabs['reason'], 4 );									//  register reason tab
		$context->registerTab( 'transport', $words->tabs['transport'], 8 );								//  register transport tab
    }

    public static function renderTabs( CMF_Hydrogen_Environment $env, $current = 0 ){
        $tabs   = new View_Helper_Navigation_Bootstrap_Tabs( $env );
        $tabs->setBasePath( './manage/ip/lock/' );
        $env->getModules()->callHook( "IpLock", "registerTabs", $tabs/*, $data*/ );						//  call tabs to be registered
        return $tabs->renderTabs( $current );
    }

}
