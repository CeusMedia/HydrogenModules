<?php

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use CeusMedia\HydrogenFramework\View;

/** @var WebEnvironment $env */
/** @var View $view */
/** @var array<string,array<string,string>> $words */
/** @var bool $canChangeUsername */

if( !$canChangeUsername )
	return '';

if( 0 && !$env->getConfig()->get( 'module.manage_my_user.username.changeable' ) )
	return '';

//  --  PANEL: USERNAME  --  //
$w	= (object) $words['username'];

extract( $view->populateTexts( ['panel.username.above', 'panel.username.below', 'panel.username.info'], 'html/manage/my/user/' ) );

return HTML::DivClass( 'content-panel content-panel-form', [
	HtmlTag::create( 'h4', $w->heading ),
	HTML::DivClass( 'content-panel-inner', [
		HTML::Form( './manage/my/user/username', 'my_user_username', [
			HTML::DivClass( 'row-fluid', [
				HTML::DivClass( 'span6', [
					HTML::DivClass( 'row-fluid',
						HTML::DivClass( 'span12', [
							HTML::Label( 'email', $w->labelUsernameOld ),
							HtmlTag::create( 'input', NULL, [
								'type'			=> "text",
								'class'			=> "span11 ",
								'disabled'		=> 'disabled',
								'readonly'		=> 'readonly',
								'value'			=> htmlentities( $user->username, ENT_QUOTES, 'UTF-8' ),
							] ),
						] )
					),
					HTML::DivClass( 'row-fluid',
						HTML::DivClass( 'span12', [
							HTML::Label( 'username', $w->labelUsernameNew, 'mandatory', $w->labelUsernameNew_title ),
							HtmlTag::create( 'input', NULL, [
								'type'			=> "text",
								'name'			=> "username",
								'id'			=> "input_username",
								'class'			=> "span11 mandatory",
								'required'		=> 'required',
								'value'			=> '',
								'autocomplete'	=> "off"
							] ),
						] )
					),
				] ),
				HTML::DivClass( 'span6', $textPanelUsernameInfo ),
			] ),
			HTML::Buttons( [
				HtmlTag::create( 'small', $w->labelPasswordCurrent_title, ['class' => 'not-muted'] ),
				HTML::DivClass( 'row-fluid',
					HTML::DivClass( 'span6', [
						HTML::DivClass( 'input-prepend input-append',
							HTML::SpanClass( 'add-on', '<i class="fa fa-fw fa-lock"></i>' ).
							HtmlTag::create( 'input', '', [
								'type'			=> 'password',
								'name'			=> 'password',
								'id'			=> 'input_password',
								'class'			=> 'span7',
								'required'		=> 'required',
								'autocomplete'	=> 'current-password',
								'placeholder'	=> $w->labelPasswordCurrent,
							] ).
							HtmlElements::Button( 'saveUser', '<i class="fa fa-fw fa-check"></i> '.$w->buttonSave, 'btn btn-primary' )
						)
					] )
				)
			] )
		] )
	] )
] );
