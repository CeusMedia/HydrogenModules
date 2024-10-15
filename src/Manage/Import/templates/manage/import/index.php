<?php /** @noinspection DuplicatedCode */

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

/** @var Environment $env */
/** @var array $connections */
/** @var array $connectorMap */

$iconAdd	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-plus'] );

$table		= HtmlTag::create( 'div', 'No connections found.', array ('class' => 'hint' ) );

//print_m( $connectorMap );

$statusHelper	= View_Helper_StatusBadge::create()
	->setStatusMap( [
		View_Helper_StatusBadge::STATUS_POSITIVE	=> 1,
		View_Helper_StatusBadge::STATUS_NEGATIVE	=> 0,
	] )
	->setLabelMap( [
		View_Helper_StatusBadge::STATUS_POSITIVE	=> 'aktiviert',
		View_Helper_StatusBadge::STATUS_NEGATIVE	=> 'deaktiviert',
	] );

$authTypes	= [
	0	=> 'keine',
	1	=> 'per Login',
	2	=> 'mit Schlüssel',
];

if( count( $connections ) > 0 ){
	$rows	= [];
	foreach( $connections as $connection ){
		$link	= HtmlTag::create( 'a', $connection->title, ['href' => 'manage/import/edit/'.$connection->importConnectionId] );
		$rows[]	= HtmlTag::create( 'tr', [
			HtmlTag::create( 'td', $connection->importConnectionId ),
			HtmlTag::create( 'td', $link ),
			HtmlTag::create( 'td', $statusHelper->setStatus( $connection->status ) ),
			HtmlTag::create( 'td', $authTypes[$connection->authType] ),
			HtmlTag::create( 'td', $connectorMap[$connection->importConnectorId]->title ),
			HtmlTag::create( 'td', View_Helper_TimePhraser::convertStatic( $env, $connection->createdAt, TRUE, 'vor' ) ),
			HtmlTag::create( 'td', View_Helper_TimePhraser::convertStatic( $env, $connection->modifiedAt, TRUE, 'vor' ) ),
		]);
	}
	$thead	= HtmlElements::TableHeads( ['ID', 'Titel', 'Zustand', 'Zugang', 'Connector', 'erstellt', 'verändert'] );
	$tbody	= HtmlTag::create( 'tbody', $rows );

	$colgroup	= HtmlElements::ColumnGroup( ['5%', '', '8%', '10%', '20%', '15%', '15%'] );
	$table	= HtmlTag::create( 'table', [$colgroup, $thead, $tbody], ['class' => 'table'] );
}

$buttonAdd	= HtmlTag::create( 'a', $iconAdd.'&nbsp;hinzufügen', [
	'href'	=> 'manage/import/add',
	'class'	=> 'btn btn-success']
);

$panelConnectionList	= HtmlTag::create( 'div', [
	HtmlTag::create( 'h3', 'Importverbindungen' ),
	HtmlTag::create( 'div', [
		$table,
		HtmlTag::create( 'div', $buttonAdd, ['class' => 'buttonbar'] ),
	], ['class' => 'content-panel-inner'] ),
], ['class' => 'content-panel'] );

//  --

$panelConnectorList	= '';
//$table	= HtmlTag::create( 'div', 'No Connectors found.', ['class' => 'alert-info']);
if( [] !== $connectorMap ){
	$rows	= [];
/*	$statusHelper	= View_Helper_StatusBadge::create()
		->setStatusMap( [
			View_Helper_StatusBadge::STATUS_POSITIVE	=> 1,
			View_Helper_StatusBadge::STATUS_NEGATIVE	=> 0,
		] )
		->setLabelMap( [
			View_Helper_StatusBadge::STATUS_POSITIVE	=> 'aktiviert',
			View_Helper_StatusBadge::STATUS_NEGATIVE	=> 'deaktiviert',
		] );*/
	$types	= [
		1	=> 'Pull: asynchron',
		2	=> 'Pull: synchron',
		3	=> 'Push: POST',
		4	=> 'Push: PUT',
	];

	foreach( $connectorMap as $connector ){
		$title	= $connector->title;
		if( '' !== ( $connector->description ?? '' ) ){
			$description	= htmlentities( $connector->description, ENT_QUOTES, 'UTF-8' );
			$title	= HtmlTag::create( 'abbr', $title, ['title' => $description, 'class' => 'initialism'] );
		}
		$rows[]	= HtmlTag::create( 'tr', [
			HtmlTag::create( 'td', $connector->importConnectorId ),
			HtmlTag::create( 'td', $title ),
			HtmlTag::create( 'td', $statusHelper->setStatus( $connector->status ) ),
			HtmlTag::create( 'td', $types[$connector->type] ),
			HtmlTag::create( 'td', $connector->mimeTypes ),
			HtmlTag::create( 'td', $connector->className ),
			HtmlTag::create( 'td', View_Helper_TimePhraser::convertStatic( $env, $connector->createdAt, TRUE, 'vor' ) ),
			HtmlTag::create( 'td', View_Helper_TimePhraser::convertStatic( $env, $connector->modifiedAt, TRUE, 'vor' ) ),
		] );
	}
	$colgroup	= HtmlElements::ColumnGroup( ['5%', '', '8%', '10%', '20%', '15%', '15%'] );
	$thead		= HtmlElements::TableHeads( ['ID', 'Titel', 'Zustand', 'Zugang', 'Connector', 'erstellt', 'verändert'] );
	$tbody		= HtmlTag::create( 'tbody', $rows );
	$table		= HtmlTag::create( 'table', [$colgroup, $thead, $tbody], ['class' => 'table'] );
	$panelConnectorList	= HtmlTag::create( 'div', [
		HtmlTag::create( 'h3', 'Importverbindungen' ),
		HtmlTag::create( 'div', [
			$table,
			HtmlTag::create( 'div', $buttonAdd, ['class' => 'buttonbar'] ),
		], ['class' => 'content-panel-inner'] ),
	], ['class' => 'content-panel'] );;
}

return $panelConnectionList.$panelConnectorList;