<?php

if( !$sellerUser->Id || empty( $sellerUser->HeadquartersAddress ) )
	return;

$w	= (object) $words['panel-banks'];

$iconAdd	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus' ) );
$iconCancel	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
$iconSave	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );
$iconWallet	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-briefcase' ) );
$iconBank	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-bank' ) );

$list		= UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'p', 'Noch kein Bankkonten vorhanden.' ),
), array( 'class' => 'alert alert-info' ) );


$helperIban	= new View_Helper_Mangopay_Entity_IBAN( $env );
$helperBic	= new View_Helper_Mangopay_Entity_BIC( $env );

if( $sellerBanks ){
	$list	= [];
	foreach( $sellerBanks as $bankAccount ){
		$buttonPayOut	= UI_HTML_Tag::create( 'button', $iconBank.'&nbsp;auszahlen', array(
			'type'		=> 'button',
			'class'		=> 'btn btn-mini',
		) );
//		$wallet->Description	= $wallet->Id;
		$id			= UI_HTML_Tag::create( 'small' , $bankAccount->Id );
		$title		= UI_HTML_Tag::create( 'div', $bankAccount->OwnerName, array( 'class' => 'autocut' ) );
		$iban		= UI_HTML_Tag::create( 'small', $helperIban->set( $bankAccount->Details->IBAN ) );
		$bic		= UI_HTML_Tag::create( 'small', $helperBic->set( $bankAccount->Details->BIC) );
		$list[]	= UI_HTML_Tag::create( 'tr', array(
//			UI_HTML_Tag::create( 'td', $id ),
			UI_HTML_Tag::create( 'td', $title ),
			UI_HTML_Tag::create( 'td', $iban.'<br/>'.$bic, array() ),
			UI_HTML_Tag::create( 'td', $buttonPayOut ),
		) );
	}
	$cols	= UI_HTML_Elements::ColumnGroup( array( /*'60', */'', '240', '100' ) );
	$thead	= UI_HTML_Tag::create( 'thead', UI_HTML_Tag::create( 'tr', array(
//		UI_HTML_Tag::create( 'th', $w->headId ),
		UI_HTML_Tag::create( 'th', $w->headTitle ),
		UI_HTML_Tag::create( 'th', $w->headDetails ),
		UI_HTML_Tag::create( 'th', $w->headActions ),
	) ) );
	$tbody	= UI_HTML_Tag::create( 'tbody', $list );
	$list	= UI_HTML_Tag::create( 'table', $cols.$thead.$tbody, array( 'class' => 'table table-fixed' ) );
}

$modalWords		= (object) $words['modal-bank-add'];
$body	= '
	<div class="row-fluid">
		<div class="span8">
			<label for="input_bank_iban">'.$modalWords->labelIBAN.'</label>
			<input type="text" name="iban" id="input_bank_iban" class="span12"/>
		</div>
		<div class="span4">
			<label for="input_bank_bic">'.$modalWords->labelBIC.'</label>
			<input type="text" name="bic" id="input_bank_bic" class="span12"/>
		</div>
	</div>
	<div class="row-fluid">
		<div class="span12">
			<label for="input_bank_title">'.$modalWords->labelTitle.'</label>
			<input type="text" name="title" id="input_bank_title" class="span12" value="'.htmlentities( $sellerUser->Name, ENT_QUOTES, 'UTF-8' ).'"/>
		</div>
	</div>
	<div class="row-fluid">
		<div class="span12">
			<label for="input_bank_address">'.$w->labelAddress.'</label>
			<input type="text" name="address" id="input_bank_address" class="span12" value="'.htmlentities( $sellerUser->HeadquartersAddress->AddressLine1, ENT_QUOTES, 'UTF-8' ).'">
		</div>
	</div>
	<div class="row-fluid">
		<div class="span4">
			<label for="input_bank_postcode">'.$w->labelPostcode.'</label>
			<input type="text" name="postcode" id="input_bank_postcode" class="span12" value="'.htmlentities( $sellerUser->HeadquartersAddress->PostalCode, ENT_QUOTES, 'UTF-8' ).'">
		</div>
		<div class="span8">
			<label for="input_bank_city">'.$w->labelCity.'</label>
			<input type="text" name="city" id="input_bank_city" class="span12" value="'.htmlentities( $sellerUser->HeadquartersAddress->City, ENT_QUOTES, 'UTF-8' ).'">
		</div>
	</div>
	<div class="row-fluid">
		<div class="span6">
			<label for="input_bank_country">'.$w->labelCountry.'</label>
			<input type="text" name="country" id="input_bank_country" class="span12" value="'.htmlentities( $sellerUser->HeadquartersAddress->Country, ENT_QUOTES, 'UTF-8' ).'">
		</div>
		<div class="span6">
			<label for="input_bank_region">'.$w->labelRegion.'</label>
			<input type="text" name="region" id="input_bank_region" class="span12" value="'.htmlentities( $sellerUser->HeadquartersAddress->Region, ENT_QUOTES, 'UTF-8' ).'">
		</div>
	</div>
</form>';

$modal		= new View_Helper_Bootstrap_Modal( $env );
$modal->setHeading( $modalWords->heading );
$modal->setBody( $body );
$modal->setFormAction( './admin/payment/mangopay/seller/bank' );
$modal->setId( 'modal-admin-payment-mangopay-seller-bank-add' );
$modal->setButtonLabelCancel( $iconCancel.'&nbsp;'.$modalWords->buttonCancel );
$modal->setButtonLabelSubmit( $iconSave.'&nbsp;'.$modalWords->buttonSubmit );
$trigger	= new View_Helper_Bootstrap_Modal_Trigger( $env );
$trigger->setModalId( 'modal-admin-payment-mangopay-seller-bank-add' );
$trigger->setLabel( $iconAdd.'&nbsp;'.$w->buttonAdd );
$trigger->setAttributes( array( 'class' => 'btn btn-success' ) );

return UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', $iconBank.'&nbsp;Bankkonten' ),
	UI_HTML_Tag::create( 'div', array(
		$list,
		UI_HTML_Tag::create( 'div', array(
			$trigger
		), array( 'class' => 'buttonbar' ) ),
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) ).$modal;
