<?php
$iconAdd		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus' ) );
$iconPerson		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-user' ) );
$iconCompany	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-building-o' ) );

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
		if( preg_match( '/\[date.d\]/', $title ) )
			$title	= preg_replace( '/\[date.d\]/', '<em class="muted">Tag</em>', $title );
		$link	= UI_HTML_Tag::create( 'a', $title, array( 'href' => './work/billing/expense/edit/'.$expense->expenseId ) );
		if( $expense->fromCorporationId ){
			$corporation	= $corporations[$expense->fromCorporationId];
			$from			= UI_HTML_Tag::create( 'a', $iconCompany.'&nbsp;'.$corporation->title, array(
				'href'	=> './work/billing/corporation/edit/'.$corporation->corporationId
			) );
		}
		else if( $expense->fromPersonId ){
			$person		= $persons[$expense->fromPersonId];
			$from		= UI_HTML_Tag::create( 'a', $iconPerson.'&nbsp;'.$person->firstname.' '.$person->surname, array(
				'href'	=> './work/billing/person/edit/'.$person->personId
			) );
		}
		$to	= '-';
		if( $expense->toCorporationId ){
			$corporation	= $corporations[$expense->toCorporationId];
			$to			= UI_HTML_Tag::create( 'a', $iconCompany.'&nbsp;'.$corporation->title, array(
				'href'	=> './work/billing/corporation/edit/'.$corporation->corporationId
			) );
		}
		else if( $expense->toPersonId ){
			$person		= $persons[$expense->toPersonId];
			$to		= UI_HTML_Tag::create( 'a', $iconPerson.'&nbsp;'.$person->firstname.' '.$person->surname, array(
				'href'	=> './work/billing/person/edit/'.$person->personId
			) );
		}
		$amount		= (float) $expense->amount ? number_format( $expense->amount, 2, ',', '.' ).'&nbsp;&euro;' : '-';
		$list[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $link ),
			UI_HTML_Tag::create( 'td', $frequencies[$expense->frequency] ),
			UI_HTML_Tag::create( 'td', $from, array( 'class' => 'autocut' ) ),
			UI_HTML_Tag::create( 'td', $to, array( 'class' => 'autocut' ) ),
			UI_HTML_Tag::create( 'td', $amount, array( 'class' => 'cell-number' ) ),
		), array( 'class' => $expense->status ? 'success' : 'warning' ) );
	}
	$colgroup	= UI_HTML_Elements::ColumnGroup( array(
		'',
		'120',
		'180',
		'180',
		'100',
	) );
	$thead	= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array(
		'Bezeichnung',
		'Wiederholung',
		'Belasteter',
		'Begünstigter',
		'Betrag'
	) ) );
	$tbody	= UI_HTML_Tag::create( 'tbody', $list );
	$list	= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-fixed' ) );
}

$buttonAdd	= UI_HTML_Tag::create( 'a', $iconAdd.' neue Ausgabe', array(
	'href'	=> './work/billing/expense/add',
	'class'	=> 'btn btn-success',
) );

return '
<div class="row-fluid">
	<div class="span12">
		<div class="content-panel">
			<h3>Regelausgaben</h3>
			<div class="content-panel-inner">
				'.$list.'
				<div class="buttonbar">
					'.$buttonAdd.'
				</div>
			</div>
		</div>
	</div>
</div>';
