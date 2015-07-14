<?php
class View_Manage_IP_Lock extends CMF_Hydrogen_View{

	public function add(){}

	public function edit(){}

	public function index(){}

	public static function ___onRegisterTab( $env, $context, $module, $data ){
		$words  = (object) $env->getLanguage()->getWords( 'manage/ip/lock' );							//  load words
		$context->registerTab( '', $words->tabs['index'], 0 );											//  register main tab
		$context->registerTab( 'reason', $words->tabs['reason'], 4 );									//  register main tab
		$context->registerTab( 'filter', $words->tabs['filter'], 3 );									//  register main tab
    }

    public static function renderTabs( CMF_Hydrogen_Environment_Abstract $env, $current = 0 ){
        $tabs   = new View_Helper_Navigation_Bootstrap_Tabs( $env );
        $tabs->setBasePath( './manage/ip/lock/' );
        $env->getModules()->callHook( "IpLock", "registerTabs", $tabs/*, $data*/ );						//  call tabs to be registered
        return $tabs->renderTabs( $current );
    }

}
