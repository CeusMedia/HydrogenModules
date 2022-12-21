<?php

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;
use CeusMedia\HydrogenFramework\View;

/** @var Web $env */
/** @var View $view */
/** @var array<array<string,string>> $words */
/** @var object $sellerUser */

$words['legaluser-types']	= array(
	'SOLETRADER'	=> 'Einzelunternehmer',
	'ORGANIZATION'	=> 'Verein / Organisation',
	'BUSINESS'		=> 'Unternehmen',
);
$optType	= HtmlElements::Options( $words['legaluser-types'], $sellerUser->LegalPersonType );

$w	= (object) $words['panel-user'];

if( !$sellerUser->Id ){
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
				<input type="text" name="representative[address]" id="input_representative_address" class="span12" required="required" value="'.htmlentities( $sellerUser->LegalRepresentativeAddress->AddressLine1, ENT_QUOTES, 'UTF-8' ).'"/>
			</div>
		</div>
		<div class="row-fluid">
			<div class="span4">
				<label class="mandatory" for="input_representative_postcode">'.$w->labelRepresentativePostcode.'</label>
				<input type="text" name="representative[postcode]" id="input_representative_postcode" class="span12" required="required" value="'.htmlentities( $sellerUser->LegalRepresentativeAddress->PostalCode, ENT_QUOTES, 'UTF-8' ).'"/>
			</div>
			<div class="span8">
				<label class="mandatory" for="input_representative_city">'.$w->labelRepresentativeCity.'</label>
				<input type="text" name="representative[city]" id="input_representative_city" class="span12" required="required" value="'.htmlentities( $sellerUser->LegalRepresentativeAddress->City, ENT_QUOTES, 'UTF-8' ).'"/>
			</div>
		</div>
		<div class="row-fluid">
			<div class="span6">
				<label class="mandatory" class="mandatory" for="input_headquarter_country">'.$w->labelRepresentativeCountry.'</label>
				<input type="text" name="representative[country]" id="input_representative_country" class="span12" required="required" value="'.htmlentities( $sellerUser->LegalRepresentativeAddress->Country, ENT_QUOTES, 'UTF-8' ).'"/>
			</div>
			<div class="span6">
				<label for="input_representative_region">'.$w->labelRepresentativeRegion.'</label>
				<input type="text" name="representative[region]" id="input_representative_region" class="span12" value="'.htmlentities( $sellerUser->LegalRepresentativeAddress->Region, ENT_QUOTES, 'UTF-8' ).'"/>
			</div>
		</div>

		<h4>Hauptsitz</h4>
		<div class="row-fluid">
			<div class="span12">
				<label class="mandatory" for="input_headquarter_address">'.$w->labelHeadquarterAddress.'</label>
				<input type="text" name="headquarter[address]" id="input_headquarter_address" class="span12" required="required" value="'.htmlentities( $sellerUser->HeadquartersAddress->AddressLine1, ENT_QUOTES, 'UTF-8' ).'"/>
			</div>
		</div>
		<div class="row-fluid">
			<div class="span4">
				<label class="mandatory" for="input_headquarter_postcode">'.$w->labelHeadquarterPostcode.'</label>
				<input type="text" name="headquarter[postcode]" id="input_headquarter_postcode" class="span12" required="required" value="'.htmlentities( $sellerUser->HeadquartersAddress->PostalCode, ENT_QUOTES, 'UTF-8' ).'"/>
			</div>
			<div class="span8">
				<label class="mandatory" for="input_headquarter_city">'.$w->labelHeadquarterCity.'</label>
				<input type="text" name="headquarter[city]" id="input_headquarter_city" class="span12" required="required" value="'.htmlentities( $sellerUser->HeadquartersAddress->City, ENT_QUOTES, 'UTF-8' ).'"/>
			</div>
		</div>
		<div class="row-fluid">
			<div class="span6">
				<label class="mandatory" for="input_headquarter_country">'.$w->labelHeadquarterCountry.'</label>
				<input type="text" name="headquarter[country]" id="input_headquarter_country" class="span12" required="required" value="'.htmlentities( $sellerUser->HeadquartersAddress->Country, ENT_QUOTES, 'UTF-8' ).'"/>
			</div>
			<div class="span6">
				<label for="input_headquarter_region">'.$w->labelHeadquarterRegion.'</label>
				<input type="text" name="headquarter[region]" id="input_headquarter_region" class="span12" value="'.htmlentities( $sellerUser->HeadquartersAddress->Region, ENT_QUOTES, 'UTF-8' ).'"/>
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
	return HtmlTag::create( 'div', array(
		HtmlTag::create( 'h3', $w->heading ),
		HtmlTag::create( 'div', array(
			$form
		), ['class' => 'content-panel-inner'] ),
	), ['class' => 'content-panel'] );
}
else {

	$iconEdit	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-pencil'] );


	$body		= '<div class="row-fluid">
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
	</div>';

	$modal		= new View_Helper_Bootstrap_Modal( $env );
	$modal->setHeading( 'Daten des Unternehmens ändern' );
	$modal->setBody( $body );
	$modal->setFormAction( './admin/payment/mangopay/seller/user' );
	$modal->setId( 'modal-admin-payment-mangopay-seller-user-edit' );
//	$modal->setButtonLabelCancel( $iconCancel.'&nbsp;'.$modalWords->buttonCancel );
//	$modal->setButtonLabelSubmit( $iconSave.'&nbsp;'.$modalWords->buttonSubmit );
	$trigger	= new View_Helper_Bootstrap_Modal_Trigger( $env );
	$trigger->setModalId( 'modal-admin-payment-mangopay-seller-user-edit' );
	$trigger->setLabel( $iconEdit.'&nbsp;ändern' );
	$trigger->setAttributes( ['class' => 'btn btn-mini'] );

	$content	= '
		<dl class="dl-horizontal">
			<dt>Name des Unternehmens</dt>
			<dd>'.htmlentities( $sellerUser->Name, ENT_QUOTES, 'UTF-8' ).'</dd>
			<dt>Art des Unternehmens</dt>
			<dd>'.$words['legaluser-types'][$sellerUser->LegalPersonType].'</dd>
			<dt>E-Mail-Adresse</dt>
			<dd>'.htmlentities( $sellerUser->Email, ENT_QUOTES, 'UTF-8' ).'</dd>
			<dt>Steuer-ID</dt>
			<dd>'.htmlentities( $sellerUser->CompanyNumber, ENT_QUOTES, 'UTF-8' ).'</dd>
		</dl>
		'.$trigger;

	$panelCompany	= HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', 'Unternehmen' ),
		HtmlTag::create( 'div', array(
			$content,
			$modal
		), ['class' => 'content-panel-inner'] ),
	), ['class' => 'content-panel'] );


	$body	= '
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
				<input type="text" name="representative[address]" id="input_representative_address" class="span12" required="required" value="'.htmlentities( $sellerUser->LegalRepresentativeAddress->AddressLine1, ENT_QUOTES, 'UTF-8' ).'"/>
			</div>
		</div>
		<div class="row-fluid">
			<div class="span4">
				<label class="mandatory" for="input_representative_postcode">'.$w->labelRepresentativePostcode.'</label>
				<input type="text" name="representative[postcode]" id="input_representative_postcode" class="span12" required="required" value="'.htmlentities( $sellerUser->LegalRepresentativeAddress->PostalCode, ENT_QUOTES, 'UTF-8' ).'"/>
			</div>
			<div class="span8">
				<label class="mandatory" for="input_representative_city">'.$w->labelRepresentativeCity.'</label>
				<input type="text" name="representative[city]" id="input_representative_city" class="span12" required="required" value="'.htmlentities( $sellerUser->LegalRepresentativeAddress->City, ENT_QUOTES, 'UTF-8' ).'"/>
			</div>
		</div>
		<div class="row-fluid">
			<div class="span6">
				<label class="mandatory" class="mandatory" for="input_headquarter_country">'.$w->labelRepresentativeCountry.'</label>
				<input type="text" name="representative[country]" id="input_representative_country" class="span12" required="required" value="'.htmlentities( $sellerUser->LegalRepresentativeAddress->Country, ENT_QUOTES, 'UTF-8' ).'"/>
			</div>
			<div class="span6">
				<label for="input_representative_region">'.$w->labelRepresentativeRegion.'</label>
				<input type="text" name="representative[region]" id="input_representative_region" class="span12" value="'.htmlentities( $sellerUser->LegalRepresentativeAddress->Region, ENT_QUOTES, 'UTF-8' ).'"/>
			</div>
		</div>';

	$modal		= new View_Helper_Bootstrap_Modal( $env );
	$modal->setHeading( 'Juristischen Vertreter ändern' );
	$modal->setBody( $body );
	$modal->setFormAction( './admin/payment/mangopay/seller/user' );
	$modal->setId( 'modal-admin-payment-mangopay-seller-person-edit' );
