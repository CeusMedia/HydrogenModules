<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;
use CeusMedia\HydrogenFramework\View;

/** @var Web $env */
/** @var View $view */
/** @var array<array<string,string>> $words */
/** @var ?bool $useOauth2 */
/** @var ?bool $useRemember */
/** @var ?bool $useRegister */
/** @var ?bool $useCsrf */
/** @var ?string $from */
/** @var ?string $login_remember */
/** @var ?string $login_username */

$w				= (object) $words['login'];

$iconLogin		= HTML::Icon( 'ok', TRUE );
$iconRegister	= HTML::Icon( 'user', TRUE );
$iconRegister	= HTML::Icon( 'plus', TRUE );
$iconPassword	= HTML::Icon( 'warning-sign' );
$iconPassword	= HTML::Icon( 'envelope' );

$fieldRemember	= "";
if( $useRemember )
	$fieldRemember	= HTML::DivClass( "row-fluid",
		HTML::DivClass( "span12", [
			HtmlTag::create( 'label', [
				HtmlTag::create( 'input', NULL, [
					'type'		=> "checkbox",
					'name'		=> "login_remember",
					'id'		=> "input_login_remember",
					'value'		=> "1",
					'checked'	=> $login_remember ? 'checked' : NULL
				] ),
				HtmlTag::create( 'abbr', $w->labelRemember, [
					'title'		=> $w->labelRemember_title
				] ),
			], ['class' => "checkbox"] )
		]
	), [
		'style'	=> $useRemember ? 'display: none' : NULL
	] );

$buttonLogin	= HtmlTag::create( 'button',  $iconLogin.'&nbsp;'.$w->buttonLogin, [
	'type'		=> "submit",
	'name'		=> "doLogin",
	'class'		=> "btn btn-primary",
] );

$buttonPassword	= HtmlTag::create( 'a', $iconPassword.'&nbsp;'.$w->buttonPassword, [
	'href'		=> './auth/password',
	'class'		=> 'btn btn-small',
] );

$buttonRegister	= '';
if( $useRegister ){
	$buttonRegister	= HtmlTag::create( 'a', $iconRegister.'&nbsp;'.$w->buttonRegister, [
		'href'		=> './auth/register'.( $from ? '?from='.$from : '' ),
		'class'		=> 'btn btn-small btn-success',
	] );
}

$panelLogin	=
HTML::DivClass( "content-panel content-panel-form", [
	HTML::H3( $w->heading ),
	HTML::DivClass( "content-panel-inner",
		HTML::DivClass( "auth-login-form",
			HtmlTag::create( 'form', [
				( $useCsrf ? View_Helper_CSRF::renderStatic( $env, 'auth/oauth/login' ) : '' ),
				HTML::DivClass( "row-fluid",
					HTML::DivClass( "span12", [
						HtmlTag::create( 'label', $w->labelUsername, [
							'for'	=> "input_login_username",
							'class'	=> "mandatory"
						] ),
						HtmlTag::create( 'input', NULL, [
							'value'		=> htmlentities( $login_username, ENT_QUOTES, 'UTF-8' ),
							'class'		=> 'span12 mandatory',
							'type'		=> 'text',
							'name'		=> 'login_username',
							'id'		=> 'input_login_username',
							'required'	=> 'required'
						] )
					] )
				),
				HTML::DivClass( "row-fluid",
					HTML::DivClass( "span12", [
						HtmlTag::create( 'label', $w->labelPassword, [
							'for'	=> "input_login_password",
							'class'	=> "mandatory"
						] ),
						HtmlTag::create( 'input', NULL, [
							'value'		=> NULL,
							'class'		=> 'span12 mandatory',
							'type'		=> 'password',
							'name'		=> 'login_password',
							'id'		=> 'input_login_password',
							'required'	=> 'required'
						] )
					] )
				),
				$fieldRemember,
				HTML::DivClass( "buttonbar", [
					HTML::DivClass( "btn_toolbar", [
						$buttonLogin,
						$buttonRegister,
						$buttonPassword,
					] )
				] )
			], [
				'action'	=> './auth/oauth/login' . ( $from ? '?from='.rawurlencode( $from ) : '' ),
				'name'		=> "editUser",
				'method'	=> "post"
			] )
		)
	)
] );

return $panelLogin;
