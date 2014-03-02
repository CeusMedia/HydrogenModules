<?php
class View_Manage_Customer extends CMF_Hydrogen_View{
	
	protected static $tabs	= array();

	public function add(){}
	public function edit(){}
	public function index(){}
	public function map(){}
	public function rate(){}

	public static function ___onRegisterTab( $env, $context, $module, $data ){
		$words	= (object) $env->getLanguage()->getWords( 'manage/customer' );						//  load words
		View_Manage_Customer::registerTab( 'edit/'.$data['customerId'], $words->tabs['edit'], 0 );	//  register main tab
		if( $env->getModules()->has( 'UI_Map' ) ){													//  map module is enabled
			$model		= new Model_Customer( $env );												//  get customer model
			$customer	= $model->get( $data['customerId'] );										//  get customer data
			$disabled	= !$customer || (bool) !$customer->latitude;								//  no customer or customer not geocoded
			$label		= $words->tabs['map'];														//  get tab label
			View_Manage_Customer::registerTab( 'map/'.$data['customerId'], $label, 2, $disabled );	//  register map tab
		}
	}

	public static function registerTab( $url, $label, $priority = 5, $disabled = NULL ){
		self::$tabs[]	= (object) array(
			'url'		=> $url,
			'label'		=> $label,
			'priority'	=> $priority,
			'disabled'	=> $disabled,
		);
	}

	public static function renderTabs( CMF_Hydrogen_Environment_Abstract $env, $customerId, $current = 0 ){
		$view	= new View_Manage_Customer( $env );													//  prepare view
		$data	= array( 'customerId' => $customerId );												//  prepare hook data
		$env->getModules()->callHook( "CustomerManager", "registerTabs", $view, $data );			//  call tabs to be registered
		$list	= array();																			//  prepare empty list
		foreach( self::$tabs as $nr => $tab ){														//  iterate registered tabs
			$attributes	= array( 'href'	=> './manage/customer/'.$tab->url );						//  collect tab link attributes
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
