<?php
$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-list-alt' ) );
$iconSave		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );

$buttonCancel	= UI_HTML_Tag::create( 'a', $iconCancel.' zur Liste', array(
	'href'	=> './work/billing/person',
	'class'	=> 'btn btn',
) );

$buttonSave	= UI_HTML_Tag::create( 'button', $iconSave.' speichern', array(
	'type'	=> 'submit',
	'name'	=> 'save',
	'class'	=> 'btn btn-primary',
) );

$panelFacts		= '
<div class="content-panel">
	<h3>Person</h3>
	<div class="content-panel-inner">
		<form action="./work/billing/person/edit/'.$person->personId.'" method="post" class="form-changes-auto">
			<div class="row-fluid">
				<div class="span2">
					<label for="input_firstname">Vorname</label>
					<input type="text" name="firstname" id="input_firstname" class="span12" required="required" value="'.htmlentities( $person->firstname, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span3">
					<label for="input_surname">Nachname</label>
					<input type="text" name="surname" id="input_surname" class="span12" required="required" value="'.htmlentities( $person->surname, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span5">
					<label for="input_email">E-Mail-Adresse</label>
					<input type="text" name="email" id="input_email" class="span12" required="required" value="'.htmlentities( $person->email, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span2">
					<label for="input_balance">Kontostand</label>
					<input type="number" step="0.01" name="balance" id="input_balance" class="span10 input-number" disabled="disabled" value="'.number_format( $person->balance, 2, '.', '' ).'"/><span class="suffix">&euro;</span>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span2">
					<label for="input_income">Einnahmen</label>
					<input type="text" id="input_income" class="span10 input-number" disabled="disabled" value="'.number_format( $income, 2, ',', '.' ).'"/><span class="suffix">&euro;</span>
				</div>
				<div class="span2">
					<label for="input_outcome">Ausgaben</label>
					<input type="text" id="input_outcome" class="span10 input-number" disabled="disabled" value="'.number_format( $outcome, 2, ',', '.' ).'"/><span class="suffix">&euro;</span>
				</div>
				<div class="span3">
					<label for="input_balance">Kontostand (berechnet)</label>
					<input type="text" id="input_balance" class="span10 input-number" disabled="disabled" value="'.number_format( ( $income - $outcome ), 2, ',', '.' ).'"/><span class="suffix">&euro;</span>
				</div>
				<div class="span3">
					<label for="input_balance">Kontostand (kumuliert)</label>
					<input type="text" id="input_balance" class="span10 input-number" disabled="disabled" value="'.number_format( $person->balance, 2, ',', '.' ).'"/><span class="suffix">&euro;</span>
				</div>
				<div class="span2">
					<label for="input_balance">Abweichung</label>
					<input type="text" id="input_balance" class="span10 input-number" disabled="disabled" value="'.number_format( ( $person->balance - $income + $outcome ), 2, ',', '.' ).'"/><span class="suffix">&euro;</span>
				</div>
			</div>
			<div class="buttonbar">
				'.$buttonCancel.'
				'.$buttonSave.'
			</div>
		</form>
	</div>
</div>';

$tabs		= View_Work_Billing_Person::renderTabs( $env, $person->personId, 0 );
$heading	= '<h2 class="autocut"><span class="muted">Person</span> '.$person->firstname.' '.$person->surname.'</h2>';

return $heading.$tabs.'
<div class="row-fluid">
	<div class="span12">
		'.$panelFacts.'
	</div>
</div>';
