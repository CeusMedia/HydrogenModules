<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;
use CeusMedia\HydrogenFramework\View;

/** @var Web $env */
/** @var View $view */
/** @var array<array<string,string>> $words */

$w			= (object) $words['password'];

$iconCancel	= HTML::Icon( 'arrow-left' );
$iconSend	= HTML::Icon( 'envelope', TRUE );
if( $env->getModules()->has( 'UI_Font_FontAwesome' ) ){
	$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-left'] );
	$iconSend		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );
}

$buttonCancel		= HtmlTag::create( 'a', $iconCancel.'&nbsp;'.$w->buttonCancel, [
	'href'		=> './auth/local',
	'class'		=> 'btn',
] );
$buttonSave			= HtmlTag::create( 'button', $iconSend.'&nbsp;'.$w->buttonSend, [
	'type'		=> 'submit',
	'id'		=> 'button_save',
	'class'		=> 'btn btn-primary',
	'name'		=> 'sendPassword',
	'disabled'	=> 'disabled',
] );
$buttonSaveBlock	= HtmlTag::create( 'button', $iconSend.'&nbsp;'.$w->buttonSend, [
	'type'		=> 'submit',
	'id'		=> 'button_save',
	'class'		=> 'btn btn-primary btn-block',
	'name'		=> 'sendPassword',
	'disabled'	=> 'disabled',
] );

$labelEmail	= $w->labelEmail;
if( !empty( $w->labelEmail_info ) )
	$labelEmail	= HtmlTag::create( 'abbr', $labelEmail, ['title' => $w->labelEmail_info] );

extract( $view->populateTexts( ['top', 'info', 'bottom'], 'html/auth/local/password/' ) );

$panelPassword	= HTML::DivClass( 'content-panel content-panel-form', [
	HTML::H3( $w->heading ),
	HTML::DivClass( 'content-panel-inner', [
		HtmlTag::create( 'form', [
			HTML::DivClass( 'row-fluid', [
				HTML::DivClass( 'bs2-span12 bs3-col-md-12 bs4-col-md-12', [
					HtmlTag::create( 'label', $labelEmail, [
						'for'			=> 'input_password_email',
						'class'			=> 'mandatory'
					] ),
					HtmlTag::create( 'input', NULL, [
						'type'			=> 'text',
						'name'			=> 'password_email',
						'id'			=> 'input_password_email',
						'class'			=> 'bs2-span12 bs3-col-md-12 bs4-col-md-12 mandatory',
						'value'			=> htmlentities( $password_email ?? '', ENT_QUOTES, 'UTF-8' ),
						'autocomplete'	=> 'email',
					] )
				] )
			] ),
		/*	HTML::DivClass( 'buttonbar buttonbar-blocks', $buttonSave ),*/
			HTML::DivClass( 'buttonbar', join( ' ', [
				$buttonCancel,
				$buttonSave,
			] ) ),
		], [
			'action'	=> './auth/local/password',
			'method'	=> 'post',
		] )
	] ),
] );


if( 0 !== strlen( trim( strip_tags( $textInfo ) ) ) ){
	return $textTop.
		HTML::DivClass( 'bs2-row-fluid bs3-row bs4-row', [
			HTML::DivClass( 'bs2-span4 bs3-col-md-4 bs4-col-md-4', $panelPassword ),
			HTML::DivClass( 'bs2-span8 bs3-col-md-8 bs4-col-md-8', $textInfo ),
		] ).$textBottom;
}

if( 0 !== strlen( trim( strip_tags( $textTop ) ) ) || 0 !== strlen( trim( strip_tags( $textBottom ) ) ) ){
	return $textTop.$panelLogin.$textBottom;
}

$env->getPage()->addBodyClass( 'auth-centered' );
return HtmlTag::create( 'div', [
	HtmlTag::create( 'div', $panelPassword, ['class' => 'centered-pane'] )
], ['class' => 'centered-pane-container'] );
