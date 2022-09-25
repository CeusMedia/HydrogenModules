<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconAdd		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus' ) );
$iconPerson		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-user-o' ) );
$iconCompany	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-building-o' ) );

$list	= HtmlTag::create( 'div', HtmlTag::create( 'em', 'Keine gefunden.', array( 'class' => 'muted' ) ), array( 'class' => 'alert alert-info' ) );

$frequencies	= array(
	0		=> '- keine Wiederholung -',
	1		=> 'jährlich',
	2		=> 'quartalsweise',
	3		=> 'monatlich',
	4		=> 'wöchentlich',
	5		=> 'täglich',
);

if( $expenses ){
	$list	= [];
	foreach( $expenses as $expense ){
		$title	= $expense->title;
		if( preg_match( '/\[date.Y\]/', $title ) )
			$title	= preg_replace( '/\[date.Y\]/', '<em class="muted">Jahr</em>', $title );
		if( preg_match( '/\[date.m\]/', $title ) )
			$title	= preg_replace( '/\[date.m\]/', '<em class="muted">Monat</em>', $title );
		if( preg_match( '/\[date.d\]/', $title ) )
			$title	= preg_replace( '/\[date.d\]/', '<em class="muted">Tag</em>', $title );
		$link	= HtmlTag::create( 'a', $title, array( 'href' => './work/billing/expense/edit/'.$expense->expenseId ) );
		if( $expense->fromCorporationId ){
			$corporation	= $corporations[$expense->fromCorporationId];
			$from			= HtmlTag::create( 'a', $iconCompany.'&nbsp;'.$corporation->title, array(
				'href'	=> './work/billing/corporation/edit/'.$corporation->corporationId
			) );
		}
		else if( $expense->fromPersonId ){
			$person		= $persons[$expense->fromPersonId];
			$from		= HtmlTag::create( 'a', $iconPerson.'&nbsp;'.$person->firstname.' '.$person->surname, array(
				'href'	=> './work/billing/person/edit/'.$person->personId
			) );
		}
		$to	= '-';
		if( $expense->toCorporationId ){
			$corporation	= $corporations[$expense->toCorporationId];
			$to			= HtmlTag::create( 'a', $iconCompany.'&nbsp;'.$corporation->title, array(
				'href'	=> './work/billing/corporation/edit/'.$corporation->corporationId
			) );
		}
		else if( $expense->toPersonId ){
			$person		= $persons[$expense->toPersonId];
			$to		= HtmlTag::create( 'a', $iconPerson.'&nbsp;'.$person->firstname.' '.$person->surname, array(
				'href'	=> './work/billing/person/edit/'.$person->personId
			) );
		}
		$amount		= (float) $expense->amount ? number_format( $expense->amount, 2, ',', '.' ).'&nbsp;&euro;' : '-';
		$list[]	= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', $link ),
			HtmlTag::create( 'td', $frequencies[$expense->frequency] ),
			HtmlTag::create( 'td', $from, array( 'class' => 'autocut' ) ),
			HtmlTag::create( 'td', $to, array( 'class' => 'autocut' ) ),
			HtmlTag::create( 'td', $amount, array( 'class' => 'cell-number' ) ),
		), array( 'class' => $expense->status ? 'success' : 'warning' ) );
	}
	$colgroup	= HtmlElements::ColumnGroup( array(
		'',
		'120',
		'180',
		'180',
		'100',
	) );
	$thead	= HtmlTag::create( 'thead', HtmlElements::TableHeads( array(
		'Bezeichnung',
		'Wiederholung',
		'Belasteter',
		'Begünstigter',
		'Betrag'
	) ) );
	$tbody	= HtmlTag::create( 'tbody', $list );
	$list	= HtmlTag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-fixed' ) );
}

$buttonAdd	= HtmlTag::create( 'a', $iconAdd.' neue Ausgabe', array(
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
