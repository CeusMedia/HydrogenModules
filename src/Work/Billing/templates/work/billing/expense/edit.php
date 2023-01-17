<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
 
$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-list-alt'] );
$iconSave		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );
$iconRemove		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-trash-o'] );

$buttonCancel	= HtmlTag::create( 'a', $iconCancel.' zur Liste', [
	'href'	=> './work/billing/expense',
	'class'	=> 'btn btn',
] );

$buttonSave	= HtmlTag::create( 'button', $iconSave.' speichern', [
	'type'	=> 'submit',
	'name'	=> 'save',
	'class'	=> 'btn btn-primary',
] );

$buttonRemove	= HtmlTag::create( 'a', $iconRemove.' entfernen', [
	'href'	=> './work/billing/expense/remove/'.$expense->expenseId,
	'class'	=> 'btn btn-danger',
] );

$optStatus	= [
	0		=> 'deaktiviert',
	1		=> 'aktiv',
];
$optStatus	= HtmlElements::Options( $optStatus, $expense->status );

$optCorporation	= [
//	'0'	=> '- kein Unternehmen -',
];
foreach( $corporations as $corporation )
	$optCorporation[$corporation->corporationId]	= $corporation->title;
$optFromCorporation	= HtmlElements::Options( $optCorporation, $expense->fromCorporationId );
$optToCorporation	= HtmlElements::Options( $optCorporation, $expense->toCorporationId );

$optPerson	= [
//	'0'	=> '- keine Person -',
];
foreach( $persons as $person )
	$optPerson[$person->personId]	= $person->firstname.' '.$person->surname;
$optFromPerson	= HtmlElements::Options( $optPerson, $expense->fromPersonId );
$optToPerson	= HtmlElements::Options( $optPerson, $expense->toPersonId );

$optFrequency	= [
	1		=> 'jährlich',
	2		=> 'quartalsweise',
	3		=> 'monatlich',
	4		=> 'wöchentlich',
	5		=> 'täglich',
];
$optFrequency	= HtmlElements::Options( $optFrequency, $expense->frequency );


$optType	= [
	1		=> 'Person',
	2		=> 'Unternehmen',
];
$optFromType	= HtmlElements::Options( $optType, $expense->fromCorporationId ? 2 : 1 );
$optType	= [
	0		=> '- keiner / extern -',
	1		=> 'Person',
	2		=> 'Unternehmen',
];
$toType		= $expense->toCorporationId ? 2 : ( $expense->toPersonId ? 1 : 0 );
$optToType	= HtmlElements::Options( $optType, $toType );

return '
<div class="row-fluid">
	<div class="span12">
		<div class="content-panel">
			<h3>Regelausgabe</h3>
			<div class="content-panel-inner">
				<form action="./work/billing/expense/edit/'.$expense->expenseId.'" method="post" class="form-changes-auto">
					<div class="row-fluid">
						<div class="span5">
							<label for="input_title">Bezeichnung</label>
							<input type="text" name="title" id="input_title" class="span12" required="required" value="'.htmlentities( $expense->title, ENT_QUOTES, 'UTF-8' ).'"/>
						</div>
						<div class="span3">
							<label for="input_frequency">Wiederholung</label>
							<select name="frequency" id="input_frequency" class="span12">'.$optFrequency.'</select>
						</div>
						<div class="span2">
							<label for="input_amount">Betrag</label>
							<input type="number" min="1" step="0.01" name="amount" id="input_amount" class="span10 input-number" required="required" value="'.$expense->amount.'"/><span class="suffix">&euro;</span>
						</div>
						<div class="span2">
							<label for="input_status">Zustand</label>
							<select name="status" id="input_status" class="span12"/>'.$optStatus.'</select>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span5">
							<div class="row-fluid">
								<div class="span5">
									<label for="input_fromType">Belasteter</label>
									<select name="fromType" id="input_fromType" class="span12 has-optionals">'.$optFromType.'</select>
								</div>
								<div class="span7 optional fromType fromType-2">
									<label for="input_fromCorporationId">Unternehmen</label>
									<select name="fromCorporationId" id="input_fromCorporationId" class="span12">'.$optFromCorporation.'</select>
								</div>
								<div class="span7 optional fromType fromType-1">
									<label for="input_fromPersonId">Person</label>
									<select name="fromPersonId" id="input_fromPersonId" class="span12">'.$optFromPerson.'</select>
								</div>
							</div>
						</div>
						<div class="span7">
							<div class="row-fluid">
								<div class="span5">
									<label for="input_toType">Begünstigter <small class="muted">(optional)</small></label>
									<select name="toType" id="input_toType" class="span12 has-optionals">'.$optToType.'</select>
								</div>
								<div class="span7 optional toType toType-2">
									<label for="input_toCorporationId">Unternehmen</label>
									<select name="toCorporationId" id="input_toCorporationId" class="span12">'.$optToCorporation.'</select>
								</div>
								<div class="span7 optional toType toType-1">
									<label for="input_toPersonId">Person</label>
									<select name="toPersonId" id="input_toPersonId" class="span12">'.$optToPerson.'</select>
								</div>
							</div>
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
