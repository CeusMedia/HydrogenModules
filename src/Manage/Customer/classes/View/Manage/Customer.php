<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

class View_Manage_Customer extends View
{
	protected static array $tabs	= [];

	public function add(): void
	{
	}

	public function edit(): void
	{
	}

	public function index(): void
	{
	}

	public function map(): void
	{
	}

	public function rate(): void
	{
	}

	public static function registerTab( $url, $label, $priority = 5, $disabled = NULL ): void
	{
		self::$tabs[]	= (object) [
			'url'		=> $url,
			'label'		=> $label,
			'priority'	=> $priority,
			'disabled'	=> $disabled,
		];
	}

	/**
	 *	@param		Environment		$env
	 *	@param		int|string		$customerId
	 *	@param		$current
	 *	@return		string
	 *	@throws		ReflectionException
	 */
	public static function renderTabs( Environment $env, int|string $customerId, $current = 0 ): string
	{
		$view	= new View_Manage_Customer( $env );													//  prepare view
		$data	= ['customerId' => $customerId];													//  prepare hook data
		$env->getModules()->callHookWithPayload( "CustomerManager", "registerTabs", $view, $data );			//  call tabs to be registered
		$list	= [];																				//  prepare empty list
		foreach( self::$tabs as $nr => $tab ){														//  iterate registered tabs
			$attributes	= ['href'	=> './manage/customer/'.$tab->url];								//  collect tab link attributes
			$link		= HtmlTag::create( 'a', $tab->label, $attributes );					//  render tab link
			$isActive	= $nr === $current || ( $tab->url === $current ) || !$nr && !$current;		//  is tab active ?
			$class		= $tab->disabled ? 'disabled' : ( $isActive ? 'active' : NULL );			//  get tab class
			if( $tab->disabled )																	//  if tab is disabled
				$link	= HtmlTag::create( 'a', $tab->label );									//  create blind link
			$key		= (float) $tab->priority.'.'.str_pad( $nr, 2, '0', STR_PAD_LEFT );			//  generate order key
			$list[$key]	= HtmlTag::create( 'li', $link, ['class' => $class] );				//  enlist tab
		}
		if( count( $list ) > 1 )																	//  more than 1 tab
			return HtmlTag::create( 'ul', $list, ['class' => "nav nav-tabs"] );				//  return rendered tab list
		return '';
	}
}
