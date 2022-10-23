<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

class View_Manage_Customer extends View
{
	protected static $tabs	= [];

	public function add(){}
	public function edit(){}
	public function index(){}
	public function map(){}
	public function rate(){}

	public static function ___onRegisterTab( Environment $env, $context, $module, $data )
	{
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

	public static function registerTab( $url, $label, $priority = 5, $disabled = NULL )
	{
		self::$tabs[]	= (object) array(
			'url'		=> $url,
			'label'		=> $label,
			'priority'	=> $priority,
			'disabled'	=> $disabled,
		);
	}

	public static function renderTabs( Environment $env, $customerId, $current = 0 )
	{
		$view	= new View_Manage_Customer( $env );													//  prepare view
		$data	= ['customerId' => $customerId];												//  prepare hook data
		$env->getModules()->callHookWithPayload( "CustomerManager", "registerTabs", $view, $data );			//  call tabs to be registered
		$list	= [];																			//  prepare empty list
		foreach( self::$tabs as $nr => $tab ){														//  iterate registered tabs
			$attributes	= ['href'	=> './manage/customer/'.$tab->url];						//  collect tab link attributes
			$link		= HtmlTag::create( 'a', $tab->label, $attributes );						//  render tab link
			$isActive	= $nr === $current || ( $tab->url === $current ) || !$nr && !$current;		//  is tab active ?
			$class		= $tab->disabled ? 'disabled' : ( $isActive ? 'active' : NULL );			//  get tab class
			if( $tab->disabled )																	//  if tab is disabled
				$link	= HtmlTag::create( 'a', $tab->label );									//  create blind link
			$key		= (float) $tab->priority.'.'.str_pad( $nr, 2, '0', STR_PAD_LEFT );			//  generate order key
			$list[$key]	= HtmlTag::create( 'li', $link, ['class' => $class] );			//  enlist tab
		}
		if( count( $list ) > 1 )																	//  more than 1 tab
			return HtmlTag::create( 'ul', $list, ['class' => "nav nav-tabs"] );			//  return rendered tab list
	}
}