//	$modal->setButtonLabelCancel( $iconCancel.'&nbsp;'.$modalWords->buttonCancel );
//	$modal->setButtonLabelSubmit( $iconSave.'&nbsp;'.$modalWords->buttonSubmit );
	$trigger	= new View_Helper_Bootstrap_Modal_Trigger( $env );
	$trigger->setModalId( 'modal-admin-payment-mangopay-seller-person-edit' );
	$trigger->setLabel( $iconEdit.'&nbsp;ändern' );
	$trigger->setAttributes( ['class' => 'btn btn-mini'] );

	$content	= '
		<dl class="dl-horizontal">
			<dt>Vorname und Nachname</dt>
			<dd>'.htmlentities( $sellerUser->LegalRepresentativeFirstName, ENT_QUOTES, 'UTF-8' ).' '.htmlentities( $sellerUser->LegalRepresentativeLastName, ENT_QUOTES, 'UTF-8' ).'</dd>
			<dt>E-Mail-Adresse</dt>
			<dd>'.htmlentities( $sellerUser->Email, ENT_QUOTES, 'UTF-8' ).'</dd>
			<dt>'.$w->labelRepresentativeAddress.'</dt>
			<dd>'.htmlentities( $sellerUser->LegalRepresentativeAddress->AddressLine1, ENT_QUOTES, 'UTF-8' ).'</dd>
			<dt>'.$w->labelRepresentativePostcode.'</dt>
			<dd>'.htmlentities( $sellerUser->LegalRepresentativeAddress->PostalCode, ENT_QUOTES, 'UTF-8' ).'</dd>
			<dt>'.$w->labelRepresentativeCity.'</dt>
			<dd>'.htmlentities( $sellerUser->LegalRepresentativeAddress->City, ENT_QUOTES, 'UTF-8' ).'</dd>
			<dt>'.$w->labelRepresentativeCountry.'</dt>
			<dd>'.htmlentities( $sellerUser->LegalRepresentativeAddress->Country, ENT_QUOTES, 'UTF-8' ).'</dd>
			<dt>'.$w->labelRepresentativeRegion.'</dt>
			<dd>'.htmlentities( $sellerUser->LegalRepresentativeAddress->Region, ENT_QUOTES, 'UTF-8' ).'</dd>
		</dl>'.$trigger;

	$panelRepresentative	= HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', 'Juristischer Vertreter' ),
		HtmlTag::create( 'div', array(
			$content,
			$modal
		), ['class' => 'content-panel-inner'] ),
	), ['class' => 'content-panel'] );


	$body		= '<div class="row-fluid">
		<div class="span12">
			<label class="mandatory" for="input_headquarter_address">'.$w->labelHeadquarterAddress.'</label>
			<input type="text" name="headquarter[address]" id="input_headquarter_address" class="span12" required="required" value="'.htmlentities( $sellerUser->HeadquartersAddress->AddressLine1, ENT_QUOTES, 'UTF-8' ).'"/>
		</div>
	</div>
	<div class="row-fluid">
		<div class="span4">
			<label class="mandatory" for="input_headquarter_postcode">'.$w->labelHeadquarterPostcode.'</label>
			<input type="text" name="headquarter[postcode]" id="input_headquarter_postcode" class="span12" required="required" value="'.htmlentities( $sellerUser->HeadquartersAddress->PostalCode, ENT_QUOTES, 'UTF-8' ).'"/>
		</div>
		<div class="span8">
			<label class="mandatory" for="input_headquarter_city">'.$w->labelHeadquarterCity.'</label>
			<input type="text" name="headquarter[city]" id="input_headquarter_city" class="span12" required="required" value="'.htmlentities( $sellerUser->HeadquartersAddress->City, ENT_QUOTES, 'UTF-8' ).'"/>
		</div>
	</div>
	<div class="row-fluid">
		<div class="span6">
			<label class="mandatory" for="input_headquarter_country">'.$w->labelHeadquarterCountry.'</label>
			<input type="text" name="headquarter[country]" id="input_headquarter_country" class="span12" required="required" value="'.htmlentities( $sellerUser->HeadquartersAddress->Country, ENT_QUOTES, 'UTF-8' ).'"/>
		</div>
		<div class="span6">
			<label for="input_headquarter_region">'.$w->labelHeadquarterRegion.'</label>
			<input type="text" name="headquarter[region]" id="input_headquarter_region" class="span12" value="'.htmlentities( $sellerUser->HeadquartersAddress->Region, ENT_QUOTES, 'UTF-8' ).'"/>
		</div>
	</div>';

	$modal		= new View_Helper_Bootstrap_Modal( $env );
	$modal->setHeading( 'Hauptsitz ändern' );
	$modal->setBody( $body );
	$modal->setFormAction( './admin/payment/mangopay/seller/user' );
	$modal->setId( 'modal-admin-payment-mangopay-seller-address-edit' );
