<?php
$w	= (object) $words['panel-emails'];

$form	= '<form action="./admin/payment/mangopay/client/edit" method="post">
	<div class="row-fluid">
		<div class="span4">
			<label for="input_emails_admin">'.$w->labelEmailsAdmin.'</label>
			<textarea rows="3" name="emails[admin]" id="input_emails_admin" class="span12">'.htmlentities( join( "\n", $client->AdminEmails ), ENT_QUOTES, 'UTF-8' ).'</textarea>
		</div>
		<div class="span4">
			<label for="input_emails_tech">'.$w->labelEmailsTech.'</label>
			<textarea rows="3" name="emails[tech]" id="input_emails_tech" class="span12">'.htmlentities( join( "\n", $client->TechEmails ), ENT_QUOTES, 'UTF-8' ).'</textarea>
		</div>
		<div class="span4">
			<label for="input_emails_billing">'.$w->labelEmailsBilling.'</label>
			<textarea rows="3" name="emails[billing]" id="input_emails_billing" class="span12">'.htmlentities( join( "\n", $client->BillingEmails ), ENT_QUOTES, 'UTF-8' ).'</textarea>
		</div>
	</div>
	<div class="buttonbar">
		<button type="submit" name="save" class="btn btn-primary"><i class="fa fa-fw fa-check"></i>&nbsp;'.$w->buttonSave.'</button>
	</div>
</form>';

return UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', $w->heading ),
	UI_HTML_Tag::create( 'div', array(
		$form,
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );