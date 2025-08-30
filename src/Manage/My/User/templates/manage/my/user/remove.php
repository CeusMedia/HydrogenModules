<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use CeusMedia\HydrogenFramework\View;

/** @var WebEnvironment $env */
/** @var View $view */
/** @var array<string,array<string,string>> $words */

$w	= (object) $words['remove'];

$tabs	= View_Manage_My_User::renderTabs( $env, 'remove' );

extract( $view->populateTexts( [
	'remove.top',
	'remove.above',
	'remove.info',
	'remove.right',
	'remove.below',
	'remove.bottom',
], 'html/manage/my/user/' ) );

extract( $view->populateTexts( [
	'panel.remove.top',
	'panel.remove.above',
	'panel.remove.info',
	'panel.remove.below',
	'panel.remove.bottom',
], 'html/manage/my/user/' ) );

$relations			= '<div class="muted"><small><em>'.$w->noRelations.'</em></small></div>';
$helperRelations	= new View_Helper_ItemRelationLister( $env );
$helperRelations->setHook( 'User', 'listRelations', ['userId' => $userId, 'linkable' => TRUE] );
$helperRelations->setLinkable( TRUE );
$helperRelations->setActiveOnly( FALSE );
//$helperRelations->setTableClass( 'limited' );
$helperRelations->setMode( 'list' );
$helperRelations->setLimit( 10 );
$helperRelations->setTableClass( 'limited' );
if( $helperRelations->hasRelations() )
	$relations	= $helperRelations->render();

$iconCancel = HtmlTag::create( 'i', '', ['class' => 'icon-arrow-left'] );
$iconSave   = HtmlTag::create( 'i', '', ['class' => 'icon-ok icon-white'] );
if( $env->getModules()->has( 'UI_Font_FontAwesome' ) ){
	$iconCancel = HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-left'] );
	$iconSave   = HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );
}

$inputPassword	= HtmlTag::create( 'input', NULL, [
	'type'			=> "password",
	'name'			=> "password",
	'id'			=> "input_password_username",
	'class'			=> "span11 mandatory",
	'required'		=> 'required',
	'value'			=> '',
	'placeholder'	=> $w->labelPassword_holder,
	'autocomplete'	=> "current-password"
] );

$buttonCancel	= HtmlTag::create( 'a', $iconCancel.'&nbsp;'.$w->buttonCancel, [
	'href'  => './manage/my/user',
	'class' => 'btn btn-small',
] );

$buttonSave		= HtmlTag::create( 'button', $iconSave.'&nbsp;'.$w->buttonRemove, [
	'type'  => 'submit',
	'name'  => 'remove',
	'class' => 'btn btn-danger',
] );

$panel	= HTML::DivClass( 'content-panel', [
	HtmlTag::create( 'h3', HtmlTag::create( 'span', 'Benutzer: ', ['class' => 'muted'] ).$user->username ),
	HTML::DivClass( 'content-panel-inner', [
		HTML::Form( './manage/my/user/remove/confirmed', 'manageMyUserRemove', [
			HTML::H4( $w->heading ),
			$textRemoveTop,
			$relations,
			HtmlTag::create( 'hr' ),
			HTML::DivClass( 'row-fluid', [
				HTML::DivClass( 'span6', [
					HTML::DivClass( 'row-fluid', [
						HTML::DivClass( 'span12', [
							HTML::Label( 'password', $w->labelPassword, 'mandatory', $w->labelPassword_title ),
							$inputPassword,
						] )
					] ),
				] ),
				HTML::DivClass( 'span6', $textRemoveInfo ),
			] ),
			View_Helper_CSRF::renderStatic( $this->env, 'manageMyUserRemove' ),
			HTML::DivClass( 'buttonbar', [
				HTML::DivClass( 'btn-toolbar', [$buttonCancel, $buttonSave] )
			] )
		] )
	] )
] );

return $tabs.'<div class="content-panel">
	<h3>Konto entfernen</h3>
	<div class="content-panel-inner">
		'.$textPanelRemoveAbove.'
	</div>
</div>'.HTML::DivClass( 'row-fluid', [
		HTML::DivClass( 'span8', $panel ),
		HTML::DivClass( 'span4', $textRemoveRight )
	] );


