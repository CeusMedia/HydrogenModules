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

$w	= (object) $words['login'];

$iconLogin		= HTML::Icon( 'ok', TRUE );
$iconRegister	= HTML::Icon( 'plus', TRUE );
$iconPassword	= HTML::Icon( 'envelope' );

if( $env->getModules()->has( 'UI_Font_FontAwesome' ) ){
	$iconLogin		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-sign-in'] );
	$iconRegister	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-user-plus'] );
	$iconPassword	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-unlock'] );
}

$fieldOauth2	= '';
if( $useOauth2 ?? FALSE ){
	$iconUnbind			= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] );
	$helper				= new View_Helper_Oauth_ProviderButtons( $this->env );
	if( $helper->count() ){
		$helper->setDropdownLabel( 'weitere' );
		$helper->setLinkPath( './auth/oauth2/login/' );
		$fieldOauth2	= HtmlTag::create( 'div', [
			HtmlTag::create( 'div', [
				HtmlTag::create( 'label', 'Anmelden mit' ),
				HtmlTag::create( 'div', [
					HtmlTag::create( 'div', $helper->render(), ['class' => 'bs2-span12 bs3-col-md-12 bs4-col-md-12'] ),
				], ['class' => 'bs2-row-fluid bs3-row bs4-row'] ),
				HtmlTag::create( 'hr', NULL ),
			], ['class' => 'bs2-span12 bs3-col-md-12 bs4-col-md-12'] ),
		], ['class' => 'bs2-row-fluid bs3-row bs4-row'] );
	}
}

$fieldRemember	= '';
if( FALSE && ( $useRemember ?? FALSE ) ){			//  disabled: insecure and not supporting user password resource
	$inputRemember	= HtmlTag::create( 'input', NULL, [
		'type'		=> 'checkbox',
		'name'		=> 'login_remember',
		'id'		=> 'input_login_remember',
		'value'		=> '1',
		'checked'	=> $login_remember ? 'checked' : NULL,
		'class'		=> 'bs4-form-check-input',
	] );
	$textRemember	= HtmlTag::create( 'abbr', $w->labelRemember, [
		'title'		=> $w->labelRemember_title,
		'class'		=> 'bs4-form-check-label',
	] );
	$labelRemember	= HtmlTag::create( 'label', [
		$inputRemember,
		$textRemember,
	], ['class' => 'bs2-checkbox bs4-form-check'] );
	$fieldRemember	= HTML::DivClass( 'bs2-row-fluid bs3-row bs4-row', [
		HTML::DivClass( 'bs2-span12 bs3-col-md-12 bs4-col-md-12', $labelRemember )
	] );
}

$linkPassword	= HtmlTag::create( 'a', $w->linkPassword, [
	'href'		=> './auth/local/password',
	'tabindex'	=> -1,
] );
$linkRegister	= HtmlTag::create( 'a', $w->linkRegister, [
	'href'		=> './auth/local/register'.( $from ? '?from='.$from : '?from=auth/login' ),
	'tabindex'	=> -1,
] );
$buttonLogin	= HtmlTag::create( 'button',  $iconLogin.'&nbsp;'.$w->buttonLogin, [
	'type'		=> 'submit',
	'name'		=> 'doLogin',
	'class'		=> 'btn btn-primary btn-large btn-block',
] );

/*$buttonLoginBlock	= HtmlTag::create( 'button',  $iconLogin.'&nbsp;'.$w->buttonLogin, [
	'type'		=> 'submit',
	'name'		=> 'doLogin',
	'class'		=> 'btn btn-primary btn-block',
] );
$buttonPasswordBlock	= HtmlTag::create( 'a', $iconPassword.'&nbsp;'.$w->buttonPassword, [
	'href'		=> './auth/local/password',
	'class'		=> 'btn btn-block bs3-btn-default bs4-btn-light',
] );
$buttonRegisterBlock	= HtmlTag::create( 'a', $iconRegister.'&nbsp;'.$w->buttonRegister, [
	'href'		=> './auth/local/register'.( $from ? '?from='.$from : '' ),
	'class'		=> 'btn btn-block btn-success',
] );*/
if( isset( $limiter ) && $limiter->get( 'Auth.Local.Login:resetPassword' ) === FALSE ){
//	$buttonPasswordBlock	= '';
	$linkPassword			= '';
}
if( !$useRegister ){
//	$buttonRegisterBlock	= '';
	$linkRegister			= '';
}

$panelLogin	=
HTML::DivClass( 'content-panel content-panel-form', [
	HTML::H3( $w->heading ),
	HTML::DivClass( 'content-panel-inner',
		HTML::DivClass( 'auth-login-form', [
			$fieldOauth2,
			HtmlTag::create( 'form', [
				( $useCsrf ? View_Helper_CSRF::renderStatic( $env, 'auth/login' ) : '' ),
				HTML::DivClass( 'bs2-row-fluid bs3-row bs4-row',
					HTML::DivClass( 'bs2-span12 bs3-col-md12 bs3-form-group bs4-col-md-12 bs4-form-group', [
						HtmlTag::create( 'div', [
							HtmlTag::create( 'label', [
								$w->labelUsername,
								HtmlTag::create( 'small', $linkRegister, [
									'class' => 'pull-right float-right',
								] ),
							], [
								'for'	=> 'input_login_username',
								'class'	=> 'mandatory not-pull-left'
							] ),
						] ),
						HtmlTag::create( 'input', NULL, [
							'value'		=> htmlentities( $login_username ?? '', ENT_QUOTES, 'UTF-8' ),
							'class'		=> 'bs2-span12 bs3-form-control bs3-input-lg bs4-form-control bs4-form-control-lg mandatory',
							'type'		=> 'text',
							'name'		=> 'login_username',
							'id'		=> 'input_login_username',
							'required'	=> 'required'
						] )
					] )
				),
				HTML::DivClass( 'bs2-row-fluid bs3-row bs4-row',
					HTML::DivClass( 'bs2-span12 bs3-col-md12 bs3-form-group bs4-col-md-12 bs4-form-group', [
						HtmlTag::create( 'div', [
							HtmlTag::create( 'label', [
								$w->labelPassword,
								HtmlTag::create( 'small', $linkPassword, [
									'class' => 'pull-right',
								] ),
							], [
								'for'	=> 'input_login_password',
								'class'	=> 'mandatory not-pull-left'
							] ),
						] ),
						HtmlTag::create( 'input', NULL, [
							'value'		=> NULL,
							'class'		=> 'bs2-span12 bs3-form-control bs4-form-control mandatory',
							'type'		=> 'password',
							'name'		=> 'login_password',
							'id'		=> 'input_login_password',
							'required'	=> 'required'
						] )
					] )
				),
				$fieldRemember,
			/*	HTML::DivClass( 'buttonbar buttonbar-blocks', [
					HTML::DivClass( 'btn_toolbar', [
					//	$buttonLoginBlock,
					//	$buttonRegisterBlock,
					//	$buttonPasswordBlock,
					] )
				] ),*/
				HTML::DivClass( 'buttonbar', $buttonLogin ),
			], [
				'action'	=> './auth/local/login' . ( $from ? '?from='.rawurlencode( $from ) : '' ),
				'name'		=> 'editUser',
				'method'	=> 'post'
			] )
		] )
	)
] );

return $panelLogin;
