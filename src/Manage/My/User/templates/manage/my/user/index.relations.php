<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/** @var WebEnvironment $env */
/** @var int|string $currentUserId */

$panelRelations		= '';
$helperRelations	= new View_Helper_ItemRelationLister( $env );
$helperRelations->setHook( 'User', 'listRelations', ['userId' => $currentUserId, 'linkable' => TRUE] );
$helperRelations->setLinkable( TRUE );
$helperRelations->setActiveOnly( FALSE );
//$helperRelations->setTableClass( 'limited' );
$helperRelations->setMode( 'list' );
$helperRelations->setLimit( 5 );
$helperRelations->setTableClass( 'limited' );
$helperRelations->setHintTextForEntities( '' );
$helperRelations->setHintTextForRelations( '' );

if( $helperRelations->hasRelations() ){
	$panelRelations	= HTML::DivClass( 'content-panel content-panel-form', [
		HtmlTag::create( 'h4', 'Zugehörige Daten' ),
		HTML::DivClass( 'content-panel-inner', [
			$helperRelations->render()
		] ),
	] );
}
return $panelRelations;
