<?php
class View_Manage_My_User extends CMF_Hydrogen_View{

	protected static $tabs	= array();
	
	public function index(){}


	public static function ___onRegisterTab( $env, $context, $module, $data ){
		$words	= (object) $env->getLanguage()->getWords( 'manage/my/user' );						//  load words
		View_Manage_My_User::registerTab( '', $words->tabs['user'], 0 );								//  register main tab
/*		if( $env->getModules()->has( 'UI_Map' ) ){													//  map module is enabled
			$model		= new Model_Customer( $env );												//  get customer model
			$customer	= $model->get( $data['customerId'] );										//  get customer data
			$disabled	= !$customer || (bool) !$customer->latitude;								//  no customer or customer not geocoded
			$label		= $words->tabs['map'];														//  get tab label
			View_Manage_Customer::registerTab( 'map/'.$data['customerId'], $label, 2, $disabled );	//  register map tab
		}*/
	}

	public static function registerTab( $url, $label, $priority = 5, $disabled = NULL ){
		self::$tabs[]	= (object) array(
			'url'		=> $url,
			'label'		=> $label,
			'priority'	=> $priority,
			'disabled'	=> $disabled,
		);
	}

	public static function renderTabs( CMF_Hydrogen_Environment_Abstract $env, $current = 0 ){
		$view	= new View_Manage_My_User( $env );													//  prepare view
		$data	= array();																			//  prepare hook data
		$env->getModules()->callHook( "MyUser", "registerTabs", $view, $data );						//  call tabs to be registered
		$list	= array();																			//  prepare empty list
		foreach( self::$tabs as $nr => $tab ){														//  iterate registered tabs
			$attributes	= array( 'href'	=> './manage/my/user/'.$tab->url );							//  collect tab link attributes
			$link		= UI_HTML_Tag::create( 'a', $tab->label, $attributes );						//  render tab link
			$isActive	= $nr === $current || ( $tab->url === $current ) || !$nr && !$current;		//  is tab active ?
			$class		= $tab->disabled ? 'disabled' : ( $isActive ? 'active' : NULL );			//  get tab class
			if( $tab->disabled )																	//  if tab is disabled
				$link	= UI_HTML_Tag::create( 'a', $tab->label );									//  create blind link
			$key		= (float) $tab->priority.'.'.str_pad( $nr, 2, '0', STR_PAD_LEFT );			//  generate order key
			$list[$key]	= UI_HTML_Tag::create( 'li', $link, array( 'class' => $class ) );			//  enlist tab
		}
		if( count( $list ) > 1 )																	//  more than 1 tab
			return UI_HTML_Tag::create( 'ul', $list, array( 'class' => "nav nav-tabs" ) );			//  return rendered tab list
	}
}
?>