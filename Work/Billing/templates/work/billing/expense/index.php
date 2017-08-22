<?php

$list	= UI_HTML_Tag::create( 'div', UI_HTML_Tag::create( 'em', 'Keine gefunden.', array( 'class' => 'muted' ) ), array( 'class' => 'alert alert-info' ) );

$frequencies	= array(
	0		=> '- keine Wiederholung -',
	1		=> 'jährlich',
	2		=> 'quartalsweise',
	3		=> 'monatlich',
	4		=> 'wöchentlich',
	5		=> 'täglich',
);

if( $expenses ){
	$list	= array();
	foreach( $expenses as $expense ){
		$title	= $expense->title;
		if( preg_match( '/\[date.Y\]/', $title ) )
			$title	= preg_replace( '/\[date.Y\]/', '<em class="muted">Jahr</em>', $title );
		if( preg_match( '/\[date.m\]/', $title ) )
			$title	= preg_replace( '/\[date.m\]/', '<em class="muted">Monat</em>', $title );
		$link	= UI_HTML_Tag::create( 'a', $title, array( 'href' => './work/billing/expense/edit/'.$expense->expenseId ) );
		if( $expense->corporationId ){
			$corporation	= $corporations[$expense->corporationId];
			$relation		= UI_HTML_Tag::create( 'a', $corporation->title, array(
				'href'	=> './work/billing/corporation/edit/'.$corporation->corporationId
			) );
		}
		else if( $expense->personId ){
			$person		= $persons[$expense->personId];
			$relation		= UI_HTML_Tag::create( 'a', $person->firstname.' '.$person->surname, array(
				'href'	=> './work/billing/person/edit/'.$person->personId
			) );
		}
		$amount		= (float) $expense->amount ? number_format( $expense->amount, 2 ).'&nbsp;&euro;' : '-';
		$list[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $link ),
			UI_HTML_Tag::create( 'td', $frequencies[$expense->frequency] ),
			UI_HTML_Tag::create( 'td', $relation ),
			UI_HTML_Tag::create( 'td', $amount, array( 'class' => 'cell-number' ) ),
		) );
	}
	$colgroup	= UI_HTML_Elements::ColumnGroup( array(
		'',
		'120',
		'180',
		'100',
	) );
	$thead	= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array(
		'Bezeichnung',
		'Wiederholung',
		'Belasteter',
		'Betrag'
	) ) );
	$tbody	= UI_HTML_Tag::create( 'tbody', $list );
	$list	= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-fixed' ) );
}

$buttonAdd	= UI_HTML_Tag::create( 'a', 'neue Ausgabe', array(
	'href'	=> './work/billing/expense/add',
	'class'	=> 'btn btn-success',
) );

return '
<div class="row-fluid">
	<div class="span9">
		<div class="content-panel">
			<h3>Ausgaben</h3>
			<div class="content-panel-inner">
				'.$list.'
				<div class="buttonbar">
					'.$buttonAdd.'
				</div>
			</div>
		</div>
	</div>
</div>';
