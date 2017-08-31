<?php
$list	= UI_HTML_Tag::create( 'div', UI_HTML_Tag::create( 'em', 'Keine gefunden.', array( 'class' => 'muted' ) ), array( 'class' => 'alert alert-info' ) );

if( $payouts ){
	$list	= array();
	foreach( $payouts as $payout ){
		$amount		= number_format( $payout->amount, 2, ',', '.' ).'&nbsp;&euro;';
		$year		= UI_HTML_Tag::create( 'small', date( 'y', strtotime( $payout->dateBooked ) ), array( 'class' => 'muted' ) );
		$date		= date( 'd.m.', strtotime( $payout->dateBooked ) ).$year;
		$buttonRemove	= UI_HTML_Tag::create( 'a', 'entfernen', array(
			'href'	=> './work/billing/person/removePayout/'.$payout->personPayoutId,
			'class'	=> 'btn btn-inverse btn-mini',
		) );
		$list[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $payout->title ),
			UI_HTML_Tag::create( 'td', $date, array( 'class' => 'cell-number' ) ),
			UI_HTML_Tag::create( 'td', $amount, array( 'class' => 'cell-number' ) ),
		) );
	}
	$colgroup	= UI_HTML_Elements::ColumnGroup( array( '', '80', '80' ) );
	$thead	= UI_HTML_Tag::create( 'thead', UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'th', 'Titel' ),
		UI_HTML_Tag::create( 'th', 'gebucht', array( 'class' => 'cell-number' ) ),
		UI_HTML_Tag::create( 'th', 'Betrag', array( 'class' => 'cell-number' ) ),
	) ) );
	$tbody	= UI_HTML_Tag::create( 'tbody', $list );
	$list	= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-fixed' ) );
}

$buttonSave	= UI_HTML_Tag::create( 'button', 'buchen', array(
	'type'	=> 'submit',
	'name'	=> 'save',
	'class'	=> 'btn btn-primary'
) );
if( $person->balance <= 0 ){
	$buttonSave	= UI_HTML_Tag::create( 'button', 'buchen', array(
		'type'	=> 'button',
		'disabled'	=> 'disabled',
		'class'	=> 'btn btn-primary'
	) );
}



$amount	= $person->balance > 0 ? floor( $person->balance * 100 ) / 100 : 0;


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

$tabs	= View_Work_Billing_Person::renderTabs( $env, $person->personId, 4 );

return '<h2 class="autocut"><span class="muted">Person</span> '.$person->firstname.' '.$person->surname.'</h2>
'.$tabs.'
<div class="row-fluid">
	<div class="span8">
		<div class="content-panel">
			<h3>Auszahlungen</h3>
			<div class="content-panel-inner">
				<form action="./work/billing/person/payout/filter/'.$person->personId.'" method="post">
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
	</div>
	<div class="span4">
		<div class="content-panel">
			<h3>Auszahlung buchen</h3>
			<div class="content-panel-inner">
				<form action="./work/billing/person/payout/add/'.$personId.'" method="post">
					<div class="row-fluid">
						<div class="span12">
							<label for="input_title">Titel</label>
							<input type="text" name="title" id="input_title" class="span12" required="required"/>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span6">
							<label for="input_dateBooked">Datum</label>
							<input type="date" name="dateBooked" id="input_dateBooked" class="span12" required="required" value="'.date( 'Y-m-d' ).'"/>
						</div>
						<div class="span6">
							<label for="input_amount">Betrag</label>
							<input type="number" step="0.01" min="0.01" max="'.$amount.'" name="amount" id="input_amount" class="span10 input-number" required="required" placeholder="0,00" value="'.$amount.'"/><span class="suffix">&euro;</span>
						</div>
					</div>
					<div class="buttonbar">
						'.$buttonSave.'
					</div>
				</form>
			</div>
		</div>
	</div>
</div>';
?>
