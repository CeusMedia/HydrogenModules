<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Shop
{
	protected Environment $env;
	protected Dictionary $config;
	protected array $words;
	protected string $language;

	public function __construct( Environment $env )
	{
		$this->env		= $env;
		$this->config	= $env->getConfig()->getAll( 'module.shop.', TRUE );
		$this->words	= $env->getLanguage()->getWords( 'shop' );
		$this->language	= $env->getLanguage()->getLanguage();
	}

	public function formatPrice( $price, bool $spaceBeforeCurrency = TRUE, bool $asHtml = TRUE ): string
	{
		$decimals	= (int) $this->config->get( 'price.accuracy' );
		$currency	= (string) $this->config->get( 'price.currency' );
		$decPoint	= $this->language === 'de' ? ',' : '.';
		$currency	= $asHtml ? htmlentities( $currency, ENT_QUOTES, 'utf-8' ) : $currency;
		$space		= $spaceBeforeCurrency ? ( $asHtml ? '&nbsp;' : ' ' ) : '';
		return number_format( $price, $decimals, $decPoint, NULL ).$space.$currency;
	}

	public function renderCartPanelAsText( array $positions ): string
	{
		$helper	= new View_Helper_Shop_CartPositions( $this->env );
		$helper->setPositions( $positions );
		$helper->setOutput( View_Helper_Shop_CartPositions::OUTPUT_TEXT );
//		$helper->setPaymentBackend(...);
		return $helper->render();
	}

	public function renderCartPanel( array $positions ): string
	{
		$helper	= new View_Helper_Shop_CartPositions( $this->env );
		$helper->setPositions( $positions );
//		$helper->setPaymentBackend(...);
		$helper->setOutput( View_Helper_Shop_CartPositions::OUTPUT_HTML );
		return '<h4>Warenkorb</h4>'.$helper->render();
	}

	public function renderCustomerPanel( $data ): string
	{
		$words	= (object) $this->words['panel-customer'];
		$helper	= new View_Helper_Shop_AddressView( $this->env );
		$helper->setAddress( $data );
		return '
			<h4>'.$words->heading.'</h4>
			'.$helper->render().'
			<br/>';
	}

	public function renderBillingPanel( $data ): string
	{
		if( !$data )
			return '';
		$words	= (object) $this->words['panel-billing'];
		$helper	= new View_Helper_Shop_AddressView( $this->env );
		$helper->setAddress( $data );
		return '
			<h4>'.$words->heading.'</h4>
			'.$helper->render().'
			<br/>';
	}
}
