<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

/** @var Environment $env */
/** @var object $bill */
/** @var object[] $billExpenses */
/** @var object[] $billReserves */
/** @var object[] $billShares */
/** @var object[] $corporations */
/** @var object[] $persons */
/** @var object[] $reserves */

$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-list-alt'] );
$iconSave		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );
$iconAdd		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-plus'] );
$iconUndo		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-undo'] );

$list	= [];
$leftAmount	= (float) $bill->amountNetto;

if( $billExpenses ){
	foreach( $billExpenses as $expense ){
		$buttonRemove	= HtmlTag::create( 'a', 'entfernen', [
			'href'	=> './work/billing/bill/breakdown/removeExpense/'.$expense->billExpenseId,
			'class'	=> 'btn btn-inverse btn-mini',
		] );
		if( $bill->status == Model_Billing_Bill::STATUS_BOOKED )
			$buttonRemove	= '';
		$list[]	= HtmlTag::create( 'tr', [
			HtmlTag::create( 'td', '<small>Ausgabe</small>' ),
			HtmlTag::create( 'td', $expense->title ),
			HtmlTag::create( 'td', '-', ['class' => 'cell-number'] ),
			HtmlTag::create( 'td', number_format( $expense->amount, 2, ',', '.' ).'&nbsp;&euro;', ['class' => 'cell-number'] ),
			HtmlTag::create( 'td', $buttonRemove, ['class' => 'cell-actions'] ),
		] );
		$leftAmount	-= (float) $expense->amount;
	}
}
if( $billReserves ){
	foreach( $billReserves as $billReserve ){
		$buttonRemove	= HtmlTag::create( 'a', 'entfernen', [
			'href'	=> './work/billing/bill/breakdown/removeReserve/'.$billReserve->billReserveId,
			'class'	=> 'btn btn-inverse btn-mini',
		] );
		if( $billReserve->status == Model_Billing_Bill_Reserve::STATUS_BOOKED )
			$buttonRemove	= '';
		$list[]	= HtmlTag::create( 'tr', [
			HtmlTag::create( 'td', '<small>Rücklage</small>' ),
			HtmlTag::create( 'td', $billReserve->reserve->title ),
			HtmlTag::create( 'td', (float) $billReserve->percent ? number_format( $billReserve->percent, 2, ',', '.' ).'&nbsp;%' : '-', ['class' => 'cell-number'] ),
			HtmlTag::create( 'td', number_format( $billReserve->amount, 2, ',', '.' ).'&nbsp;&euro;', ['class' => 'cell-number'] ),
			HtmlTag::create( 'td', $buttonRemove, ['class' => 'cell-actions'] ),
		] );
		$leftAmount	-= (float) $billReserve->amount;
	}
}

$sharedAmount	= 0;
if( $billShares ){
	foreach( $billShares as $billShare ){
		$buttonRemove	= HtmlTag::create( 'a', 'entfernen', [
			'href'	=> './work/billing/bill/breakdown/removeShare/'.$billShare->billShareId,
			'class'	=> 'btn btn-inverse btn-mini',
		] );
		if( $billShare->status == Model_Billing_Bill_Share::STATUS_BOOKED )
			$buttonRemove	= '';

		$label	= (int) $billShare->personId > 0 ? $billShare->person->firstname.' '.$billShare->person->surname : $billShare->corporation->title;
		$list[]	= HtmlTag::create( 'tr', [
			HtmlTag::create( 'td', '<small>Anteil</small>' ),
			HtmlTag::create( 'td', $label ),
			HtmlTag::create( 'td', (float) $billShare->percent ? number_format( $billShare->percent, 2, ',', '.' ).'&nbsp;%' : '-', ['class' => 'cell-number'] ),
			HtmlTag::create( 'td', number_format( $billShare->amount, 2, ',', '.' ).'&nbsp;&euro;', ['class' => 'cell-number'] ),
			HtmlTag::create( 'td', $buttonRemove, ['class' => 'cell-actions'] ),
		] );
		$sharedAmount	+= (float) $billShare->amount;
	}
}

if( $bill->status != Model_Billing_Bill::STATUS_BOOKED ){
	$missingAmount	= (float) round( (float) $bill->amountNetto - (float) $bill->amountAssigned, 2 );
	$missingPercent	= $leftAmount > 0 ? ( $leftAmount - $sharedAmount ) / $leftAmount * 100 : 0;

	if( $missingAmount < 0 ){
		$labelPercent	= HtmlTag::create( 'strong', number_format( $missingPercent, 2, ',', '.' ).'&nbsp;%', ['class' => 'text-error'] );
		$labelMissing	= HtmlTag::create( 'strong', number_format( $missingAmount, 2, ',', '.' ).'&nbsp;&euro;', ['class' => 'text-error'] );
	}
	else if( $missingAmount > 0 ){
		$labelPercent	= HtmlTag::create( 'strong', number_format( $missingPercent, 2, ',', '.' ).'&nbsp;%', ['class' => 'text-error'] );
		$labelMissing	= HtmlTag::create( 'strong', number_format( $missingAmount, 2, ',', '.' ).'&nbsp;&euro;', ['class' => 'text-error'] );
	}
	else{
		$labelPercent	= HtmlTag::create( 'strong', number_format( $missingPercent, 2, ',', '.' ).'&nbsp;%', ['class' => 'text-success'] );
		$labelMissing	= HtmlTag::create( 'strong', number_format( $missingAmount, 2, ',', '.' ).'&nbsp;&euro;', ['class' => 'text-success'] );
	}

	$list[]	= HtmlTag::create( 'tr', [
		HtmlTag::create( 'td', '<strong>Noch zu verteilen</strong>', ['colspan' => '2'] ),
		HtmlTag::create( 'td', $labelPercent, ['class' => 'cell-number'] ),
		HtmlTag::create( 'td', $labelMissing, ['class' => 'cell-number'] ),
		HtmlTag::create( 'td', '' ),
	] );
}


