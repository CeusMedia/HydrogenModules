<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;
use CeusMedia\HydrogenFramework\View;

/** @var Web $env */
/** @var View $view */
/** @var array<array<string,string>> $words */
/** @var ?string $from */
/** @var string $pak */

$w				= (object) $words['confirm'];
$panelConfirm	= renderPanel( $env, $w, $pak, $from );
return renderLayout( $env, $view, $panelConfirm );


function renderLayout( Web $env, View $view, string $panel ): string
{
	[$textTop, $textInfo, $textBottom]	= array_values( $view->populateTexts( ['top', 'info', 'bottom'], 'html/auth/local/confirm/' ) );
	$textTop	= trim( strip_tags( $textTop ?? '' ) );
	$textBottom	= trim( strip_tags( $textBottom ?? '' ) );
	$textInfo	= trim( strip_tags( $textInfo ?? '' ) );

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

function renderPanel( Web $env, object $w, string $pak, ?string $from ): string
{
	$iconSend	= HTML::Icon( 'ok', TRUE );
	if( $env->getModules()->has( 'UI_Font_FontAwesome' ) )
		$iconSend	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );
	return HTML::DivClass( "content-panel content-panel-form", [
		HTML::H3( $w->heading ),
		HTML::DivClass( "content-panel-inner", [
			HtmlTag::create( 'form', [
				HTML::DivClass( 'row-fluid', [
					HTML::DivClass( 'span12', [
						HTML::Label( "confirm_code", $w->labelCode, "mandatory" ),
						HtmlTag::create( 'input', NULL, [
							'type'		=> 'hidden',
							'name'		=> 'from',
							'value'		=> $from
						] ),
						HtmlTag::create( 'input', NULL, [
							'type'		=> 'text',
							'name'		=> 'confirm_code',
							'id'		=> 'input_confirm_code',
							'class'		=> 'bs2-span12 bs3-col-md-12 bs4-col-md-12 mandatory',
							'required'	=> 'required',
							'value'		=> $pak
						] ),
					] ),
				] ),
				HTML::DivClass( "buttonbar", [
					HtmlTag::create( 'button', $iconSend.'&nbsp;'.$w->buttonSend, [
						'type'	=> "submit",
						'name'	=> "confirm",
						'class'	=> "btn btn-primary btn-large"
					] )
				] )
			], [
				'action'	=> './auth/local/confirm',
				'method'	=> 'POST',
				'name'		=> 'auth-confirm'
			] )
		] )
	] );
}
