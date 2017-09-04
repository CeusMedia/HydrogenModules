<?php

$iconPerson		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-user' ) );
$iconCompany	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-building-o' ) );

$list	= UI_HTML_Tag::create( 'div', UI_HTML_Tag::create( 'em', 'Keine gefunden.', array( 'class' => 'muted' ) ), array( 'class' => 'alert alert-info' ) );

if( $reserves ){
	$list			= array();
	$totalAmount	= 0;
	foreach( $reserves as $reserve ){
		$title	= UI_HTML_Tag::create( 'a', $reserve->reserve->title, array(
			'href'	=> './work/billing/reserve/edit/'.$reserve->reserve->reserveId
		) );
		$person	= '-';
		if( $reserve->personId ){
			$person	= $reserve->person->firstname.' '.$reserve->person->surname;
			$person	= UI_HTML_Tag::create( 'a', $iconPerson.'&nbsp;'.$person, array(
				'href'	=> './work/billing/person/edit/'.$reserve->personId,
			) );
		}

		$target	= UI_HTML_Tag::create( 'a', $iconCompany.'&nbsp;'.$reserve->corporation->title, array(
			'href'	=> './work/billing/corporation/edit/'.$reserve->corporationId,
		) );
		$bill	= UI_HTML_Tag::create( 'a', $reserve->bill->number.': '.$reserve->bill->title, array(
			'href'	=> './work/billing/bill/edit/'.$reserve->billId,
		) );
		$totalAmount	+= $reserve->amount;

		$amount		= number_format( $reserve->amount, 2, ',', '.' ).'&nbsp;&euro;';
		$year		= UI_HTML_Tag::create( 'small', date( 'y', strtotime( $reserve->dateBooked ) ), array( 'class' => 'muted' ) );
		$date		= date( 'd.m.', strtotime( $reserve->dateBooked ) ).$year;
		$list[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $title, array( 'class' => 'autocut' ) ),
			UI_HTML_Tag::create( 'td', $bill, array( 'class' => 'autocut' ) ),
			UI_HTML_Tag::create( 'td', $target, array( 'class' => 'autocut' ) ),
			UI_HTML_Tag::create( 'td', $person, array( 'class' => 'autocut' ) ),
			UI_HTML_Tag::create( 'td', $date, array( 'class' => 'cell-number' ) ),
			UI_HTML_Tag::create( 'td', $amount, array( 'class' => 'cell-number' ) ),
		) );


	}
	$tfoot	= UI_HTML_Tag::create( 'tfoot', UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'td', 'Gesamt' ),
		UI_HTML_Tag::create( 'td', '' ),
		UI_HTML_Tag::create( 'td', '' ),
		UI_HTML_Tag::create( 'td', '' ),
		UI_HTML_Tag::create( 'td', '' ),
		UI_HTML_Tag::create( 'td', number_format( $totalAmount, 2, ',', '.' ).'&nbsp;&euro;', array( 'class' => 'cell-number' ) ),
	) ) );

	$colgroup	= UI_HTML_Elements::ColumnGroup( array(
		 '25%',
		 '',
		 '160',
		 '160',
		 '80',
		 '80'
	) );
	$thead	= UI_HTML_Tag::create( 'thead', UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'th', 'Titel' ),
		UI_HTML_Tag::create( 'th', 'Rechnung' ),
		UI_HTML_Tag::create( 'th', 'Unternehmen' ),
		UI_HTML_Tag::create( 'th', 'Person' ),
		UI_HTML_Tag::create( 'th', 'Datum', array( 'class' => 'cell-number' ) ),
		UI_HTML_Tag::create( 'th', 'Betrag', array( 'class' => 'cell-number' ) ),
	) ) );
	$tbody	= UI_HTML_Tag::create( 'tbody', $list );
	$list	= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody.$tfoot, array( 'class' => 'table table-fixed' ) );
}

$filter	= new View_Work_Billing_Helper_Filter( $this->env );
$filter->setFilters( array( 'year', 'month' ) );
$filter->setSessionPrefix( $filterSessionPrefix );
$filter->setUrl( './work/billing/corporation/reserve/filter/'.$corporation->corporationId );

$tabs	= View_Work_Billing_Corporation::renderTabs( $env, $corporationId, 2 );

return '<h2 class="autocut"><span class="muted">Unternehmen</span> '.$corporation->title.'</h2>
'.$tabs.'
<div class="content-panel">
	<h3>Rücklagen</h3>
	<div class="content-panel-inner">
		'.$filter->render().'
		'.$list.'
	</div>
</div>
';
?>
