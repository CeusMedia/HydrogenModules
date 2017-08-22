<?php

	$list	= array();
	if( $billExpenses ){
		foreach( $billExpenses as $expense ){
			$buttonRemove	= UI_HTML_Tag::create( 'a', 'entfernen', array(
				'href'	=> './work/billing/bill/breakdown/removeExpense/'.$expense->billExpenseId,
				'class'	=> 'btn btn-inverse btn-mini',
			) );
			if( $bill->status == Model_Billing_Bill::STATUS_BOOKED )
				$buttonRemove	= '';
			$list[]	= UI_HTML_Tag::create( 'tr', array(
				UI_HTML_Tag::create( 'td', '<small>Ausgabe</small>' ),
				UI_HTML_Tag::create( 'td', $expense->title ),
				UI_HTML_Tag::create( 'td', '', array( 'class' => 'cell-number' ) ),
				UI_HTML_Tag::create( 'td', number_format( $expense->amount, 2 ).'&nbsp;&euro;', array( 'class' => 'cell-number' ) ),
				UI_HTML_Tag::create( 'td', $buttonRemove, array( 'class' => 'cell-actions' ) ),
			) );
		}
	}
	if( $billReserves ){
		foreach( $billReserves as $billReserve ){
			$buttonRemove	= UI_HTML_Tag::create( 'a', 'entfernen', array(
				'href'	=> './work/billing/bill/breakdown/removeReserve/'.$billReserve->billReserveId,
				'class'	=> 'btn btn-inverse btn-mini',
			) );
			if( $billReserve->status == Model_Billing_Bill_Reserve::STATUS_BOOKED )
				$buttonRemove	= '';
			$list[]	= UI_HTML_Tag::create( 'tr', array(
				UI_HTML_Tag::create( 'td', '<small>Rücklage</small>' ),
				UI_HTML_Tag::create( 'td', $billReserve->reserve->title ),
				UI_HTML_Tag::create( 'td', $billReserve->percent ? $billReserve->percent.'&nbsp;%' : '-', array( 'class' => 'cell-number' ) ),
				UI_HTML_Tag::create( 'td', number_format( $billReserve->amount, 2 ).'&nbsp;&euro;', array( 'class' => 'cell-number' ) ),
				UI_HTML_Tag::create( 'td', $buttonRemove, array( 'class' => 'cell-actions' ) ),
			) );
		}
	}

	if( $billShares ){
		foreach( $billShares as $billShare ){
			$buttonRemove	= UI_HTML_Tag::create( 'a', 'entfernen', array(
				'href'	=> './work/billing/bill/breakdown/removeShare/'.$billShare->billShareId,
				'class'	=> 'btn btn-inverse btn-mini',
			) );
			if( $billShare->status == Model_Billing_Bill_Share::STATUS_BOOKED )
				$buttonRemove	= '';
			$list[]	= UI_HTML_Tag::create( 'tr', array(
				UI_HTML_Tag::create( 'td', '<small>Anteil</small>' ),
				UI_HTML_Tag::create( 'td', $billShare->person->firstname.' '.$billShare->person->surname ),
				UI_HTML_Tag::create( 'td', (float) $billShare->percent ? $billShare->percent.'&nbsp;%' : '-', array( 'class' => 'cell-number' ) ),
				UI_HTML_Tag::create( 'td', number_format( $billShare->amount, 2 ).'&nbsp;&euro;', array( 'class' => 'cell-number' ) ),
				UI_HTML_Tag::create( 'td', $buttonRemove, array( 'class' => 'cell-actions' ) ),
			) );
		}
	}

	if( $bill->status != Model_Billing_Bill::STATUS_BOOKED ){
		$missing	= (float) round( (float) $bill->amountNetto - (float) $bill->amountAssigned, 2 );
		if( $missing < 0 )
			$missing	= UI_HTML_Tag::create( 'span', number_format( $missing, 2 ).'&nbsp;&euro;', array( 'class' => 'label label-important' ) );
		else if( $missing > 0 )
			$missing	= UI_HTML_Tag::create( 'span', number_format( $missing, 2 ).'&nbsp;&euro;', array( 'class' => 'label' ) );
		else
			$missing	= UI_HTML_Tag::create( 'span', number_format( $missing, 2 ).'&nbsp;&euro;', array( 'class' => 'label label-success' ) );

		$list[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', '<strong>Noch zu verteilen</strong>', array( 'colspan' => '3' ) ),
			UI_HTML_Tag::create( 'td', $missing, array( 'class' => 'cell-number' ) ),
			UI_HTML_Tag::create( 'td', '' ),
		) );
	}


	$colgroup	= UI_HTML_Elements::ColumnGroup( array( '80', '', '80', '100', '80' ) );
	$thead	= UI_HTML_Tag::create( 'thread', UI_HTML_Elements::TableHeads( array( 'Type', 'Bezug', 'Prozent', 'Betrag', '' ) ) );
	$tbody	= UI_HTML_Tag::create( 'tbody', $list );
	$list	= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-fixed' ) );

$tabs	= View_Work_Billing_Bill::renderTabs( $env, $bill->billId, 1 );

