<?php
//$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-list-alt' ) );
$iconSave		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );

$list	= UI_HTML_Tag::create( 'div', UI_HTML_Tag::create( 'em', 'Keine gefunden.', array( 'class' => 'muted' ) ), array( 'class' => 'alert alert-info' ) );

if( $expenses ){
	$list	= array();
	foreach( $expenses as $expense ){
		$amount		= number_format( $expense->amount, 2, ',', '.' ).'&nbsp;&euro;';
		$year		= UI_HTML_Tag::create( 'small', date( 'y', strtotime( $expense->dateBooked ) ), array( 'class' => 'muted' ) );
		$date		= date( 'd.m.', strtotime( $expense->dateBooked ) ).$year;
		$list[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $expense->title ),
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

$buttonSave	= UI_HTML_Tag::create( 'button', $iconSave.' buchen', array(
	'type'	=> 'submit',
	'name'	=> 'save',
	'class'	=> 'btn btn-primary'
) );

$tabs	= View_Work_Billing_Person::renderTabs( $env, $person->personId, 2 );

$filter	= new View_Work_Billing_Helper_Filter( $this->env );
$filter->setFilters( array( 'year', 'month' ) );
$filter->setSessionPrefix( $filterSessionPrefix );
$filter->setUrl( './work/billing/person/expense/filter/'.$person->personId );

return '<h2 class="autocut"><span class="muted">Person</span> '.$person->firstname.' '.$person->surname.'</h2>
'.$tabs.'
<div class="row-fluid">
	<div class="span8">
		<div class="content-panel">
			<h3>Ausgaben</h3>
			<div class="content-panel-inner">
				'.$filter->render().'
				'.$list.'
			</div>
		</div>
	</div>
	<div class="span4">
		<div class="content-panel">
			<h3>Ausgabe buchen</h3>
			<div class="content-panel-inner">
				<form action="./work/billing/person/expense/add/'.$person->personId.'" method="post">
					<div class="row-fluid">
						<div class="span12">
							<label for="input_title">Bezeichnung</label>
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
							<input type="number" step="0.01" min="0.01" name="amount" id="input_amount" class="span10 input-number" required="required"/><span class="suffix">&euro;</span>
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
