<?php

use CeusMedia\HydrogenFramework\View;

class View_Manage_My_Mangopay extends View
{
	public static function formatMoney( $money, $separator = "&nbsp;", $accuracy = 2 )
	{
		$price		= number_format( $money->Amount / 100, $accuracy, ',', '.' );
//		$pattern	= '{Amount}&nbsp;{Currency}';
//		return str_replace( ['{Amount}', '{Currency}'], array( $price, $money->Currency), $pattern );
		$pattern	= '%2$s'.$separator.'%1$s';
		return sprintf( $pattern, $money->Currency, $price );
	}

	public function index()
	{
	}

	protected function __onInit(): void
	{
		$this->env->getPage()->addThemeStyle( 'module.manage.my.mangopay.css' );
	}
}
