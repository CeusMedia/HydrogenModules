<?php
class View_Manage_Shop_Report extends View_Manage_Shop{

	public static function ___onRegisterTab( $env, $context, $module, $data ){
		$words	= (object) $env->getLanguage()->getWords( 'manage/shop' );						//  load words
		$context->registerTab( 'report', $words->tabs['reports'], 8 );									//  register report tab
	}

	public function index(){
		$uriGoogleAPI	= "https://www.google.com/jsapi";
		$this->env->getPage()->addJavaScript( $uriGoogleAPI );
	}

}
?>
