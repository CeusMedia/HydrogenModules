<?php

$w				= (object) $words['login'];

$iconLogin		= HTML::Icon( 'ok', TRUE );
$iconRegister	= HTML::Icon( 'plus', TRUE );
$iconPassword	= HTML::Icon( 'envelope' );

if( $env->getModules()->has( 'UI_Font_FontAwesome' ) ){
	$iconLogin		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-sign-in' ) );
	$iconRegister	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-user-plus' ) );
	$iconPassword	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-unlock' ) );
}

$fieldOauth2	= '';
if( $useOauth2 ){
	$iconUnbind			= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );
	$helper				= new View_Helper_Oauth_ProviderButtons( $this->env );
	if( $helper->count() ){
		$helper->setDropdownLabel( 'weitere' );
		$helper->setLinkPath( './auth/oauth2/login/' );
		$fieldOauth2	= UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'label', 'Anmelden mit' ),
				UI_HTML_Tag::create( 'div', array(
					UI_HTML_Tag::create( 'div', $helper->render(), array( 'class' => 'span12' ) ),
				), array( 'class' => 'row-fluid' ) ),
				UI_HTML_Tag::create( 'hr', NULL ),
			), array( 'class' => 'span12' ) ),
		), array( 'class' => 'row-fluid' ) );
	}
}

$fieldRemember	= '';
if( $useRemember )
	$fieldRemember	= HTML::DivClass( 'row-fluid',
		HTML::DivClass( 'span12', array(
			UI_HTML_Tag::create( 'label', array(
				UI_HTML_Tag::create( 'input', NULL, array(
					'type'		=> 'checkbox',
					'name'		=> 'login_remember',
					'id'		=> 'input_login_remember',
					'value'		=> '1',
					'checked'	=> $login_remember ? 'checked' : NULL,
					'class'		=> 'bs4-form-check-input',
				) ),
				UI_HTML_Tag::create( 'abbr', $w->labelRemember, array(
					'title'		=> $w->labelRemember_title,
					'class'		=> 'bs4-form-check-label',
				) ),
			), array( 'class' => 'bs2-checkbox bs4-form-check' ) )
		)
	), array(
		'style'	=> $useRemember ? 'display: none' : NULL
	) );

$linkPassword	= UI_HTML_Tag::create( 'a', $w->linkPassword, array(
	'href'		=> './auth/local/password',
) );
$linkRegister	= UI_HTML_Tag::create( 'a', $w->linkRegister, array(
	'href'		=> './auth/local/register'.( $from ? '?from='.$from : '' ),
) );
$buttonLogin	= UI_HTML_Tag::create( 'button',  $iconLogin.'&nbsp;'.$w->buttonLogin, array(
	'type'		=> 'submit',
	'name'		=> 'doLogin',
	'class'		=> 'btn btn-primary btn-large',
) );

/*$buttonLoginBlock	= UI_HTML_Tag::create( 'button',  $iconLogin.'&nbsp;'.$w->buttonLogin, array(
	'type'		=> 'submit',
	'name'		=> 'doLogin',
	'class'		=> 'btn btn-primary btn-block',
) );
$buttonPasswordBlock	= UI_HTML_Tag::create( 'a', $iconPassword.'&nbsp;'.$w->buttonPassword, array(
	'href'		=> './auth/local/password',
	'class'		=> 'btn btn-block bs3-btn-default bs4-btn-light',
) );
$buttonRegisterBlock	= UI_HTML_Tag::create( 'a', $iconRegister.'&nbsp;'.$w->buttonRegister, array(
	'href'		=> './auth/local/register'.( $from ? '?from='.$from : '' ),
	'class'		=> 'btn btn-block btn-success',
) );*/
if( isset( $limiter ) && $limiter->get( 'Auth.Local.Login:resetPassword' ) === FALSE ){
//	$buttonPasswordBlock	= '';
	$linkPassword			= '';
}
if( !$useRegister ){
//	$buttonRegisterBlock	= '';
	$linkRegister			= '';
}

$panelLogin	=
HTML::DivClass( 'content-panel content-panel-form', array(
	HTML::H3( $w->heading ),
	HTML::DivClass( 'content-panel-inner',
		HTML::DivClass( 'auth-login-form', array(
			$fieldOauth2,
			UI_HTML_Tag::create( 'form', array(
				( $useCsrf ? View_Helper_CSRF::renderStatic( $env, 'auth/login' ) : '' ),
				HTML::DivClass( 'bs2-row-fluid bs3-row bs4-row',
					HTML::DivClass( 'bs2-span12 bs3-col-md12 bs3-form-group bs4-col-md-12 bs4-form-group', array(
						UI_HTML_Tag::create( 'div', array(
							UI_HTML_Tag::create( 'label', $w->labelUsername, array(
								'for'	=> 'input_login_username',
								'class'	=> 'mandatory pull-left'
							) ),
 							UI_HTML_Tag::create( 'small', $linkRegister, array(
								'class' => 'pull-right',
							) ),
						) ),
						UI_HTML_Tag::create( 'input', NULL, array(
							'value'		=> htmlentities( $login_username, ENT_QUOTES, 'UTF-8' ),
							'class'		=> 'bs2-span12 bs3-form-control bs4-form-control mandatory',
							'type'		=> 'text',
							'name'		=> 'login_username',
							'id'		=> 'input_login_username',
							'required'	=> 'required'
						) )
					) )
				),
				HTML::DivClass( 'bs2-row-fluid bs3-row bs4-row',
					HTML::DivClass( 'bs2-span12 bs3-col-md12 bs3-form-group bs4-col-md-12 bs4-form-group', array(
						UI_HTML_Tag::create( 'div', array(
							UI_HTML_Tag::create( 'label', $w->labelPassword, array(
								'for'	=> 'input_login_password',
								'class'	=> 'mandatory pull-left'
							) ),
 							UI_HTML_Tag::create( 'small', $linkPassword, array(
								'class' => 'pull-right',
							) ),
						) ),
						UI_HTML_Tag::create( 'input', NULL, array(
							'value'		=> NULL,
							'class'		=> 'bs2-span12 bs3-form-control bs4-form-control mandatory',
							'type'		=> 'password',
							'name'		=> 'login_password',
							'id'		=> 'input_login_password',
							'required'	=> 'required'
						) )
					) )
				),
				$fieldRemember,
			/*	HTML::DivClass( 'buttonbar buttonbar-blocks', array(
					HTML::DivClass( 'btn_toolbar', array(
					//	$buttonLoginBlock,
					//	$buttonRegisterBlock,
					//	$buttonPasswordBlock,
					) )
				) ),*/
				HTML::DivClass( 'buttonbar', $buttonLogin ),
			), array(
				'action'	=> './auth/local/login' . ( $from ? '?from='.rawurlencode( $from ) : '' ),
				'name'		=> 'editUser',
				'method'	=> 'post'
			) )
		) )
	)
) );

return $panelLogin;