//	$modal->setButtonLabelCancel( $iconCancel.'&nbsp;'.$modalWords->buttonCancel );
//	$modal->setButtonLabelSubmit( $iconSave.'&nbsp;'.$modalWords->buttonSubmit );
	$trigger	= new View_Helper_Bootstrap_Modal_Trigger( $env );
	$trigger->setModalId( 'modal-admin-payment-mangopay-seller-address-edit' );
	$trigger->setLabel( $iconEdit.'&nbsp;ändern' );
	$trigger->setAttributes( ['class' => 'btn btn-mini'] );

	$content	= '
		<dl class="dl-horizontal">
			<dt>'.$w->labelHeadquarterAddress.'</dt>
			<dd>'.htmlentities( $sellerUser->HeadquartersAddress->AddressLine1, ENT_QUOTES, 'UTF-8' ).'</dd>
			<dt>'.$w->labelHeadquarterPostcode.'</dt>
			<dd>'.htmlentities( $sellerUser->HeadquartersAddress->PostalCode, ENT_QUOTES, 'UTF-8' ).'</dd>
			<dt>'.$w->labelHeadquarterCity.'</dt>
			<dd>'.htmlentities( $sellerUser->HeadquartersAddress->City, ENT_QUOTES, 'UTF-8' ).'</dd>
			<dt>'.$w->labelHeadquarterCountry.'</dt>
			<dd>'.htmlentities( $sellerUser->HeadquartersAddress->Country, ENT_QUOTES, 'UTF-8' ).'</dd>
			<dt>'.$w->labelHeadquarterRegion.'</dt>
			<dd>'.htmlentities( $sellerUser->HeadquartersAddress->Region, ENT_QUOTES, 'UTF-8' ).'</dd>
		</dl>'.$trigger;
	$panelHeadquarters	= HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', 'Hauptsitz' ),
		HtmlTag::create( 'div', array(
			$content,
			$modal
		), ['class' => 'content-panel-inner'] ),
	), ['class' => 'content-panel'] );

	return $panelCompany.$panelRepresentative.$panelHeadquarters.'<style>
	.input-value {
		height: 22px;
		padding: 5px 6px 3px 8px;
	/*	background-color: rgba(191, 191, 191, 0.33);*/
	/*	border: 1px solid rgba(191, 191, 191, 1);*/
		background-color: rgba(255, 255, 255, 1);
		border-radius: 4px;
		margin: 1px 0px 10px 0px;
		}
	body.moduleAdminPaymentMangopay dl.dl-horizontal dt {
		width: 150px;
		font-weight: normal;
		font-size: 0.9em;
		letter-spacing: -0px;
		opacity: 0.75;
		}
	body.moduleAdminPaymentMangopay dl.dl-horizontal dd {
		margin-left: 170px;
		}


			</style>';
}
