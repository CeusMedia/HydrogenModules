<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$panelRelations		= '';
$helperRelations	= new View_Helper_ItemRelationLister( $env );
$helperRelations->setHook( 'User', 'listRelations', array( 'userId' => $currentUserId, 'linkable' => TRUE ) );
$helperRelations->setLinkable( TRUE );
$helperRelations->setActiveOnly( FALSE );
//$helperRelations->setTableClass( 'limited' );
$helperRelations->setMode( 'list' );
$helperRelations->setLimit( 5 );
$helperRelations->setTableClass( 'limited' );
$helperRelations->setHintTextForEntities( '' );
$helperRelations->setHintTextForRelations( '' );
if( $helperRelations->hasRelations() ){
	$panelRelations	= HTML::DivClass( 'content-panel content-panel-form', array(
		HtmlTag::create( 'h4', 'Zugehörige Daten' ),
		HTML::DivClass( 'content-panel-inner', array(
			$helperRelations->render()
		) ),
	) );
}
return $panelRelations;
