<?php

$w				= (object) $words['login'];

$iconLogin		= HTML::Icon( 'ok', TRUE );
$iconRegister	= HTML::Icon( 'plus' );
$iconPassword	= HTML::Icon( 'envelope' );

extract( $view->populateTexts( array( 'top', 'info', 'bottom' ), 'html/auth/login/', array( 'from' => $from ) ) );

$panelLogin	=
HTML::DivClass( "content-panel content-panel-form", array(
	HTML::H3( $w->legend ),
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
				HTML::DivClass( "row-fluid",
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
				) ),
				HTML::DivClass( "buttonbar", array(
					UI_HTML_Tag::create( 'button',  $iconLogin.'&nbsp;'.$w->buttonLogin, array(
						'type'		=> "submit",
						'name'		=> "doLogin",
						'class'		=> "btn btn-primary",
					) ),
					UI_HTML_Tag::create( 'a', $iconPassword.'&nbsp;'.$w->buttonPassword, array(
						'href'		=> './auth/password',
						'class'		=> 'btn btn-small',
					) ),
					UI_HTML_Tag::create( 'a', $iconRegister.'&nbsp;'.$w->buttonRegister, array(
						'href'		=> './auth/register'.( $from ? '?from='.$from : '' ),
						'class'		=> 'btn btn-small',
						'style'		=> !$useRegister ? 'display: none' : NULL,
					) ),
				) )
			), array(
				'action'	=> './auth/login' . ( $from ? '?from='.rawurlencode( $from ) : '' ),
				'name'		=> "editUser",
				'method'	=> "post"
			) )
		)
	)
) );

return
HTML::DivClass( "auth-login-text-top", $textTop ).
HTML::DivClass( "row-fluid", array(
	HTML::DivClass( "span6",
		$panelLogin
	),
	HTML::DivClass( "span6",
		HTML::DivClass( "auth-login-text-info", $textInfo )
	)
) ).
HTML::DivClass( "auth-login-text-bottom", $textBottom );
?>
