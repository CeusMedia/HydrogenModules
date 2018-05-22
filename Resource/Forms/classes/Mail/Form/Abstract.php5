<?php
abstract class Mail_Form_Abstract extends Mail_Abstract{

	protected $app;
	protected $usePremailer;

	public function __construct( $app ){
		$this->app			= $app;
		$this->usePremailer	= $this->app->getConfig()->get( 'use.premailer' );
	}

	protected function applyFillData( $content, $fill ){
		$data	= json_decode( $fill->data, TRUE );
		while( preg_match( '/\[data_(\S+)\]/su', $content ) ){
			$identifier		= preg_replace( '/.*\[data_(\S+)\].*/su', "\\1", $content );
			$replace		= '';
			if( isset( $data[$identifier] ) ){
				$replace	= $data[$identifier]['value'];
				if( in_array( $data[$identifier]['type'], array( 'select' ) ) )
					$replace	= $data[$identifier]['valueLabel'];
			}
			$pattern		= '/'.preg_quote( '[data_'.$identifier.']' ).'/su';
			$content		= preg_replace( $pattern, $replace, $content, 1 );
		}
		return $content;
	}

	protected function applyHelpers( $content, $fill, $form ){
		while( preg_match( '/\[helper_(\S+)\]/su', $content ) ){
			$identifier		= preg_replace( '/.*\[helper_(\S+)\].*/su', "\\1", $content );
			$replace		= '';
			if( $identifier === "fill_person" ){
				$helperPerson	= new Helper_Fill_Person( $this->app );
				$helperPerson->setFill( $fill );
				$helperPerson->setForm( $form );
				$replace		= $helperPerson->render();
			}
			else if( $identifier === "fill_data" ){
				$helperData	= new Helper_Fill_Data( $this->app );
				$helperData->setFill( $fill );
				$helperData->setForm( $form );
				$replace		= $helperData->render();
			}
			$pattern		= '/'.preg_quote( '[helper_'.$identifier.']' ).'/su';
			$content		= preg_replace( $pattern, $replace, $content, 1 );
		}
		return $content;
	}

	public function renderPage( $content ){
		$html	= file_get_contents( 'inc/mail.html' );
		$html	= str_replace( '[title]', 'DtHPS Formular Mail', $html );
		$html	= str_replace( '[style]', file_get_contents( 'inc/mail.css' ), $html );
		$html	= str_replace( '[content]', $content, $html );
		return $html;

/*		$body	= UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'div', array(
					UI_HTML_Tag::create( 'div', array(
						UI_HTML_Tag::create( 'img', NULL, array( 'src' => 'CID:image1' ) )
					), array( 'style' => 'float: left; width: 15%;') ),
					UI_HTML_Tag::create( 'div', array(
						UI_HTML_Tag::create( 'br' ),
						UI_HTML_Tag::create( 'h1', 'Deutsche Heilpraktikerschule' ),
					), array( 'style' => 'float: left; width: 70%; margin-left: 5%;') ),
					UI_HTML_Tag::create( 'div', '', array( 'style' => 'clear: left' ) ),
				), array( 'id' => 'layout-mail-header' ) ),
				UI_HTML_Tag::create( 'div', array(
					$content,
				), array( 'id' => 'layout-mail-content' ) ),
				UI_HTML_Tag::create( 'div', array(
				), array( 'id' => 'layout-mail-footer' ) ),
			), array( 'id' => 'layout-mail' ) ),
		), array( 'id' => 'mail' ) );

		$page	= new UI_HTML_PageFrame();
		if( $this->usePremailer ){
			$page->addStylesheet( $this->app->getConfig()->get( 'app.url' ).'inc/bootstrap.email.min.css' );
			$page->addStylesheet( $this->app->getConfig()->get( 'app.url' ).'inc/mail.css' );
			$page->addBody( $body );
			$html	= $page->build( array( 'class' => 'mail' ) );
			$premailer	= new Net_API_Premailer();
			$premailer->convertFromHtml( $html, array( 'preserve_styles' => FALSE ) );
			$html	= $premailer->getHtml();
		}
		else{
			$page->addHead( UI_HTML_Tag::create( 'style', file_get_contents( 'inc/bootstrap.email.min.css' ), array( 'rel' => 'stylesheet' ) ) );
			$page->addHead( UI_HTML_Tag::create( 'style', file_get_contents( 'inc/style.css' ), array( 'rel' => 'stylesheet' ) ) );
			$page->addBody( $body );
			$html	= $page->build( array( 'class' => 'mail' ) );
		}
		return $html;*/
	}
}

