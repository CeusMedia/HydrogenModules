<?php
$iconAdd		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus' ) );

$helperTime		= new View_Helper_TimePhraser( $env );

$table	= UI_HTML_Tag::create( 'div', 'Keine vorhanden.', array( 'class' => 'alert alert-info' ) );
if( $providers ){
	$rows	= array();
	foreach( $providers as $provider ){
		$link	= UI_HTML_Tag::create( 'a', $provider->title, array(
			'href'	=> './admin/oauth2/edit/'.$provider->oauthProviderId,
		) );
		$rows[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $provider->oauthProviderId ),
			UI_HTML_Tag::create( 'td', $link ),
			UI_HTML_Tag::create( 'td', $provider->rank ),
			UI_HTML_Tag::create( 'td', $helperTime->convert( max( $provider->createdAt, $provider->modifiedAt ), TRUE ) ),
		) );
	}
	$colgroup	= UI_HTML_Elements::ColumnGroup( array(
		'50px',
		'',
		'6%',
		'140px',
	) );
	$thead	= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array(
		'ID',
		'Anbieter',
		'Rank',
		'geändert',
	) ) );
	$tbody	= UI_HTML_Tag::create( 'tbody', $rows );
	$table	= UI_HTML_Tag::create( 'table', array( $colgroup, $thead, $tbody ), array(
		'class'	=> 'table table-striped table-fixed'
	) );
}

$buttonAdd	= UI_HTML_Tag::create( 'a', $iconAdd.' neuer Anbieter', array(
	'href'	=> './admin/oauth2/add',
	'class' => 'btn btn-success',
) );

return UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', 'Providers' ),
	UI_HTML_Tag::create( 'div', array(
		$table,
		UI_HTML_Tag::create( 'div', array(
			$buttonAdd,
		), array( 'class' => 'buttonbar' ) ),
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );
