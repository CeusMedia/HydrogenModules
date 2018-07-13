<?php

$w	= (object) $words['remove'];

extract( $view->populateTexts( array( 'remove.right', 'remove.panel.top' ), 'html/manage/my/user/' ) );
extract( $view->populateTexts( array( 'panel.remove.above', 'panel.remove.below', 'panel.remove.info' ), 'html/manage/my/user/' ) );

$relations			= '<div class="muted"><small><em>'.$w->noRelations.'</em></small></div>';
$helperRelations	= new View_Helper_ItemRelationLister( $env );
$helperRelations->setHook( 'User', 'listRelations', array( 'userId' => $userId, 'linkable' => TRUE ) );
$helperRelations->setLinkable( TRUE );
$helperRelations->setActiveOnly( FALSE );
//$helperRelations->setTableClass( 'limited' );
$helperRelations->setMode( 'list' );
$helperRelations->setLimit( 7 );
$helperRelations->setTableClass( 'limited' );

$iconCancel = UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-arrow-left' ) );
$iconSave   = UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-ok icon-white' ) );
if( $env->getModules()->has( 'UI_Font_FontAwesome' ) ){
	$iconCancel = UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
	$iconSave   = UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );
}

return HTML::DivClass( 'row-fluid', array(
	HTML::DivClass( 'span8', array(
		HTML::DivClass( 'content-panel', array(
			UI_HTML_Tag::create( 'h3', UI_HTML_Tag::create( 'span', 'Benutzer: ', array( 'class' => 'muted' ) ).$userId ),
			HTML::DivClass( 'content-panel-inner', array(
				HTML::Form( './manage/my/user/remove/confirmed', 'removeUser', array(
					HTML::H4( $w->heading ),
					$textRemovePanelTop,
					$helperRelations->render(),
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
										'autocomplete'	=> "off"
									) ),
								) )
							) ),
						) ),
						HTML::DivClass( 'span6', $textPanelRemoveInfo ),
					) ),
					HTML::DivClass( 'buttonbar', array(
						HTML::DivClass( 'btn-toolbar', array(
							UI_HTML_Tag::create( 'a', $iconCancel.'&nbsp;'.$w->buttonCancel, array(
								'href'  => './manage/my/user/'.$userId,
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
