<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;
use CeusMedia\HydrogenFramework\View;

/** @var Web $env */
/** @var View $view */
/** @var array<array<string,string>> $words */
/** @var ?string $from */

$w			= (object) $words['update'];
$panelUpdate	= renderPanel( $env, $w, $from );
return renderLayout( $env, $view, $panelUpdate );


function renderLayout( Web $env, View $view, string $panel ): string
{
	$texts		= (object) $view->populateTexts( ['top', 'info', 'bottom'], 'html/auth/local/update/' );
	$textTop	= trim( strip_tags( $texts->textTop ?? '' ) );
	$textBottom	= trim( strip_tags( $texts->textBottom ?? '' ) );
	$textInfo	= trim( strip_tags( $texts->textInfo ?? '' ) );

	if( '' !== $textInfo )
		return $textTop.
			HTML::DivClass( 'bs2-row-fluid bs3-row bs4-row', [
				HTML::DivClass( 'bs2-span4 bs3-col-md-4 bs4-col-md-4 bs2-offset1 bs3-col-offset-1 bs4-col-offset-1', $panel ),
				HTML::DivClass( 'bs2-span6 bs3-col-md-6 bs4-col-md-6', $textInfo ),
			] ).$textBottom;

	if( '' !== $textTop || '' !== $textBottom )
		return $textTop.$panel.$textBottom;

	$env->getPage()->addBodyClass( 'auth-centered' );
	return HtmlTag::create( 'div', [
		HtmlTag::create( 'div', $panel, ['class' => 'centered-pane'] )
	], ['class' => 'centered-pane-container'] );
}

function renderPanel( Web $env, object $w, ?string $from ): string
{
	$iconCancel	= HTML::Icon(	'arrow-left' );
	$iconSend	= HTML::Icon( 'envelope', TRUE );
	if( $env->getModules()->has( 'UI_Font_FontAwesome' ) ){
		$iconCancel	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-left'] );
		$iconSend	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );
	}

	$buttonCancel	= HtmlTag::create( 'a', $iconCancel . '&nbsp;' . $w->buttonCancel, [
		'href'		=> './',
		'class'		=> 'btn',
	] );
	$buttonSave		= HtmlTag::create( 'button', $iconSend . '&nbsp;' . $w->buttonSend, [
		'type'		=> 'submit',
		'id'		=> 'button_save',
		'class'		=> 'btn btn-primary',
		'name'		=> 'sendPassword',
		'disabled'	=> 'disabled',
	] );
	$buttonSaveBlock = HtmlTag::create( 'button', $iconSend . '&nbsp;' . $w->buttonSend, [
		'type'		=> 'submit',
		'id'		=> 'button_save',
		'class'		=> 'btn btn-primary btn-block',
		'name'		=> 'sendPassword',
		'disabled'	=> 'disabled',
	] );

	$labelPassword = $w->labelPassword;
	if( !empty( $w->labelPassword_info ) )
		$labelPassword = HtmlTag::create( 'abbr', $labelPassword, ['title' => $w->labelPassword_info] );

	return HTML::DivClass( 'content-panel content-panel-form', [
		HTML::H3( $w->heading ),
		HTML::DivClass( 'content-panel-inner', [
			HtmlTag::create( 'form', [
				HTML::DivClass( 'row-fluid', [
					HTML::DivClass( 'bs2-span12 bs3-col-md-12 bs4-col-md-12', [
						HtmlTag::create( 'label', $labelPassword, [
							'for'	=> 'input_password',
							'class'	=> 'mandatory'
						] ),
						HtmlTag::create( 'input', NULL, [
							'type'	=> 'text',
							'name'	=> 'password',
							'id'	=> 'input_password',
							'class'	=> 'bs2-span12 bs3-col-md-12 bs4-col-md-12 mandatory',
							'value'	=> htmlentities( $password ?? '', ENT_QUOTES, 'UTF-8' )
						] )
					] )
				] ),
				/*	HTML::DivClass( 'buttonbar buttonbar-blocks', $buttonSave ),*/
				HTML::DivClass( 'buttonbar', join( ' ', [
					$buttonCancel,
					$buttonSave,
				] ) ),
			], [
				'action'	=> './auth/local/update'.( $from ? '?from='.$from : '' ),
				'method'	=> 'post',
			] )
		] ),
	] );
}