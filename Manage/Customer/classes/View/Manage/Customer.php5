<?php
class View_Manage_Customer extends CMF_Hydrogen_View{
	
	protected static $tabs	= array();

	public function add(){}
	public function edit(){}
	public function index(){}
	public function map(){}
	public function rate(){}

	public static function ___onRegisterTab( $env, $context, $context, $data ){
		$words	= $env->getLanguage()->getWords( 'manage/customer' );
		$words	= (object) $words['tabs'];
		View_Manage_Customer::registerTab( 'edit/'.$data['customerId'], $words->edit, 0 );
		if( $env->getModules()->has( 'UI_Map' ) ){
			$model		= new Model_Customer( $env );
			$customer	= $model->get( $data['customerId'] );
			$disabled	= $customer && (bool) !$customer->latitude;
			View_Manage_Customer::registerTab( 'map/'.$data['customerId'], $words->map, 1, $disabled );
		}
	}

	public static function registerTab( $url, $label, $priority = 5, $disabled = NULL ){
		self::$tabs[]	= (object) array(
			'url'		=> $url,
			'priority'	=> $priority,
			'label'		=> $label,
			'disabled'	=> $disabled,
		);
	}

	public static function renderTabs( CMF_Hydrogen_Environment_Abstract $env, $customerId, $current = 0 ){
		$view	= new View_Manage_Customer( $env );
		$env->getModules()->callHook( "CustomerManager", "registerTabs", $view, array( 'customerId' => $customerId ) );

		$list	= array();
		if( count( self::$tabs ) < 2 )
			return '';
		$list	= array();
		foreach( self::$tabs as $nr => $tab ){
			$url		= sprintf( $tab->url, $customerId );
			$attributes	= array( 'href'	=> './manage/customer/'.$url );
			$link		= UI_HTML_Tag::create( 'a', $tab->label, $attributes );
			$isActive	= $nr === $current || ( $url === $current ) || !$nr && !$current; 
			$attributes	= array( 'class' => $isActive ? 'active' : NULL );
			if( $tab->disabled ){
				$link	= UI_HTML_Tag::create( 'a', $tab->label );
				$attributes['class']	.= ' disabled';
			}
			$key	= (float) $tab->priority.'.'.time();
			$list[$key]	= UI_HTML_Tag::create( 'li', $link, $attributes );
		}
		ksort( $list );
		return UI_HTML_Tag::create( 'ul', $list, array( 'class' => "nav nav-tabs" ) );
	}
}
?>
