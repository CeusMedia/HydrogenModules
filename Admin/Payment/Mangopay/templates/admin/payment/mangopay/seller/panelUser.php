<?php
$optType		= array(
	'SOLETRADER'	=> 'Einzelunternehmer',
	'ORGANIZATION'	=> 'Verein / Organisation',
	'BUSINESS'		=> 'Unternehmen',
);
$optType	= UI_HTML_Elements::Options( $optType, $sellerUser->LegalPersonType );

$w	= (object) $words['panel-user'];
$form	= '<form action="./admin/payment/mangopay/seller/user" method="post">
	<div class="row-fluid">
		<div class="span6">
			<label class="mandatory" for="input_company_name">Name des Unternehmens</label>
			<input type="text" name="company[name]" id="input_company_name" class="span12" required="required" value="'.htmlentities( $sellerUser->Name, ENT_QUOTES, 'UTF-8' ).'"/>
		</div>
		<div class="span6">
			<label class="mandatory" for="input_company_type">Art des Unternehmens</label>
			<select name="company[type]" id="input_company_type" class="span12" required="required">'.$optType.'</select>
		</div>
	</div>
	<div class="row-fluid">
		<div class="span8">
			<label class="mandatory" for="input_company_email">E-Mail-Adresse</label>
			<input type="text" name="company[email]" id="input_company_email" class="span12" required="required" value="'.htmlentities( $sellerUser->Email, ENT_QUOTES, 'UTF-8' ).'"/>
		</div>
		<div class="span4">
			<label for="input_company_number">Steuer-ID</label>
			<input type="text" name="company[number]" id="input_company_number" class="span12" value="'.htmlentities( $sellerUser->CompanyNumber, ENT_QUOTES, 'UTF-8' ).'"/>
		</div>
	</div>
	<h4>Juristischer Vertreter</h4>
	<div class="row-fluid">
		<div class="span6">
			<label class="mandatory" class="mandatory" for="input_representative_firstname">Vorname</label>
			<input type="text" name="representative[firstname]" id="input_representative_firstname" class="span12" required="required" value="'.htmlentities( $sellerUser->LegalRepresentativeFirstName, ENT_QUOTES, 'UTF-8' ).'"/>
		</div>
		<div class="span6">
			<label class="mandatory" class="mandatory" for="input_representative_surname">Nachname</label>
			<input type="text" name="representative[surname]" id="input_representative_surname" class="span12" required="required" value="'.htmlentities( $sellerUser->LegalRepresentativeLastName, ENT_QUOTES, 'UTF-8' ).'"/>
		</div>
	</div>
	<div class="row-fluid">
		<div class="span12">
			<label class="mandatory" for="input_representative_email">E-Mail-Adresse</label>
			<input type="text" name="representative[email]" id="input_representative_email" class="span12" value="'.htmlentities( $sellerUser->Email, ENT_QUOTES, 'UTF-8' ).'"/>
		</div>
	</div>
	<div class="row-fluid">
		<div class="span12">
			<label class="mandatory" for="input_representative_address">'.$w->labelRepresentativeAddress.'</label>
			<input type="text" name="representative[address]" id="input_representative_address" class="span12" required="required" value="'.htmlentities( $sellerUser->LegalRepresentativeAddress->AddressLine1, ENT_QUOTES, 'UTF-8' ).'">
		</div>
	</div>
	<div class="row-fluid">
		<div class="span4">
			<label class="mandatory" for="input_representative_postcode">'.$w->labelRepresentativePostcode.'</label>
			<input type="text" name="representative[postcode]" id="input_representative_postcode" class="span12" required="required" value="'.htmlentities( $sellerUser->LegalRepresentativeAddress->PostalCode, ENT_QUOTES, 'UTF-8' ).'">
		</div>
		<div class="span8">
			<label class="mandatory" for="input_representative_city">'.$w->labelRepresentativeCity.'</label>
			<input type="text" name="representative[city]" id="input_representative_city" class="span12" required="required" value="'.htmlentities( $sellerUser->LegalRepresentativeAddress->City, ENT_QUOTES, 'UTF-8' ).'">
		</div>
	</div>
	<div class="row-fluid">
		<div class="span6">
			<label class="mandatory" class="mandatory" for="input_headquarter_country">'.$w->labelRepresentativeCountry.'</label>
			<input type="text" name="representative[country]" id="input_representative_country" class="span12" required="required" value="'.htmlentities( $sellerUser->LegalRepresentativeAddress->Country, ENT_QUOTES, 'UTF-8' ).'">
		</div>
		<div class="span6">
			<label for="input_representative_region">'.$w->labelRepresentativeRegion.'</label>
			<input type="text" name="representative[region]" id="input_representative_region" class="span12" value="'.htmlentities( $sellerUser->LegalRepresentativeAddress->Region, ENT_QUOTES, 'UTF-8' ).'">
		</div>
	</div>

	<h4>Hauptsitz</h4>
	<div class="row-fluid">
		<div class="span12">
			<label class="mandatory" for="input_headquarter_address">'.$w->labelHeadquarterAddress.'</label>
			<input type="text" name="headquarter[address]" id="input_headquarter_address" class="span12" required="required" value="'.htmlentities( $sellerUser->HeadquartersAddress->AddressLine1, ENT_QUOTES, 'UTF-8' ).'">
		</div>
	</div>
	<div class="row-fluid">
		<div class="span4">
			<label class="mandatory" for="input_headquarter_postcode">'.$w->labelHeadquarterPostcode.'</label>
			<input type="text" name="headquarter[postcode]" id="input_headquarter_postcode" class="span12" required="required" value="'.htmlentities( $sellerUser->HeadquartersAddress->PostalCode, ENT_QUOTES, 'UTF-8' ).'">
		</div>
		<div class="span8">
			<label class="mandatory" for="input_headquarter_city">'.$w->labelHeadquarterCity.'</label>
			<input type="text" name="headquarter[city]" id="input_headquarter_city" class="span12" required="required" value="'.htmlentities( $sellerUser->HeadquartersAddress->City, ENT_QUOTES, 'UTF-8' ).'">
		</div>
	</div>
	<div class="row-fluid">
		<div class="span6">
			<label class="mandatory" for="input_headquarter_country">'.$w->labelHeadquarterCountry.'</label>
			<input type="text" name="headquarter[country]" id="input_headquarter_country" class="span12" required="required" value="'.htmlentities( $sellerUser->HeadquartersAddress->Country, ENT_QUOTES, 'UTF-8' ).'">
		</div>
		<div class="span6">
			<label for="input_headquarter_region">'.$w->labelHeadquarterRegion.'</label>
			<input type="text" name="headquarter[region]" id="input_headquarter_region" class="span12" value="'.htmlentities( $sellerUser->HeadquartersAddress->Region, ENT_QUOTES, 'UTF-8' ).'">
		</div>
	</div>
<!--
	<div class="row-fluid">
		<div class="span12">
			<label for="input_"></label>
			<input type="text" name="" id="input_" class="span12" value="'.$sellerUser->Name.'"/>
		</div>
	</div>
-->
	<div class="buttonbar">
		<button type="submit" name="save" class="btn btn-primary"><i class="fa fa-fw fa-check"></i>&nbsp;'.$w->buttonSave.'</button>
	</div>
</form>';

return UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', $w->heading ),
	UI_HTML_Tag::create( 'div', array(
		$form
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );
