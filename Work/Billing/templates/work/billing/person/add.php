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

return '
<div class="row-fluid">
	<div class="span12">
		<div class="content-panel">
			<h3>Neue Person</h3>
			<div class="content-panel-inner">
				<form action="./work/billing/person/add" method="post" class="form-changes-auto">
					<div class="row-fluid">
						<div class="span2">
							<label for="input_firstname">Vorname</label>
							<input type="text" name="firstname" id="input_firstname" class="span12" required="required"/>
						</div>
						<div class="span3">
							<label for="input_surname">Nachname</label>
							<input type="text" name="surname" id="input_surname" class="span12" required="required"/>
						</div>
						<div class="span4">
							<label for="input_email">E-Mail-Adresse</label>
							<input type="text" name="email" id="input_email" class="span12" required="required"/>
						</div>
						<div class="span2">
							<label for="input_balance">Kontostand</label>
							<input type="number" step="0.01" name="balance" id="input_balance" class="span10 input-number" value="0"/><span class="suffix">&euro;</span>
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
