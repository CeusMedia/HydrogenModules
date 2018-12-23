<?php
class View_Manage_Shop_Shipping extends CMF_Hydrogen_View{

	public static function ___onRegisterTab( CMF_Hydrogen_Environment $env, $context, $module, $data ){
		$words	= (object) $env->getLanguage()->getWords( 'manage/shop' );						//  load words
		$context->registerTab( 'shipping', $words->tabs['shipping'], 6 );									//  register report tab
	}

	public function __onInit(){}

	public function index(){}
}
