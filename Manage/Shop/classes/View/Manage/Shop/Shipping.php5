<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

class View_Manage_Shop_Shipping extends View
{
	public static function ___onRegisterTab( Environment $env, $context, $module, $data )
	{
		$words	= (object) $env->getLanguage()->getWords( 'manage/shop' );						//  load words
		$context->registerTab( 'shipping', $words->tabs['shipping'], 6 );						//  register report tab
	}

	public function index()
	{
		$this->env->getPage()->addCommonStyle( 'module.manage.shop.css' );
		$this->env->getPage()->js->addModuleFile( 'module.manage.shop.js' );
	}

	protected function __onInit()
	{
	}
}
