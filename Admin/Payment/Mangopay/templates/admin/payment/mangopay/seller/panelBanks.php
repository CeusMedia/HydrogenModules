<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

if( !$sellerUser->Id || empty( $sellerUser->HeadquartersAddress ) )
	return;

$w	= (object) $words['panel-banks'];

$iconAdd	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus' ) );
$iconCancel	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
$iconSave	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );
$iconWallet	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-briefcase' ) );
$iconBank	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-bank' ) );

$list		= HtmlTag::create( 'div', array(
	HtmlTag::create( 'p', 'Noch kein Bankkonten vorhanden.' ),
), array( 'class' => 'alert alert-info' ) );


$helperIban	= new View_Helper_Mangopay_Entity_IBAN( $env );
$helperBic	= new View_Helper_Mangopay_Entity_BIC( $env );

if( $sellerBanks ){
	$list	= [];
	foreach( $sellerBanks as $bankAccount ){
		$buttonPayOut	= HtmlTag::create( 'button', $iconBank.'&nbsp;auszahlen', array(
			'type'		=> 'button',
			'class'		=> 'btn btn-mini',
		) );
//		$wallet->Description	= $wallet->Id;
		$id			= HtmlTag::create( 'small' , $bankAccount->Id );
		$title		= HtmlTag::create( 'div', $bankAccount->OwnerName, array( 'class' => 'autocut' ) );
		$iban		= HtmlTag::create( 'small', $helperIban->set( $bankAccount->Details->IBAN ) );
		$bic		= HtmlTag::create( 'small', $helperBic->set( $bankAccount->Details->BIC) );
		$list[]	= HtmlTag::create( 'tr', array(
//			HtmlTag::create( 'td', $id ),
			HtmlTag::create( 'td', $title ),
			HtmlTag::create( 'td', $iban.'<br/>'.$bic, array() ),
			HtmlTag::create( 'td', $buttonPayOut ),
		) );
	}
	$cols	= HtmlElements::ColumnGroup( array( /*'60', */'', '240', '100' ) );
	$thead	= HtmlTag::create( 'thead', HtmlTag::create( 'tr', array(
//		HtmlTag::create( 'th', $w->headId ),
		HtmlTag::create( 'th', $w->headTitle ),
		HtmlTag::create( 'th', $w->headDetails ),
		HtmlTag::create( 'th', $w->headActions ),
	) ) );
	$tbody	= HtmlTag::create( 'tbody', $list );
	$list	= HtmlTag::create( 'table', $cols.$thead.$tbody, array( 'class' => 'table table-fixed' ) );
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

return HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', $iconBank.'&nbsp;Bankkonten' ),
	HtmlTag::create( 'div', array(
		$list,
		HtmlTag::create( 'div', array(
			$trigger
		), array( 'class' => 'buttonbar' ) ),
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) ).$modal;
