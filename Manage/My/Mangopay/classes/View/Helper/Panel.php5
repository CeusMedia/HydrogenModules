<?php
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
			UI_HTML_Tag::create( 'div', $header, array( 'class' => 'panel-head' ) ),
			UI_HTML_Tag::create( 'div', $content, array( 'class' => 'panel-body' ) )
		);
		if( $footer )
			$contents[]	= UI_HTML_Tag::create( 'div', $footer, array( 'class' => 'panel-foot' ) );
		$class		= 'panel'.( $type ? ' panel-'.$type : '' );
		return UI_HTML_Tag::create( 'div', $contents, array( 'class' => $class ) );
	}

	public function renderObject( $title, $data ){

	}

}
?>
