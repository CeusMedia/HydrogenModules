<?php

$iconSave		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );

$buttonSave		= UI_HTML_Tag::create( 'button', $iconSave.'&nbsp;erstellen', array(
	'type'		=> 'submit',
	'name'		=> 'save',
	'class'		=> 'btn btn-primary',
	'disabled'	=> 'disabled',
) );

$optGender	= array(
	0		=> 'männlich',
	1		=> 'weiblich',
	2		=> 'transgender',
);
$optGender	= UI_HTML_Elements::Options( $optGender );

return '
<div class="content-panel" id="panel-auth-rest-register">
	<h3>Konto erstellen</h3>
	<div class="content-panel-inner">
		<form action="./auth/rest/register" method="post">
			<input type="hidden" value="'.$from.'"/>
			<div class="row-fluid">
				<div class="span12">
					<h4>Angaben zur Person</h4>
					<p>Für den Zugang benötigen Sie einen Benutzernamen. Die E-Mail-Adresse muss existieren und wird geprüft. Die Angabe der Telefonnummer ist optional.</p>
					<p>Ein initiales Passwort wird für Sie erzeugt und Ihnen per E-Mail zugestellt, nachdem die E-Mail-Adresse bestätigt wurde.</p>
				</div>
			</div>
			<br/>
			<div class="row-fluid">
				<div class="span4">
					<label for="input_firstname" class="mandatory">Vorname</label>
					<input type="text" name="firstname" id="input_firstname" class="span12" required="required" autocomplete="off" value="'.htmlentities( $data->firstname, ENT_QUOTES, 'UTF-8' ).'">
				</div>
				<div class="span5">
					<label for="input_surname" class="mandatory">Nachname</label>
					<input type="text" name="surname" id="input_surname" class="span12" required="required" autocomplete="off" value="'.htmlentities( $data->surname, ENT_QUOTES, 'UTF-8' ).'">
				</div>
				<div class="span3">
					<label for="input_gender" class="mandatory">Geschlecht</label>
					<select name="gender" id="input_gender" class="span12">'.$optGender.'</select>
				</div>
			</div>
			<hr/>
			<div class="row-fluid">
				<div class="span12">
					<h4>Zugangsdaten und Kontaktinformationen</h4>
					<p>Für den Zugang benötigen Sie einen Benutzernamen. Die E-Mail-Adresse muss existieren und wird geprüft. Die Angabe der Telefonnummer ist optional.</p>
					<p>Ein initiales Passwort wird für Sie erzeugt und Ihnen per E-Mail zugestellt, nachdem die E-Mail-Adresse bestätigt wurde.</p>
				</div>
			</div>
			<br/>
			<div class="row-fluid">
				<div class="span3">
					<label for="input_username" class="mandatory">Benutzername</label>
					<input type="text" name="username" id="input_username" class="span12" required="required" autocomplete="off" data-last-value="" value="'.htmlentities( $data->username, ENT_QUOTES, 'UTF-8' ).'">
				</div>
				<div class="span6">
					<label for="input_email" class="mandatory">E-Mail-Adresse</label>
					<input type="email" name="email" id="input_email" class="span12" required="required" autocomplete="off" data-last-value="" value="'.htmlentities( $data->email, ENT_QUOTES, 'UTF-8' ).'">
				</div>
				<div class="span3">
					<label for="input_phone" class="">Telefon</label>
					<input type="text" name="phone" id="input_phone" class="span12" value="'.htmlentities( $data->phone, ENT_QUOTES, 'UTF-8' ).'">
				</div>
			</div>
			<hr/>
			<div class="row-fluid">
				<div class="span12">
					<h4>Anschrift</h4>
					<p>Für die Nutzung von regulären Online-Bezahlsystemen benötigen wir später Ihre Anschrift.</p>
					<p>Wenn Sie Ihre genutzten Services mit kryptografischen Währungen (z.B. in BitCoin) bezahlen wollen, müssen Sie diese Informationen nicht preisgeben.</p>
				</div>
			</div>
			<br/>
			<div class="row-fluid">
				<div class="span5">
					<label for="input_country" class="mandatory">Staat / Land</label>
					<input type="text" name="country" id="input_country" class="span12 typeahead" data-provide="typeahead" autocomplete="off" required="required" value="'.htmlentities( $data->country, ENT_QUOTES, 'UTF-8' ).'">
				</div>
				<div class="span5">
					<label for="input_state" class="">Bundesland / Kanton</label>
					<input type="text" name="state" id="input_state" class="span12" value="'.htmlentities( $data->state, ENT_QUOTES, 'UTF-8' ).'">
				</div>
				<div class="span2">
					<label for="input_postcode" class="">PLZ</label>
					<input type="text" name="postcode" id="input_postcode" class="span12" value="'.htmlentities( $data->postcode, ENT_QUOTES, 'UTF-8' ).'">
				</div>
			</div>
			<div class="row-fluid">
				<div class="span6">
					<label for="input_city" class="">Stadt</label>
					<input type="text" name="city" id="input_city" class="span12" value="'.htmlentities( $data->city, ENT_QUOTES, 'UTF-8' ).'">
				</div>
				<div class="span6">
					<label for="input_street" class="">Straße und Hausnummer</label>
					<input type="text" name="street" id="input_street" class="span12" value="'.htmlentities( $data->street, ENT_QUOTES, 'UTF-8' ).'">
				</div>
			</div>
			<hr/>
			<div class="row-fluid">
				<div class="span12">
					<h4>Angaben zum Unternehmen</h4>
					<p>Wollen Sie diesen Service privat oder für Ihr Unternehmen nutzen?</p>
					<p>Bei einer geschäftlichen Nutzung geben Sie bitte hier den Namen Ihres Unternehmens.
				</div>
			</div>
			<br/>
			<div class="row-fluid">
				<div class="span12">
					<label>Art der Nutzung</label>
					<label class="checkbox noselect">
						<input type="checkbox" name="business" value="1" class="has-optionals" '.( $data->business ? 'checked="checked"' : '' ).'/> geschäftlich
					</label>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span5 optional business business-true">
					<label for="input_company" class="">Unternehmen</label>
					<input type="text" name="company" id="input_company" class="span12" value="'.htmlentities( $data->company, ENT_QUOTES, 'UTF-8' ).'">
				</div>
				<div class="span2 optional business business-true">
					<label for="input_tax_id" class="">Steuer-Nr.</label>
					<input type="text" name="tax_id" id="input_tax_id" class="span12" value="'.htmlentities( $data->tax_id, ENT_QUOTES, 'UTF-8' ).'">
				</div>
				<div class="span5 optional business business-true">
					<label>Rechnungsadresse</label>
					<label class="checkbox noselect">
						<input type="checkbox" name="billing_address" value="1" class="has-optionals" '.( $data->billing_address ? 'checked="checked"' : '' ).'/> weicht ab von Anschrift
					</label>
				</div>
			</div>
			<div class="optional business business-true">
				<div class="row-fluid optional billing_address billing_address-true">
					<div class="span5">
						<label for="input_billing_country" class="mandatory">Staat / Land</label>
						<input type="text" name="billing_country" id="input_billing_country" class="span12 typeahead" data-provide="typeahead" autocomplete="off" required="required" value="'.htmlentities( $data->billing_country, ENT_QUOTES, 'UTF-8' ).'">
					</div>
					<div class="span5">
						<label for="input_billing_state" class="">Bundesland / Kanton</label>
						<input type="text" name="billing_state" id="input_billing_state" class="span12" value="'.htmlentities( $data->billing_state, ENT_QUOTES, 'UTF-8' ).'">
					</div>
					<div class="span2">
						<label for="input_billing_postcode" class="">PLZ</label>
						<input type="text" name="billing_postcode" id="input_billing_postcode" class="span12" required="required" value="'.htmlentities( $data->billing_postcode, ENT_QUOTES, 'UTF-8' ).'">
					</div>
				</div>
				<div class="row-fluid optional billing_address billing_address-true">
					<div class="span6">
						<label for="input_billing_city" class="">Stadt</label>
						<input type="text" name="billing_city" id="input_billing_city" class="span12" required="required" value="'.htmlentities( $data->billing_city, ENT_QUOTES, 'UTF-8' ).'">
					</div>
					<div class="span6">
						<label for="input_billing_street" class="">Straße und Hausnummer</label>
						<input type="text" name="billing_street" id="input_billing_street" class="span12" required="required" value="'.htmlentities( $data->billing_street, ENT_QUOTES, 'UTF-8' ).'">
					</div>
				</div>
				<div class="row-fluid optional billing_address billing_address-true">
					<div class="span6">
						<label for="input_billing_email" class="mandatory">E-Mail-Adresse</label>
						<input type="email" name="billing_email" id="input_billing_email" class="span12" required="required" autocomplete="off" data-last-value="" value="'.htmlentities( $data->billing_email, ENT_QUOTES, 'UTF-8' ).'">
					</div>
					<div class="span3">
						<label for="input_billing_phone" class="">Telefon</label>
						<input type="text" name="billing_phone" id="input_billing_phone" class="span12" required="required" value="'.htmlentities( $data->billing_phone, ENT_QUOTES, 'UTF-8' ).'">
					</div>
				</div>
			</div>
			<div class="buttonbar">
				'.$buttonSave.'
			</div>
		</form>
	</div>
</div>
<style>
#panel-auth-rest-register input.warning {
	background-color: #fcf8e3;
	not-border-color: #fbeed5;
	color: #c09853;
	}
#panel-auth-rest-register input.success {
	background-color: #dff0d8;
	border-color: #468847;
	color: #468847;
	}
#panel-auth-rest-register input.error {
	background-color: #f2dede;
	border-color: #b94a48;
	color: #b94a48;
	}
</style>
<script>
jQuery(document).ready(function(){
	Auth.Rest.Register.countries = '.json_encode( array_values( $words['countries' ] ) ).'
	Auth.Rest.Register.init("#panel-auth-rest-register");
});
</script>';
