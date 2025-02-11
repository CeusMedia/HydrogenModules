<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\View;

/** @var View $view */
/** @var array<string,array<string,string>> $words */

$w	= (object) $words['remove'];

extract( $view->populateTexts( [
	'remove.top',
	'remove.above',
	'remove.info',
	'remove.right',
	'remove.below',
	'remove.bottom',
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

return HTML::DivClass( 'row-fluid', array(
	HTML::DivClass( 'span8', array(
		HTML::DivClass( 'content-panel', array(
			HtmlTag::create( 'h3', HtmlTag::create( 'span', 'Benutzer: ', ['class' => 'muted'] ).$user->username ),
			HTML::DivClass( 'content-panel-inner', array(
				HTML::Form( './manage/my/user/remove/confirmed', 'removeUser', array(
					HTML::H4( $w->heading ),
					$textRemoveTop,
					$relations,
					HtmlTag::create( 'hr' ),
					HTML::DivClass( 'row-fluid', array(
						HTML::DivClass( 'span6', array(
							HTML::DivClass( 'row-fluid', array(
								HTML::DivClass( 'span12', array(
									HTML::Label( 'username', $w->labelPassword, 'mandatory', $w->labelPassword_title ),
									HtmlTag::create( 'input', NULL, [
										'type'			=> "password",
										'name'			=> "password",
										'id'			=> "input_password_username",
										'class'			=> "span11 mandatory",
										'required'		=> 'required',
										'value'			=> '',
										'placeholder'	=> $w->labelPassword_holder,
										'autocomplete'	=> "current-password"
									] ),
								) )
							) ),
						) ),
						HTML::DivClass( 'span6', $textRemoveInfo ),
					) ),
					HTML::DivClass( 'buttonbar', array(
						HTML::DivClass( 'btn-toolbar', array(
							HtmlTag::create( 'a', $iconCancel.'&nbsp;'.$w->buttonCancel, [
								'href'  => './manage/my/user',
								'class' => 'btn btn-small',
							] ),
							HtmlTag::create( 'button', $iconSave.'&nbsp;'.$w->buttonRemove, [
								'type'  => 'submit',
								'name'  => 'remove',
								'class' => 'btn btn-danger',
							] )
						) )
					) )
				) )
			) )
		) )
	) ),
	HTML::DivClass( 'span4', [
		$textRemoveRight
	] )
) );
