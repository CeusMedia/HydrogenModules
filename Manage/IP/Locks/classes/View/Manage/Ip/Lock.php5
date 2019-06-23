<?php
class View_Manage_IP_Lock extends CMF_Hydrogen_View{

	public function add(){}

	public function edit(){}

	public function index(){}

    public static function renderTabs( CMF_Hydrogen_Environment $env, $current = 0 ){
        $tabs   = new View_Helper_Navigation_Bootstrap_Tabs( $env );
        $tabs->setBasePath( './manage/ip/lock/' );
        $env->getModules()->callHook( "IpLock", "registerTabs", $tabs/*, $data*/ );						//  call tabs to be registered
        return $tabs->renderTabs( $current );
    }
}