$colgroup	= HtmlElements::ColumnGroup( ['80', '', '80', '100', '80'] );
$thead	= HtmlTag::create( 'thread', HtmlElements::TableHeads( ['Type', 'Bezug', 'Prozent', 'Betrag', ''] ) );
$tbody	= HtmlTag::create( 'tbody', $list );
$list	= HtmlTag::create( 'table', $colgroup.$thead.$tbody, ['class' => 'table table-fixed'] );

$tabs	= View_Work_Billing_Bill::renderTabs( $env, $bill->billId, 1 );

if( $bill->status == Model_Billing_Bill::STATUS_BOOKED ){

	$buttonUnbook		= HtmlTag::create( 'a', $iconUndo.' zurücksetzen', [
		'href'		=> './work/billing/bill/unbook/'.$bill->billId,
		'class'		=> 'btn btn-mini',
	] );
	return '<h2 class="autocut"><span class="muted">Rechnung</span> '.$bill->number.' - '.$bill->title.'</h2>
'.$tabs.'
<div class="content-panel">
	<h3>Aufteilung</h3>
	<div class="content-panel-inner">
		'.$list.'
		<div class="buttonbar">
			'.$buttonUnbook.'
		</div>
	</div>
</div>';
}

$optType	= [
	0	=> 'Person',
	1	=> 'Unternehmen',
];
$optType	= HtmlElements::Options( $optType );


$optCorporation	= [];
foreach( $corporations as $corporation )
	$optCorporation[$corporation->corporationId]	= $corporation->title;
$optCorporation	= HtmlElements::Options( $optCorporation );

$optPerson	= [];
foreach( $persons as $person )
	$optPerson[$person->personId]	= $person->firstname.' '.$person->surname;
$optPerson	= HtmlElements::Options( $optPerson );

$optReserve	= [];
foreach( $reserves as $reserve )
	$optReserve[$reserve->reserveId]	= $reserve->title;
$optReserve	= HtmlElements::Options( $optReserve );

$buttonBook		= HtmlTag::create( 'button', $iconSave.' buchen', [
	'type'		=> 'button',
	'disabled'	=> 'disabled',
	'class'		=> 'btn btn-primary',
] );
$buttonAddExpense	= HtmlTag::create( 'a', $iconAdd.' neue Ausgabe', [
	'href'			=> '#modal-add-expense',
	'class'			=> 'btn btn-success',
	'role'			=> 'button',
	'data-toggle'	=> 'modal',
] );
$buttonAddReserve	= HtmlTag::create( 'a', $iconAdd.' neue Rücklage', [
	'href'			=> '#modal-add-reserve',
	'class'			=> 'btn btn-success',
	'role'			=> 'button',
	'data-toggle'	=> 'modal',
] );
$buttonAddShare		= HtmlTag::create( 'a', $iconAdd.' neuer Anteil', [
	'href'			=> '#modal-add-share',
	'class'			=> 'btn btn-success',
	'role'			=> 'button',
	'data-toggle'	=> 'modal',
] );
if( $bill->amountNetto - $bill->amountAssigned == 0 ){
	$buttonBook		= HtmlTag::create( 'a', $iconSave.' buchen', [
		'href'	=> './work/billing/bill/breakdown/book/'.$bill->billId,
		'class'	=> 'btn btn-primary',
	] );
/*	$buttonAddExpense	= HtmlTag::create( 'button', $iconAdd.' neue Ausgabe', [
		'class'			=> 'btn btn-success',
		'disabled'		=> 'disabled',
	] );*/
	$buttonAddShare		= HtmlTag::create( 'a', $iconAdd.' neuer Anteil', [
		'class'			=> 'btn btn-success',
		'disabled'		=> 'disabled',
	] );
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
					<label for="input_amount">Nettobetrag</label>
					<input type="number" step="0.01" min="0" name="amount" id="input_amount" class="span10 input-number" required="required" placeholder="0,00"/><span class="suffix">&euro;</span>
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<button type="submit" name="save" class="btn btn-primary">'.$iconSave.' speichern</button>
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
			<button type="submit" name="save" class="btn btn-primary">'.$iconSave.' speichern</button>
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
				<div class="span5" >
					<label for="input_type">Typ</label>
					<select name="type" id="input_type" class="span12 has-optionals">'.$optType.'</select>
				</div>
				<div class="span7 optional type type-0">
					<label for="input_personId">Person</label>
					<select name="personId" id="input_personId" class="span12">'.$optPerson.'</select>
				</div>
				<div class="span7 optional type type-1">
					<label for="input_coporationId">Unternehmen</label>
					<select name="corporationId" id="input_corporationId" class="span12">'.$optCorporation.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span4">
					<label for="input_percent"><small class="muted">entweder</small> Prozent</label>
					<input type="number" step="0.01" min="0" max="100" name="percent" id="input_percent" class="span12 input-number" value="'.number_format( $missingPercent, 2, '.', '' ).'"/>
				</div>
				<div class="span4">
					<label for="input_amount"><small class="muted">oder</small> <strong>Netto</strong>betrag</label>
					<input type="number" step="0.01" min="0" name="amount" id="input_amount" class="span12 input-number" placeholder="0,00"/>
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<button type="submit" name="save" class="btn btn-primary">'.$iconSave.' speichern</button>
		</div>
	</form>
</div>';