if( $bill->status == Model_Billing_Bill::STATUS_BOOKED ){

	return '<h2 class="autocut"><span class="muted">Rechnung</span> '.$bill->number.' - '.$bill->title.'</h2>
'.$tabs.'
<div class="content-panel">
	<h3>Aufteilung</h3>
	<div class="content-panel-inner">
		'.$list.'
	</div>
</div>';
}

$optPerson	= array();
foreach( $persons as $person )
	$optPerson[$person->personId]	= $person->firstname.' '.$person->surname;
$optPerson	= UI_HTML_Elements::Options( $optPerson );

$optReserve	= array();
foreach( $reserves as $reserve )
	$optReserve[$reserve->reserveId]	= $reserve->title;
$optReserve	= UI_HTML_Elements::Options( $optReserve );

$buttonBook		= UI_HTML_Tag::create( 'button', 'buchen', array(
	'type'		=> 'button',
	'disabled'	=> 'disabled',
	'class'		=> 'btn btn-primary',
) );
$buttonAddExpense	= UI_HTML_Tag::create( 'a', '+ neue Ausgabe', array(
	'href'			=> '#modal-add-expense',
	'class'			=> 'btn btn-success',
	'role'			=> 'button',
	'data-toggle'	=> 'modal',
) );
$buttonAddReserve	= UI_HTML_Tag::create( 'a', '+ neue Rücklage', array(
	'href'			=> '#modal-add-reserve',
	'class'			=> 'btn btn-success',
	'role'			=> 'button',
	'data-toggle'	=> 'modal',
) );
$buttonAddShare		= UI_HTML_Tag::create( 'a', '+ neuer Anteil', array(
	'href'			=> '#modal-add-share',
	'class'			=> 'btn btn-success',
	'role'			=> 'button',
	'data-toggle'	=> 'modal',
) );
if( $bill->amountNetto - $bill->amountAssigned == 0 ){
	$buttonBook		= UI_HTML_Tag::create( 'a', 'buchen', array(
		'href'	=> './work/billing/bill/breakdown/book/'.$bill->billId,
		'class'	=> 'btn btn-primary',
	) );
	$buttonAddExpense	= UI_HTML_Tag::create( 'button', '+ neue Ausgabe', array(
		'class'			=> 'btn btn-success',
		'disabled'		=> 'disabled',
	) );
	$buttonAddShare		= UI_HTML_Tag::create( 'a', '+ neuer Anteil', array(
		'class'			=> 'btn btn-success',
		'disabled'		=> 'disabled',
	) );
}

return '<h2 class="autocut"><span class="muted">Rechnung</span> '.$bill->number.' - '.$bill->title.'</h2>
'.$tabs.'
<div class="content-panel">
	<h3>Aufteilung</h3>
	<div class="content-panel-inner">
		'.$list.'
		<div class="buttonbar">
			'.$buttonAddExpense.'
			'.$buttonAddReserve.'
			'.$buttonAddShare.'
			'.$buttonBook.'
		</div>
	</div>
</div>

<div id="modal-add-expense" class="modal hide not-fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<form action="./work/billing/bill/breakdown/addExpense/'.$bill->billId.'" method="post">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			<h3 id="myModalLabel">Ausgabe hinzufügen</h3>
		</div>
		<div class="modal-body">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_title">Titel</label>
					<input type="text" name="title" id="input_title" class="span11" required="required"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span3">
					<label for="input_amount">Netto-Betrag</label>
					<input type="text" name="amount" id="input_amount" class="span10 input-number" data-min-value="0" data-max-precision="2" required="required" placeholder="0.00"/><span class="suffix">&euro;</span>
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<button type="submit" name="save" class="btn btn-primary">speichern</button>
		</div>
	</form>
</div>

<div id="modal-add-reserve" class="modal hide not-fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<form action="./work/billing/bill/breakdown/addReserve/'.$bill->billId.'" method="post">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			<h3 id="myModalLabel">Rücklage hinzufügen</h3>
		</div>
		<div class="modal-body">
			<div class="row-fluid">
				<div class="span6">
					<label for="input_reserveId">Rücklage</label>
					<select name="reserveId" class="span12">'.$optReserve.'</select>
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<button type="submit" name="save" class="btn btn-primary">speichern</button>
		</div>
	</form>
</div>

<div id="modal-add-share" class="modal hide not-fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<form action="./work/billing/bill/breakdown/addShare/'.$bill->billId.'" method="post">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			<h3 id="myModalLabel">Anteil hinzufügen</h3>
		</div>
		<div class="modal-body">
			<div class="row-fluid">
				<div class="span4">
					<label for="input_personId">Person</label>
					<select name="personId" id="input_personId" class="span12">'.$optPerson.'</select>
				</div>
				<div class="span4">
					<label for="input_percent"><small class="muted">entweder</small> Prozent</label>
					<input type="text" name="percent" id="input_percent" class="span12 input-number" data-min-value="1" data-max-value="100" data-max-precision="2" placeholder="0.00"/>
				</div>
				<div class="span4">
					<label for="input_amount"><small class="muted">oder</small> Betrag</label>
					<input type="text" name="amount" id="input_amount" class="span12 input-number" data-max-precision="2" placeholder="0.00"/>
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<button type="submit" name="save" class="btn btn-primary">speichern</button>
		</div>
	</form>
</div>
';


?>
