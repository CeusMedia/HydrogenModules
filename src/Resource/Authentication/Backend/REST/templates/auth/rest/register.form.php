<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var array<string,array<string,string>> $words */

$w				= (object) $words['register'];

$iconSave		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );

$buttonSave		= HtmlTag::create( 'button', $iconSave.'&nbsp;'.$w->buttonSave, [
	'type'		=> 'submit',
	'name'		=> 'save',
	'class'		=> 'btn btn-primary',
	'disabled'	=> 'disabled',
] );

$optGender	= $words['register-gender'];
$optGender	= HtmlElements::Options( $optGender );

return '
<div class="content-panel" id="panel-auth-rest-register">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<form action="./auth/rest/register" method="post">
			<input type="hidden" value="'.$from.'"/>
			<div class="row-fluid">
				<div class="span12">
					<h4>'.$w->headingPerson.'</h4>
					<p>'.$w->textPerson1.'</p>
					<p>'.$w->textPerson2.'</p>
				</div>
			</div>
			<br/>
			<div class="row-fluid">
				<div class="span4">
					<label for="input_firstname" class="mandatory">'.$w->labelFirstname.'</label>
					<input type="text" name="firstname" id="input_firstname" class="span12" required="required" autocomplete="off" value="'.htmlentities( $data->firstname, ENT_QUOTES, 'UTF-8' ).'">
				</div>
				<div class="span5">
					<label for="input_surname" class="mandatory">'.$w->labelSurname.'</label>
					<input type="text" name="surname" id="input_surname" class="span12" required="required" autocomplete="off" value="'.htmlentities( $data->surname, ENT_QUOTES, 'UTF-8' ).'">
				</div>
				<div class="span3">
					<label for="input_gender" class="mandatory">'.$w->labelGender.'</label>
					<select name="gender" id="input_gender" class="span12">'.$optGender.'</select>
				</div>
			</div>
			<hr/>
			<div class="row-fluid">
				<div class="span12">
					<h4>'.$w->headingAccess.'</h4>
					<p>'.$w->textPerson1.'</p>
					<p>'.$w->textPerson2.'</p>
				</div>
			</div>
			<br/>
			<div class="row-fluid">
				<div class="span3">
					<label for="input_username" class="mandatory">'.$w->labelUsername.'</label>
					<input type="text" name="username" id="input_username" class="span12" required="required" autocomplete="off" data-last-value="" value="'.htmlentities( $data->username, ENT_QUOTES, 'UTF-8' ).'">
				</div>
				<div class="span6">
					<label for="input_email" class="mandatory">'.$w->labelEmail.'</label>
					<input type="email" name="email" id="input_email" class="span12" required="required" autocomplete="off" data-last-value="" value="'.htmlentities( $data->email, ENT_QUOTES, 'UTF-8' ).'">
				</div>
				<div class="span3">
					<label for="input_phone" class="">'.$w->labelPhone.'</label>
					<input type="text" name="phone" id="input_phone" class="span12" value="'.htmlentities( $data->phone, ENT_QUOTES, 'UTF-8' ).'">
				</div>
			</div>
			<hr/>
			<div class="row-fluid">
				<div class="span12">
					<h4>'.$w->headingAddress.'</h4>
					<p>'.$w->textAddress1.'</p>
					<p>'.$w->textAddress2.'</p>
				</div>
			</div>
			<br/>
			<div class="row-fluid">
				<div class="span5">
					<label for="input_country" class="mandatory">'.$w->labelCountry.'</label>
					<input type="text" name="country" id="input_country" class="span12 typeahead" data-provide="typeahead" autocomplete="off" required="required" value="'.htmlentities( $data->country, ENT_QUOTES, 'UTF-8' ).'">
				</div>
				<div class="span5">
					<label for="input_state" class="">'.$w->labelState.'</label>
					<input type="text" name="state" id="input_state" class="span12" value="'.htmlentities( $data->state, ENT_QUOTES, 'UTF-8' ).'">
				</div>
				<div class="span2">
					<label for="input_postcode" class="">'.$w->labelPostcode.'</label>
					<input type="text" name="postcode" id="input_postcode" class="span12" value="'.htmlentities( $data->postcode, ENT_QUOTES, 'UTF-8' ).'">
				</div>
			</div>
			<div class="row-fluid">
				<div class="span6">
					<label for="input_city" class="">'.$w->labelCity.'</label>
					<input type="text" name="city" id="input_city" class="span12" value="'.htmlentities( $data->city, ENT_QUOTES, 'UTF-8' ).'">
				</div>
				<div class="span6">
					<label for="input_street" class="">'.$w->labelStreet.'</label>
					<input type="text" name="street" id="input_street" class="span12" value="'.htmlentities( $data->street, ENT_QUOTES, 'UTF-8' ).'">
				</div>
			</div>
			<hr/>
			<div class="row-fluid">
				<div class="span12">
					<h4>'.$w->headingCompany.'</h4>
					<p>'.$w->textCompany1.'</p>
					<p>'.$w->textCompany2.'</p>
				</div>
			</div>
			<br/>
			<div class="row-fluid">
				<div class="span12">
					<label>'.$w->labelUsage.'</label>
					<label class="checkbox noselect">
						<input type="checkbox" name="business" value="1" class="has-optionals" '.( $data->business ? 'checked="checked"' : '' ).'/> '.$w->labelBusiness.'
					</label>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span5 optional business business-true">
					<label for="input_company" class="">'.$w->labelCompany.'</label>
					<input type="text" name="company" id="input_company" class="span12" value="'.htmlentities( $data->company, ENT_QUOTES, 'UTF-8' ).'">
				</div>
				<div class="span2 optional business business-true">
					<label for="input_tax_id" class="">'.$w->labelTaxId.'</label>
					<input type="text" name="tax_id" id="input_tax_id" class="span12" value="'.htmlentities( $data->tax_id, ENT_QUOTES, 'UTF-8' ).'">
				</div>
				<div class="span5 optional business business-true">
					<label>'.$w->labelBillingAddress.'</label>
					<label class="checkbox noselect">
						<input type="checkbox" name="billing_address" value="1" class="has-optionals" '.( $data->billing_address ? 'checked="checked"' : '' ).'/> '.$w->labelBillingAddressDiffers.'
					</label>
				</div>
			</div>
			<div class="optional business business-true">
				<div class="row-fluid optional billing_address billing_address-true">
					<div class="span5">
						<label for="input_billing_country" class="mandatory">'.$w->labelCountry.'</label>
						<input type="text" name="billing_country" id="input_billing_country" class="span12 typeahead" data-provide="typeahead" autocomplete="off" required="required" value="'.htmlentities( $data->billing_country, ENT_QUOTES, 'UTF-8' ).'">
					</div>
					<div class="span5">
						<label for="input_billing_state" class="">'.$w->labelState.'</label>
						<input type="text" name="billing_state" id="input_billing_state" class="span12" value="'.htmlentities( $data->billing_state, ENT_QUOTES, 'UTF-8' ).'">
					</div>
					<div class="span2">
						<label for="input_billing_postcode" class="">'.$w->labelPostcode.'</label>
						<input type="text" name="billing_postcode" id="input_billing_postcode" class="span12" required="required" value="'.htmlentities( $data->billing_postcode, ENT_QUOTES, 'UTF-8' ).'">
					</div>
				</div>
				<div class="row-fluid optional billing_address billing_address-true">
					<div class="span6">
						<label for="input_billing_city" class="">'.$w->labelCity.'</label>
						<input type="text" name="billing_city" id="input_billing_city" class="span12" required="required" value="'.htmlentities( $data->billing_city, ENT_QUOTES, 'UTF-8' ).'">
					</div>
					<div class="span6">
						<label for="input_billing_street" class="">'.$w->labelStreet.'</label>
						<input type="text" name="billing_street" id="input_billing_street" class="span12" required="required" value="'.htmlentities( $data->billing_street, ENT_QUOTES, 'UTF-8' ).'">
					</div>
				</div>
				<div class="row-fluid optional billing_address billing_address-true">
					<div class="span6">
						<label for="input_billing_email" class="mandatory">'.$w->labelEmail.'</label>
						<input type="email" name="billing_email" id="input_billing_email" class="span12" required="required" autocomplete="off" data-last-value="" value="'.htmlentities( $data->billing_email, ENT_QUOTES, 'UTF-8' ).'">
					</div>
					<div class="span3">
						<label for="input_billing_phone" class="">'.$w->labelPhone.'</label>
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
	Auth.Rest.Register.countries = '.json_encode( array_values( $countries ) ).'
	Auth.Rest.Register.init("#panel-auth-rest-register");
});
</script>';
