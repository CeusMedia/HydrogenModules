<?php

$table	= '<div class="alert alert-info">Keine.</div>';
if( $hosts ){
	$list	= array();
	foreach( $hosts as $host ){
		$list[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $host->host ),
			UI_HTML_Tag::create( 'td', $host->ip ),
		) );
	}
	$table	= UI_HTML_Tag::create( 'table', $list, array( 'class' => 'table table-fixed' ) );
}
$panelHosts	= '<div class="content-panel">
	<h3>Hosts</h3>
	<div class="content-panel-inner">
		'.$table.'
		<div class="buttonbar">
			<a href="./work/mail/sync/addHost" class="btn btn-success"><i class="fa fa-fw fa-plus"></i>&nbsp;add</a>
		</div>
	</div>
</div>';

$statuses	= array(
	-1		=> "Fehler",
	0		=> "neu",
	1		=> "in Arbeit",
	2		=> "synchronisiert",
	3		=> "abgeschlossen",
);

$table	= '<div class="alert alert-info">Keine.</div>';
if( $syncs ){
	$list	= array();
	foreach( $syncs as $sync ){
		foreach( $hosts as $host )
			if( $host->mailHostId == $sync->sourceMailHostId ){
				$sourceHost	= $host->host ? $host->host : $host->ip;
				break;
			}
		foreach( $hosts as $host )
			if( $host->mailHostId == $sync->targetMailHostId ){
				$targetHost	= $host ? $host->host : $host->ip;
				break;
			}
		$list[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $sync->sourceUsername.'<br/>'.$sourceHost  ),
			UI_HTML_Tag::create( 'td', $sync->targetUsername.'<br/>'.$targetHost  ),
			UI_HTML_Tag::create( 'td', $statuses[$sync->status] ),
		) );
	}
	$thead	= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array(
		'Quelle',
		'Ziel',
		'Zustand',
	) ) );
	$tbody	= UI_HTML_Tag::create( 'tbody', $list );
	$colgroup	= UI_HTML_Elements::ColumnGroup( '40%', '40%', '20%' );
	$table	= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-fixed' ) );
}
$panelSyncs	= '<div class="content-panel">
	<h3>Syncs</h3>
	<div class="content-panel-inner">
		<tbody>
			'.$table.'
		</tbody>
		<div class="buttonbar">
			<a href="./work/mail/sync/addSync" class="btn btn-success"><i class="fa fa-fw fa-plus"></i>&nbsp;add</a>
		</div>
	</div>
</div>';

return '<div class="row-fluid">
	<div class="span8">
		'.$panelSyncs.'
	</div>
	<div class="span4">
		'.$panelHosts.'
	</div>
</div>';
;
