<?php

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
			UI_HTML_Tag::create( 'label', array(
				UI_HTML_Tag::create( 'input', NULL, array(
					'type'		=> "checkbox",
					'name'		=> "login_remember",
					'id'		=> "input_login_remember",
					'value'		=> "1",
					'checked'	=> $login_remember ? 'checked' : NULL
				) ),
				UI_HTML_Tag::create( 'abbr', $w->labelRemember, array(
					'title'		=> $w->labelRemember_title
				) ),
			), array( 'class' => "checkbox" ) )
		)
	), array(
		'style'	=> $useRemember ? 'display: none' : NULL
	) );

$buttonLogin	= UI_HTML_Tag::create( 'button',  $iconLogin.'&nbsp;'.$w->buttonLogin, array(
	'type'		=> "submit",
	'name'		=> "doLogin",
	'class'		=> "btn btn-primary",
) );

$buttonPassword	= UI_HTML_Tag::create( 'a', $iconPassword.'&nbsp;'.$w->buttonPassword, array(
	'href'		=> './auth/local/password',
	'class'		=> 'btn btn-small',
) );

$buttonRegister	= "";
if( $useRegister ){
	$buttonRegister	= UI_HTML_Tag::create( 'a', $iconRegister.'&nbsp;'.$w->buttonRegister, array(
		'href'		=> './auth/local/register'.( $from ? '?from='.$from : '' ),
		'class'		=> 'btn btn-small btn-success',
	) );
}

$panelLogin	=
HTML::DivClass( "content-panel content-panel-form", array(
	HTML::H3( $w->heading ),
	HTML::DivClass( "content-panel-inner",
		HTML::DivClass( "auth-login-form",
			UI_HTML_Tag::create( 'form', array(
				( $useCsrf ? View_Helper_CSRF::renderStatic( $env, 'auth/login' ) : '' ),
				HTML::DivClass( "row-fluid",
					HTML::DivClass( "span12", array(
						UI_HTML_Tag::create( 'label', $w->labelUsername, array(
							'for'	=> "input_login_username",
							'class'	=> "mandatory"
						) ),
						UI_HTML_Tag::create( 'input', NULL, array(
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
						UI_HTML_Tag::create( 'label', $w->labelPassword, array(
							'for'	=> "input_login_password",
							'class'	=> "mandatory"
						) ),
						UI_HTML_Tag::create( 'input', NULL, array(
							'value'		=> NULL,
							'class'		=> 'span12 mandatory',
							'type'		=> 'password',
							'name'		=> 'login_password',
							'id'		=> 'input_login_password',
							'required'	=> 'required'
						) )
					) )
				),
				$fieldRemember,
				HTML::DivClass( "buttonbar", array(
					HTML::DivClass( "btn_toolbar", array(
						$buttonLogin,
						$buttonRegister,
						$buttonPassword,
					) )
				) )
			), array(
				'action'	=> './auth/local/login' . ( $from ? '?from='.rawurlencode( $from ) : '' ),
				'name'		=> "editUser",
				'method'	=> "post"
			) )
		)
	)
) );

return $panelLogin;
