<?php

$list	= UI_HTML_Tag::create( 'div', UI_HTML_Tag::create( 'em', 'Keine gefunden.', array( 'class' => 'muted' ) ), array( 'class' => 'alert alert-info' ) );

if( $corporations ){
	$list	= array();
	foreach( $corporations as $corporation ){
		$link	= UI_HTML_Tag::create( 'a', $corporation->title, array( 'href' => './work/billing/corporation/edit/'.$corporation->corporationId ) );
		$list[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $link ),
			UI_HTML_Tag::create( 'td', number_format( $corporation->balance, 2, ',', '.' ).'&nbsp;&euro;', array( 'class' => 'cell-number' ) ),
		) );
	}
	$colgroup	= UI_HTML_Elements::ColumnGroup( array(
		'',
		'100',
	) );
	$thead	= UI_HTML_Tag::create( 'thead', UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'th', 'Bezeichnung' ),
		UI_HTML_Tag::create( 'th', 'Balance', array( 'class' => 'cell-number' ) )
	) ) );
	$tbody	= UI_HTML_Tag::create( 'tbody', $list );
	$list	= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-fixed' ) );
}

$buttonAdd	= UI_HTML_Tag::create( 'a', 'neues Unternehmen', array(
	'href'	=> './work/billing/corporation/add',
	'class'	=> 'btn btn-success',
) );

return '
<div class="content-panel">
	<h3>Unternehmen</h3>
	<div class="content-panel-inner">
		'.$list.'
		<div class="buttonbar">
			'.$buttonAdd.'
		</div>
	</div>
</div>';
