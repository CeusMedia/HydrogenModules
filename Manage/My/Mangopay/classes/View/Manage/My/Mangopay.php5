<?php
class View_Manage_My_Mangopay extends CMF_Hydrogen_View{

	public function __onInit(){
		$this->env->getPage()->addThemeStyle( 'module.manage.my.mangopay.css' );
	}

	static public function formatMoney( $money, $separator = "&nbsp;", $accuracy = 2 ){
		$price		= number_format( $money->Amount / 100, $accuracy, ',', '.' );
//		$pattern	= '{Amount}&nbsp;{Currency}';
//		return str_replace( array( '{Amount}', '{Currency}' ), array( $price, $money->Currency), $pattern );
		$pattern	= '%2$s'.$separator.'%1$s';
		return sprintf( $pattern, $money->Currency, $price );
	}
}
