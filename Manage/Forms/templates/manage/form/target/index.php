<?php

$table	= UI_HTML_Tag::create( 'div', 'Keine Transferziele definiert. <a href="./manage/form/target/add" class="btn btn-success btn-mini"><i class="fa fa-fw fa-plus"></i>&nbsp;hinzufügen</a>', array( 'class' => 'alert alert-info' ) );

$statuses	= [
	0	=> 'inaktiv',
	1	=> 'aktiv',
];

$helper	= new View_Helper_TimePhraser( $env );
$helper->setTemplate( 'vor %s' );
if( count( $targets ) ){
	$rows	= [];
	foreach( $targets as $target ){
		$link	= UI_HTML_Tag::create( 'a', $target->title, array( 'href' => './manage/form/target/edit/'.$target->formTransferTargetId ) );
		$rows[]	= UI_HTML_Tag::create( 'tr', [
//			UI_HTML_Tag::create( 'td', print_m($target, NULL, NULL, TRUE ) ),
			UI_HTML_Tag::create( 'td', $link ),
			UI_HTML_Tag::create( 'td', $statuses[$target->status] ),
//			UI_HTML_Tag::create( 'td', $target->className ),
			UI_HTML_Tag::create( 'td', $target->transfers ),
			UI_HTML_Tag::create( 'td', $target->usedAt ? $helper->setTimestamp( $target->usedAt )->render() : '-' ),
		] );
	}
	$thead	= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( ['Titel', 'Zustand'/*, 'Implementierung'*/, 'Transfers', 'Verwendung'] ) );
	$tbody	= UI_HTML_Tag::create( 'tbody', $rows );
	$table	= UI_HTML_Tag::create( 'table', [$thead, $tbody], array( 'class' => 'table table-striped table-fixed not-table-bordered' ) );
}

$iconAdd	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus' ) );
$buttonAdd	= UI_HTML_Tag::create( 'a', $iconAdd.'&nbsp;hinzufügen', array( 'class' => 'btn btn-success', 'href' => './manage/form/target/add' ) );

return '<div class="content-panel">
	<h3><span class="muted">Transferziele</h3>
	<div class="content-panel-inner">
		'.$table.'
		<div class="buttonbar">
			'.$buttonAdd.'
		</div>
	</div>
</div>';
