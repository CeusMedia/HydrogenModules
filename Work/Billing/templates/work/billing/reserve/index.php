<?php

$list	= UI_HTML_Tag::create( 'em', 'Keine gefunden.', array( 'class' => 'muted' ) );

if( $reserves ){
	$list	= array();
	foreach( $reserves as $reserve ){
		$link	= UI_HTML_Tag::create( 'a', $reserve->title, array( 'href' => './work/billing/reserve/edit/'.$reserve->reserveId ) );
		$corporation	= '<em class="muted">Person per Anteil</em>';
		if( $reserve->corporationId ){
			$corporation	= $corporations[$reserve->corporationId];
			$corporation	= UI_HTML_Tag::create( 'a', $corporation->title, array(
				'href'	=> './work/billing/corporation/edit/'.$corporation->corporationId
			) );
		}
		$percent	= (float) $reserve->percent ? number_format( $reserve->percent, 2 ).'&nbsp;%' : '-';
		$amount		= (float) $reserve->amount ? number_format( $reserve->amount, 2 ).'&nbsp;&euro;' : '-';
		$list[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $link ),
			UI_HTML_Tag::create( 'td', $corporation ),
			UI_HTML_Tag::create( 'td', $reserve->personalize ? 'ja' : 'nein', array( 'class' => 'cell-feature' ) ),
			UI_HTML_Tag::create( 'td', $percent, array( 'class' => 'cell-number' ) ),
			UI_HTML_Tag::create( 'td', $amount, array( 'class' => 'cell-number' ) ),
		) );
	}
	$colgroup	= UI_HTML_Elements::ColumnGroup( array(
		'25%',
		'25%',
		'80',
		'100',
		'100',
	) );
	$thead	= UI_HTML_Tag::create( 'thead', UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'th', 'Bezeichnung' ),
		UI_HTML_Tag::create( 'th', 'Zielkonto' ),
		UI_HTML_Tag::create( 'th', '<small>personalisiert</small>', array( 'class' => 'cell-feature' ) ),
		UI_HTML_Tag::create( 'th', 'Prozent', array( 'class' => 'cell-number' ) ),
		UI_HTML_Tag::create( 'th', 'Betrag', array( 'class' => 'cell-number' ) ),
	) ) );
	$tbody	= UI_HTML_Tag::create( 'tbody', $list );
	$list	= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-fixed' ) );
}

$buttonAdd	= UI_HTML_Tag::create( 'a', 'neue Rücklage', array(
	'href'	=> './work/billing/reserve/add',
	'class'	=> 'btn btn-success',
) );

return '
<div class="row-fluid">
	<div class="span12">
		<div class="content-panel">
			<h3>Rücklagen</h3>
			<div class="content-panel-inner">
				'.$list.'
				<div class="buttonbar">
					'.$buttonAdd.'
				</div>
			</div>
		</div>
	</div>
</div>';
