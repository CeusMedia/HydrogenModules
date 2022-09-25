<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class View_Helper_Panel{

	public function __construct( $client ){
		$this->client	= $client;
	}

	protected function formatMoney( $money ){
		$price		= number_format( $money->Amount / 100, 2, ',', '.' );
//		$pattern	= '{Amount}&nbsp;{Currency}';
//		return str_replace( array( '{Amount}', '{Currency}' ), array( $price, $money->Currency), $pattern );
		$pattern	= '%2$s&nbsp;%1$s';
		return sprintf( $pattern, $money->Currency, $price );
	}

	public function renderPanel( $header, $content, $footer = NULL, $type = NULL ){
		$contents	= array(
			HtmlTag::create( 'div', $header, array( 'class' => 'panel-head' ) ),
			HtmlTag::create( 'div', $content, array( 'class' => 'panel-body' ) )
		);
		if( $footer )
			$contents[]	= HtmlTag::create( 'div', $footer, array( 'class' => 'panel-foot' ) );
		$class		= 'panel'.( $type ? ' panel-'.$type : '' );
		return HtmlTag::create( 'div', $contents, array( 'class' => $class ) );
	}

	public function renderObject( $title, $data ){

	}

}
?>
