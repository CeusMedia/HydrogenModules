<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconAdd	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-plus'] );

$table		= HtmlTag::create( 'div', 'No connections found.', array ('class' => 'hint' ) );

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
		$link	= HtmlTag::create( 'a', $connection->title, ['href' => 'manage/import/edit/'.$connection->importConnectionId] );
		$rows[]	= HtmlTag::create( 'tr', [
			HtmlTag::create( 'td', $connection->importConnectionId ),
			HtmlTag::create( 'td', $link ),
			HtmlTag::create( 'td', $statuses[$connection->status] ),
			HtmlTag::create( 'td', $authTypes[$connection->authType] ),
			HtmlTag::create( 'td', $connection->hostName ),
			HtmlTag::create( 'td', $connectorMap[$connection->importConnectorId]->title ),
			HtmlTag::create( 'td', View_Helper_TimePhraser::convertStatic( $env, $connection->createdAt, TRUE, 'vor' ) ),
			HtmlTag::create( 'td', View_Helper_TimePhraser::convertStatic( $env, $connection->modifiedAt, TRUE, 'vor' ) ),
		]);
	}
	$thead	= HtmlElements::TableHeads( ['ID', 'Titel', 'Zustand', 'Authentifikation', 'Server', 'Connector', 'Erstellung', 'Veränderung'] );
	$tbody	= HtmlTag::create( 'tbody', $rows );

	$table	= HtmlTag::create( 'table', [$thead, $tbody], array( 'class' => 'table' ) );
	$buttonAdd	= HtmlTag::create( 'a', $iconAdd.'&nbsp;hinzufügen', ['href' => 'manage/import/add', 'class' => 'btn btn-success'] );
}

return HtmlTag::create( 'div', [
	HtmlTag::create( 'h3', 'Connections' ),
	HtmlTag::create( 'div', [
		$table,
		HtmlTag::create( 'div', $buttonAdd, ['class' => 'buttonbar'] ),
	], ['class' => 'content-panel-inner']),
], ['class' => 'content-panel']);
