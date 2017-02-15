<?php

$w	= (object) $words['remove'];

extract( $view->populateTexts( array( 'remove.right', 'remove.panel.top' ), 'html/manage/project/', array( 'project' => $project ) ) );

$relations			= '<div class="muted"><small><em>'.$w->noRelations.'</em></small></div>';
$helperRelations	= new View_Helper_ItemRelationLister( $env );
$helperRelations->setHook( 'Project', 'listRelations', array( 'projectId' => $project->projectId ) );
$helperRelations->setLinkable( TRUE );
$helperRelations->setActiveOnly( FALSE );
//$helperRelations->setTableClass( 'limited' );
$helperRelations->setMode( 'list' );
if( $helperRelations->hasRelations() ){
	$relations	= $helperRelations->render();
}

//$iconCancel   = HTML::Icon( 'icon-arrow-left' );
//$iconSave = HTML::Icon( 'icon-ok', TRUE );

$iconCancel = UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-arrow-left' ) );
$iconSave   = UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-ok icon-white' ) );
if( $env->getModules()->has( 'UI_Font_FontAwesome' ) ){
	$iconCancel = UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
	$iconSave   = UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );
}

return HTML::DivClass( 'row-fluid', array(
	HTML::DivClass( 'span8', array(
		HTML::DivClass( 'content-panel', array(
			UI_HTML_Tag::create( 'h3', UI_HTML_Tag::create( 'span', 'Projekt: ', array( 'class' => 'muted' ) ).$project->title ),
			HTML::DivClass( 'content-panel-inner', array(
				HTML::Form( './manage/project/remove/'.$project->projectId.'/confirmed', 'removeProject', array(
					HTML::H4( $w->heading ),
					$textRemovePanelTop,
					$relations,
					HTML::DivClass( 'buttonbar', array(
						HTML::DivClass( 'btn-toolbar', array(
							UI_HTML_Tag::create( 'a', $iconCancel.'&nbsp;'.$w->buttonCancel, array(
								'href'  => './manage/project/edit/'.$project->projectId,
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
