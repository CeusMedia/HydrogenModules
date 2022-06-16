<?php
$iconAdd	= UI_HTML_Tag::create( 'i', '', ['class' => 'fa fa-fw fa-plus'] );

$table		= UI_HTML_Tag::create( 'div', 'No connections found.', array ('class' => 'hint' ) );

//print_m( $connectorMap );

$statuses	= [
	0	=> 'deaktiviert',
	1	=> 'aktiviert',
];
$authTypes	= [
	0	=> 'keine',
	1	=> 'per Login',
	2	=> 'mit Schlüssel',
];


if( count( $connections ) > 0 ){
	$rows	= [];
	foreach( $connections as $connection ){
		$link	= UI_HTML_Tag::create( 'a', $connection->title, ['href' => 'manage/import/edit/'.$connection->importConnectionId] );
		$rows[]	= UI_HTML_Tag::create( 'tr', [
			UI_HTML_Tag::create( 'td', $connection->importConnectionId ),
			UI_HTML_Tag::create( 'td', $link ),
			UI_HTML_Tag::create( 'td', $statuses[$connection->status] ),
			UI_HTML_Tag::create( 'td', $authTypes[$connection->authType] ),
			UI_HTML_Tag::create( 'td', $connection->hostName ),
			UI_HTML_Tag::create( 'td', $connectorMap[$connection->importConnectorId]->title ),
			UI_HTML_Tag::create( 'td', View_Helper_TimePhraser::convertStatic( $env, $connection->createdAt, TRUE, 'vor' ) ),
			UI_HTML_Tag::create( 'td', View_Helper_TimePhraser::convertStatic( $env, $connection->modifiedAt, TRUE, 'vor' ) ),
		]);
	}
	$thead	= UI_HTML_Elements::TableHeads( ['ID', 'Titel', 'Zustand', 'Authentifikation', 'Server', 'Connector', 'Erstellung', 'Veränderung'] );
	$tbody	= UI_HTML_Tag::create( 'tbody', $rows );

	$table	= UI_HTML_Tag::create( 'table', [$thead, $tbody], array( 'class' => 'table' ) );
	$buttonAdd	= UI_HTML_Tag::create( 'a', $iconAdd.'&nbsp;hinzufügen', ['href' => 'manage/import/add', 'class' => 'btn btn-success'] );
}

return UI_HTML_Tag::create( 'div', [
	UI_HTML_Tag::create( 'h3', 'Connections' ),
	UI_HTML_Tag::create( 'div', [
		$table,
		UI_HTML_Tag::create( 'div', $buttonAdd, ['class' => 'buttonbar'] ),
	], ['class' => 'content-panel-inner']),
], ['class' => 'content-panel']);
