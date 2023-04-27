<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w				= (object) $words['login'];

$iconLogin		= HTML::Icon( 'ok', TRUE );
$iconRegister	= HTML::Icon( 'user', TRUE );
$iconRegister	= HTML::Icon( 'plus', TRUE );
$iconPassword	= HTML::Icon( 'warning-sign' );
$iconPassword	= HTML::Icon( 'envelope' );

$fieldRemember	= "";
if( $useRemember )
	$fieldRemember	= HTML::DivClass( "row-fluid",
		HTML::DivClass( "span12", array(
			HtmlTag::create( 'label', array(
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
			), ['class' => "checkbox"] )
		)
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

$buttonRegister	= "";
if( $useRegister ){
	$buttonRegister	= HtmlTag::create( 'a', $iconRegister.'&nbsp;'.$w->buttonRegister, array(
		'href'		=> './auth/register'.( $from ? '?from='.$from : '' ),
		'class'		=> 'btn btn-small btn-success',
	) );
}

$panelLogin	=
HTML::DivClass( "content-panel content-panel-form", array(
	HTML::H3( $w->heading ),
	HTML::DivClass( "content-panel-inner",
		HTML::DivClass( "auth-login-form",
			HtmlTag::create( 'form', array(
				( $useCsrf ? View_Helper_CSRF::renderStatic( $env, 'auth/oauth/login' ) : '' ),
				HTML::DivClass( "row-fluid",
					HTML::DivClass( "span12", array(
						HtmlTag::create( 'label', $w->labelUsername, [
							'for'	=> "input_login_username",
							'class'	=> "mandatory"
						] ),
						HtmlTag::create( 'input', NULL, array(
							'value'		=> htmlentities( $login_username, ENT_QUOTES, 'UTF-8' ),
							'class'		=> 'span12 mandatory',
							'type'		=> 'text',
							'name'		=> 'login_username',
							'id'		=> 'input_login_username',
							'required'	=> 'required'
						) )
					) )
				),
				HTML::DivClass( "row-fluid",
					HTML::DivClass( "span12", array(
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
					) )
				),
				$fieldRemember,
				HTML::DivClass( "buttonbar", array(
					HTML::DivClass( "btn_toolbar", [
						$buttonLogin,
						$buttonRegister,
						$buttonPassword,
					] )
				) )
			), array(
				'action'	=> './auth/oauth/login' . ( $from ? '?from='.rawurlencode( $from ) : '' ),
				'name'		=> "editUser",
				'method'	=> "post"
			) )
		)
	)
) );

return $panelLogin;
