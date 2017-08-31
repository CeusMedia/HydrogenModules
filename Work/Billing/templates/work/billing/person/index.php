<?php

$list	= UI_HTML_Tag::create( 'em', 'Keine gefunden.', array( 'class' => 'muted' ) );

if( $persons ){
	$list	= array();
	foreach( $persons as $person ){
		$link	= UI_HTML_Tag::create( 'a', $person->firstname.' '.$person->surname, array( 'href' => './work/billing/person/edit/'.$person->personId ) );
		$list[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $link ),
			UI_HTML_Tag::create( 'td', count( $person->payouts ) ),
			UI_HTML_Tag::create( 'td', number_format( $person->balance, 2, ',', '.' ).'&nbsp;&euro;', array( 'class' => 'cell-number' ) ),
		) );
	}
	$colgroup	= UI_HTML_Elements::ColumnGroup( array(
		'',
		'120',
		'100',
	) );
	$thead	= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array(
		'Person',
		'Auszahlungen',
		'Balance'
	) ) );
	$thead	= UI_HTML_Tag::create( 'thead', UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'th', 'Person' ),
		UI_HTML_Tag::create( 'th', 'Auszahlungen' ),
		UI_HTML_Tag::create( 'th', 'Balance', array( 'class' => 'cell-number' ) ),
	) ) );
	$tbody	= UI_HTML_Tag::create( 'tbody', $list );
	$list	= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-fixed' ) );
}

$buttonAdd	= UI_HTML_Tag::create( 'a', 'neue Person', array(
	'href'	=> './work/billing/person/add',
	'class'	=> 'btn btn-success',
) );

return '
<div class="content-panel">
	<h3>Personen</h3>
	<div class="content-panel-inner">
		'.$list.'
		<div class="buttonbar">
			'.$buttonAdd.'
		</div>
	</div>
</div>';
