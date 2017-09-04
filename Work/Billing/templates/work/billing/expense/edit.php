<?php
$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-list-alt' ) );
$iconSave		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );
$iconRemove		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-trash-o' ) );

$buttonCancel	= UI_HTML_Tag::create( 'a', $iconCancel.' zur Liste', array(
	'href'	=> './work/billing/expense',
	'class'	=> 'btn btn',
) );

$buttonSave	= UI_HTML_Tag::create( 'button', $iconSave.' speichern', array(
	'type'	=> 'submit',
	'name'	=> 'save',
	'class'	=> 'btn btn-primary',
) );

$buttonRemove	= UI_HTML_Tag::create( 'a', $iconRemove.' entfernen', array(
	'href'	=> './work/billing/expense/remove/'.$expense->expenseId,
	'class'	=> 'btn btn-danger',
) );

$optCorporation	= array(
	'0'	=> '- kein Unternehmen -',
);
foreach( $corporations as $corporation )
	$optCorporation[$corporation->corporationId]	= $corporation->title;
$optCorporation	= UI_HTML_Elements::Options( $optCorporation, $expense->corporationId );

$optPerson	= array(
	'0'	=> '- keine Person -',
);
foreach( $persons as $person )
	$optPerson[$person->personId]	= $person->firstname.' '.$person->surname;
$optPerson	= UI_HTML_Elements::Options( $optPerson, $expense->personId );

$optFrequency	= array(
	0		=> '- keine Wiederholung -',
	1		=> 'jährlich',
	2		=> 'quartalsweise',
	3		=> 'monatlich',
	4		=> 'wöchentlich',
	5		=> 'täglich',
);
$optFrequency	= UI_HTML_Elements::Options( $optFrequency, $expense->frequency );


$optDayOfMonth	= array(
	1		=> '1.',
	15		=> '15.',
	28		=> '28.',
);
$optDayOfMonth	= UI_HTML_Elements::Options( $optDayOfMonth, $expense->dayOfMonth );

return '
<div class="row-fluid">
	<div class="span9">
		<div class="content-panel">
			<h3>Ausgabe</h3>
			<div class="content-panel-inner">
				<form action="./work/billing/expense/edit/'.$expense->expenseId.'" method="post" class="form-changes-auto">
					<div class="row-fluid">
						<div class="span4">
							<label for="input_title">Bezeichnung</label>
							<input type="text" name="title" id="input_title" class="span12" required="required" value="'.htmlentities( $expense->title, ENT_QUOTES, 'UTF-8' ).'"/>
						</div>
						<div class="span4">
							<label for="input_corporationId"><small class="muted">entweder</small> Unternehmen</label>
							<select name="corporationId" id="input_corporationId" class="span12">'.$optCorporation.'</select>
						</div>
						<div class="span4">
							<label for="input_personId"><small class="muted">oder</small> Person</label>
							<select name="personId" id="input_personId" class="span12">'.$optPerson.'</select>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span3">
							<label for="input_amount">Betrag</label>
							<input type="text" name="amount" id="input_amount" class="span10 input-number" data-max-precision="2" required="required" value="'.$expense->amount.'"/><span class="suffix">&euro;</span>
						</div>
						<div class="span3">
							<label for="input_frequency">Wiederholung</label>
							<select name="frequency" id="input_frequency" class="span12">'.$optFrequency.'</select>
						</div>
						<div class="span3">
							<label for="input_dayOfMonth">Ausführungstag</label>
							<select name="dayOfMonth" id="input_dayOfMonth" class="span12">'.$optDayOfMonth.'</select>
						</div>
					</div>
					<div class="buttonbar">
						'.$buttonCancel.'
						'.$buttonSave.'
						'.$buttonRemove.'
					</div>
				</form>
			</div>
		</div>
	</div>
</div>';
