<?php

$w	= (object) $words['remove'];

extract( $view->populateTexts( array(
	'remove.top',
	'remove.above',
	'remove.info',
	'remove.right',
	'remove.below',
	'remove.bottom',
), 'html/manage/my/user/' ) );

$relations			= '<div class="muted"><small><em>'.$w->noRelations.'</em></small></div>';
$helperRelations	= new View_Helper_ItemRelationLister( $env );
$helperRelations->setHook( 'User', 'listRelations', array( 'userId' => $userId, 'linkable' => TRUE ) );
$helperRelations->setLinkable( TRUE );
$helperRelations->setActiveOnly( FALSE );
//$helperRelations->setTableClass( 'limited' );
$helperRelations->setMode( 'list' );
$helperRelations->setLimit( 10 );
$helperRelations->setTableClass( 'limited' );
if( $helperRelations->hasRelations() )
	$relations	= $helperRelations->render();

$iconCancel = UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-arrow-left' ) );
$iconSave   = UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-ok icon-white' ) );
if( $env->getModules()->has( 'UI_Font_FontAwesome' ) ){
	$iconCancel = UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
	$iconSave   = UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );
}

return HTML::DivClass( 'row-fluid', array(
	HTML::DivClass( 'span8', array(
		HTML::DivClass( 'content-panel', array(
			UI_HTML_Tag::create( 'h3', UI_HTML_Tag::create( 'span', 'Benutzer: ', array( 'class' => 'muted' ) ).$user->username ),
			HTML::DivClass( 'content-panel-inner', array(
				HTML::Form( './manage/my/user/remove/confirmed', 'removeUser', array(
					HTML::H4( $w->heading ),
					$textRemoveTop,
					$relations,
					UI_HTML_Tag::create( 'hr' ),
					HTML::DivClass( 'row-fluid', array(
						HTML::DivClass( 'span6', array(
							HTML::DivClass( 'row-fluid', array(
								HTML::DivClass( 'span12', array(
									HTML::Label( 'username', $w->labelPassword, 'mandatory', $w->labelPassword_title ),
									UI_HTML_Tag::create( 'input', NULL, array(
										'type'			=> "password",
										'name'			=> "password",
										'id'			=> "input_password_username",
										'class'			=> "span11 mandatory",
										'required'		=> 'required',
										'value'			=> '',
										'placeholder'	=> $w->labelPassword_holder,
										'autocomplete'	=> "current-password"
									) ),
								) )
							) ),
						) ),
						HTML::DivClass( 'span6', $textRemoveInfo ),
					) ),
					HTML::DivClass( 'buttonbar', array(
						HTML::DivClass( 'btn-toolbar', array(
							UI_HTML_Tag::create( 'a', $iconCancel.'&nbsp;'.$w->buttonCancel, array(
								'href'  => './manage/my/user',
								'class' => 'btn btn-small',
							) ),
							UI_HTML_Tag::create( 'button', $iconSave.'&nbsp;'.$w->buttonRemove, array(
								'type'  => 'submit',
								'name'  => 'remove',
								'class' => 'btn btn-danger',
							) )
						) )
					) )
				) )
			) )
		) )
	) ),
	HTML::DivClass( 'span4', array(
		$textRemoveRight
	) )
) );
?>
