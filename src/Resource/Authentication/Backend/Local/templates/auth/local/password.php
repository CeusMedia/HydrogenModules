<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;
use CeusMedia\HydrogenFramework\View;

/** @var Web $env */
/** @var View $view */
/** @var array<array<string,string>> $words */

$w			= (object) $words['password'];

$panelPassword	= renderPanel( $env, $w );
return renderLayout( $env, $view, $panelPassword );

function renderLayout( Web $env, View $view, string $panel ): string
{
	extract( $view->populateTexts( ['top', 'info', 'bottom'], 'html/auth/local/password/' ) );
	$textTop	= trim( strip_tags( $textTop ?? '' ) );
	$textBottom	= trim( strip_tags( $textBottom ?? '' ) );
	$textInfo	= trim( strip_tags( $textInfo ?? '' ) );

	if( '' !== $textInfo ){
		return $textTop.
			HTML::DivClass( 'bs2-row-fluid bs3-row bs4-row', [
				HTML::DivClass( 'bs2-span4 bs3-col-md-4 bs4-col-md-4', $panel ),
				HTML::DivClass( 'bs2-span8 bs3-col-md-8 bs4-col-md-8', $textInfo ),
			] ).$textBottom;
	}

	if( '' !== $textTop || '' !== $textBottom ){
		return $textTop.$panel.$textBottom;
	}

	$env->getPage()->addBodyClass( 'auth-centered' );
	return HtmlTag::create( 'div', [
		HtmlTag::create( 'div', $panel, ['class' => 'centered-pane'] )
	], ['class' => 'centered-pane-container'] );
}

function renderPanel( Web $env, object $w ): string
{
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

	return HTML::DivClass( 'content-panel content-panel-form', [
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
}