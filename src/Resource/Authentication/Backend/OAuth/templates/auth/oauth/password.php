<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w		= (object) $words['password'];

$iconSend	= HTML::Icon( 'envelope', TRUE );

extract( $view->populateTexts( ['top', 'info', 'bottom'], 'html/auth/password/' ) );

$panelPassword	= HTML::DivClass( "content-panel content-panel-form", [
	HTML::H3( $w->heading ),
	HTML::DivClass( "content-panel-inner", [
		HtmlTag::create( 'form', [
			HTML::DivClass( "row-fluid", [
				HTML::DivClass( "span12", [
					HtmlTag::create( 'label', $w->labelEmail, [
						'for'	=> "input_password_email",
						'class'	=> "mandatory"
					] ),
					HtmlTag::create( 'input', NULL, [
						'type'	=> "text",
						'name'	=> "password_email",
						'id'	=> "input_password_email",
						'class'	=> "span12 mandatory",
						'value'	=> htmlentities( $password_email, ENT_QUOTES, 'UTF-8' )
					] )
				] )
			] ),
			HTML::DivClass( "buttonbar", [
				HtmlTag::create( 'button', $iconSend.'&nbsp;'.$w->buttonSend, [
					'type'	=> "submit",
					'class'	=> "btn btn-primary",
					'name'	=> "sendPassword"
				] )
			] )
		], [
			'action'	=> "./auth/password",
			'method'	=> "post",
		] )
	] ),
] );

return HTML::DivClass( "auth-password-text-top", $textTop ).
HTML::DivClass( "row-fluid", [
	HTML::DivClass( "span4 offset1", [
		$panelPassword
	] ),
	HTML::DivClass( "span6", [
		$textInfo
	] ),
] ).HTML::DivClass( "auth-password-text-bottom", $textBottom );
