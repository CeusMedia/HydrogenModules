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

if( $sellerBanks ){
	$list	= array();
	foreach( $sellerBanks as $bankAccount ){
		print_m( $bankAccount );die;
		$buttonPayOut	= UI_HTML_Tag::create( 'button', $iconBank.'&nbsp;auszahlen', array(
			'type'		=> 'button',
			'class'		=> 'btn btn-mini',
			'disabled'	=> $wallet->Balance->Amount > 0 ? NULL : 'disabled',
		) );
//		$wallet->Description	= $wallet->Id;
		$id			= UI_HTML_Tag::create( 'small' , $wallet->Id );
		$title		= UI_HTML_Tag::create( 'div', $wallet->Description, array( 'class' => 'autocut' ) );
		$balance	= UI_HTML_Tag::create( 'strong', $helperMoney->set( $wallet->Balance ) );
		$currency	= UI_HTML_Tag::create( 'abbr', $wallet->Currency, array( 'title' => $words['currencies'][$wallet->Currency] ) );
		$list[]	= UI_HTML_Tag::create( 'tr', array(
//			UI_HTML_Tag::create( 'td', $id ),
			UI_HTML_Tag::create( 'td', $title ),
			UI_HTML_Tag::create( 'td', $currency, array() ),
			UI_HTML_Tag::create( 'td', $balance, array( 'style' => 'text-align: right' ) ),
			UI_HTML_Tag::create( 'td', $buttonPayOut ),
		) );
	}
	$cols	= UI_HTML_Elements::ColumnGroup( array( /*'60', */'', '70', '100', '100' ) );
	$thead	= UI_HTML_Tag::create( 'thead', UI_HTML_Tag::create( 'tr', array(
//		UI_HTML_Tag::create( 'th', $w->headId ),
		UI_HTML_Tag::create( 'th', $w->headTitle ),
		UI_HTML_Tag::create( 'th', $w->headCurrency ),
		UI_HTML_Tag::create( 'th', $w->headBalance, array( 'style' => 'text-align: right' ) ),
		UI_HTML_Tag::create( 'th', $w->headActions ),
	) ) );
	$tbody	= UI_HTML_Tag::create( 'tbody', $list );
	$list	= UI_HTML_Tag::create( 'table', $cols.$thead.$tbody, array( 'class' => 'table table-fixed' ) );
}

$modalWords		= (object) $words['modal-bank-add'];
$body	= '
	<div class="row-fluid">
		<div class="span8">
			<label for="input_iban">'.$modalWords->labelIBAN.'</label>
			<input type="text" name="iban" id="input_iban" class="span12"/>
		</div>
		<div class="span4">
			<label for="input_bic">'.$modalWords->labelBIC.'</label>
			<input type="text" name="bic" id="input_bic" class="span12"/>
		</div>
	</div>
	<div class="row-fluid">
		<div class="span12">
			<label for="input_title">'.$modalWords->labelTitle.'</label>
			<input type="text" name="title" id="input_title" class="span12"/>
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
