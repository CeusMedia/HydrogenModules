<?php
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
			$person	= UI_HTML_Tag::create( 'a', $person, array(
				'href'	=> './work/billing/person/edit/'.$reserve->personId,
			) );
		}

		$target	= UI_HTML_Tag::create( 'a', $reserve->corporation->title, array(
			'href'	=> './work/billing/corporation/edit/'.$reserve->corporationId,
		) );
		$bill	= UI_HTML_Tag::create( 'a', $reserve->bill->number.': '.$reserve->bill->title, array(
			'href'	=> './work/billing/bill/edit/'.$reserve->billId,
		) );
		$totalAmount	+= $reserve->amount;
		$date	= date( 'd.m.Y', strtotime( $reserve->dateBooked ) );
		$date	= UI_HTML_Tag::create( 'small', $date );
		$list[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $title, array( 'class' => 'autocut' ) ),
			UI_HTML_Tag::create( 'td', $bill, array( 'class' => 'autocut' ) ),
			UI_HTML_Tag::create( 'td', $target, array( 'class' => 'autocut' ) ),
			UI_HTML_Tag::create( 'td', $person, array( 'class' => 'autocut' ) ),
			UI_HTML_Tag::create( 'td', number_format( $reserve->amount, 2 ).'&nbsp;&euro;', array( 'class' => 'cell-number' ) ),
			UI_HTML_Tag::create( 'td', $date ),
		) );


	}
	$tfoot	= UI_HTML_Tag::create( 'tfoot', UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'td', 'Gesamt' ),
		UI_HTML_Tag::create( 'td', '' ),
		UI_HTML_Tag::create( 'td', '' ),
		UI_HTML_Tag::create( 'td', '' ),
		UI_HTML_Tag::create( 'td', number_format( $totalAmount, 2 ).'&nbsp;&euro;', array( 'class' => 'cell-number' ) ),
		UI_HTML_Tag::create( 'td', '' ),
	) ) );

	$colgroup	= UI_HTML_Elements::ColumnGroup( array(
		 '25%',
		 '',
		 '160',
		 '160',
		 '80',
		 '80'
	) );
	$thead	= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array(
		'Titel',
		'Rechnung',
		'Unternehmen',
		'Person',
		'Betrag',
		'Datum'
	) ) );
	$tbody	= UI_HTML_Tag::create( 'tbody', $list );
	$list	= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody.$tfoot, array( 'class' => 'table table-fixed' ) );
}

$optYear	= array(
	''	=> '- alle -',
);
$optYear[date( "Y" )]	= date( "Y" );
$optYear[date( "Y" )-1]	= date( "Y" )-1;
$optYear[date( "Y" )-2]	= date( "Y" )-2;
$optYear	= UI_HTML_Elements::Options( $optYear, $filterYear );

$optMonth	= array(
	''		=> '- alle -',
	'01'	=> 'Januar',
	'02'	=> 'Februar',
	'03'	=> 'März',
	'04'	=> 'April',
	'05'	=> 'Mai',
	'06'	=> 'Juni',
	'07'	=> 'Juli',
	'08'	=> 'August',
	'09'	=> 'September',
	'10'	=> 'Oktober',
	'11'	=> 'November',
	'12'	=> 'Dezember',
);
$optMonth	= UI_HTML_Elements::Options( $optMonth, $filterMonth );


$tabs	= View_Work_Billing_Corporation::renderTabs( $env, $corporationId, 2 );

return '<h2 class="autocut"><span class="muted">Unternehmen</span> '.$corporation->title.'</h2>
'.$tabs.'
<div class="content-panel">
	<h3>Rücklagen</h3>
	<div class="content-panel-inner">
		<form action="./work/billing/corporation/reserve/filter/'.$corporationId.'" method="post">
			<div class="row-fluid">
				<div class="span2">
					<label for="input_year">Jahr</label>
					<select name="year" id="input_year" class="span12" onchange="this.form.submit()">'.$optYear.'</select>
				</div>
				<div class="span2">
					<label for="input_month">Monat</label>
					<select name="month" id="input_month" class="span12" onchange="this.form.submit()">'.$optMonth.'</select>
				</div>
			</div>
		</form>
		'.$list.'
	</div>
</div>
';
?>
