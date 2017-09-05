<?php
$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-list-alt' ) );
$iconSave		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );

$buttonCancel	= UI_HTML_Tag::create( 'a', $iconCancel.' zur Liste', array(
	'href'	=> './work/billing/expense',
	'class'	=> 'btn btn',
) );

$buttonSave	= UI_HTML_Tag::create( 'button', $iconSave.' speichern', array(
	'type'	=> 'submit',
	'name'	=> 'save',
	'class'	=> 'btn btn-primary',
) );

$optStatus	= array(
	0		=> 'deaktiviert',
	1		=> 'aktiv',
);
$optStatus	= UI_HTML_Elements::Options( $optStatus, 1 );

$optCorporation	= array(
//	'0'	=> '- kein Unternehmen -',
);
foreach( $corporations as $corporation )
	$optCorporation[$corporation->corporationId]	= $corporation->title;
$optCorporation	= UI_HTML_Elements::Options( $optCorporation );

$optPerson	= array(
//	'0'	=> '- keine Person -',
);
foreach( $persons as $person )
	$optPerson[$person->personId]	= $person->firstname.' '.$person->surname;
$optPerson	= UI_HTML_Elements::Options( $optPerson );

$optFrequency	= array(
	1		=> 'jährlich',
	2		=> 'quartalsweise',
	3		=> 'monatlich',
	4		=> 'wöchentlich',
	5		=> 'täglich',
);
$optFrequency	= UI_HTML_Elements::Options( $optFrequency );

$optType	= array(
	1		=> 'Person',
	2		=> 'Unternehmen',
);
$optFromType	= UI_HTML_Elements::Options( $optType, 1 );
$optType	= array(
	0		=> '- keiner / extern -',
	1		=> 'Person',
	2		=> 'Unternehmen',
);
$optToType	= UI_HTML_Elements::Options( $optType, 0 );

return '
<div class="row-fluid">
	<div class="span12">
		<div class="content-panel">
			<h3>Neue Regelausgabe</h3>
			<div class="content-panel-inner">
				<form action="./work/billing/expense/add" method="post" class="form-changes-auto">
					<div class="row-fluid">
						<div class="span5">
							<label for="input_title">Bezeichnung</label>
							<input type="text" name="title" id="input_title" class="span12" required="required"/>
						</div>
						<div class="span3">
							<label for="input_frequency">Wiederholung</label>
							<select name="frequency" id="input_frequency" class="span12">'.$optFrequency.'</select>
						</div>
						<div class="span2">
							<label for="input_amount">Betrag</label>
							<input type="number" min="1"  name="amount" id="input_amount" class="span10 input-number" required="required"/><span class="suffix">&euro;</span>
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
									<select name="fromCorporationId" id="input_fromCorporationId" class="span12">'.$optCorporation.'</select>
								</div>
								<div class="span7 optional fromType fromType-1">
									<label for="input_fromPersonId">Person</label>
									<select name="fromPersonId" id="input_fromPersonId" class="span12">'.$optPerson.'</select>
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
									<select name="toCorporationId" id="input_toCorporationId" class="span12">'.$optCorporation.'</select>
								</div>
								<div class="span7 optional toType toType-1">
									<label for="input_toPersonId">Person</label>
									<select name="toPersonId" id="input_toPersonId" class="span12">'.$optPerson.'</select>
								</div>
							</div>
						</div>
					</div>
					<div class="buttonbar">
						'.$buttonCancel.'
						'.$buttonSave.'
					</div>
				</form>
			</div>
		</div>
	</div>
</div>';
