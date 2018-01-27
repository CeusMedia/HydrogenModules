<?php
if( !$sellerUser->Id || empty( $sellerUser->HeadquartersAddress ) )
	return;

$w	= (object) $words['panel-wallets'];

$iconAdd	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus' ) );
$iconCancel	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
$iconSave	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );
$iconWallet	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-briefcase' ) );
$iconBank	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-bank' ) );

$helperMoney		= new View_Helper_Mangopay_Entity_Money( $this->env );
$helperMoney->setFormat( View_Helper_Mangopay_Entity_Money::FORMAT_AMOUNT_SPACE_CURRENCY );
$helperMoney->setNumberFormat( View_Helper_Mangopay_Entity_Money::NUMBER_FORMAT_COMMA );

$gotCurrencies	= array();

$list		= UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h5', 'Noch kein Portmoney vorhanden.' ),
	UI_HTML_Tag::create( 'p', 'Portmoneys für die verschiedenen Währungen werden automatisch hinzugefügt, wenn eine Zahlung in einer bestimmten Währung eingeht.<br/>Sie müssen hier also nicht unbedingt Portmoneys hinzufügen.' ),
	UI_HTML_Tag::create( 'p', 'Um die Einrichtung vollständig abzuschließen ist es jedoch ratsam, ein Portmoney für die Standardwährung des Shops anzulegen.' ),
), array( 'class' => 'alert alert-info' ) );

if( $sellerWallets ){
	$list	= array();
	foreach( $sellerWallets as $wallet ){
		$gotCurrencies[]	= $wallet->Currency;
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

$modalWords		= (object) $words['modal-wallet-add'];
$optCurrency	= $words['currencies'];
foreach( $gotCurrencies as $key )
	unset( $optCurrency[$key] );
foreach( $optCurrency as $key => $value )
	$optCurrency[$key]	= $key.' - '.$value;
$optCurrency	= UI_HTML_Elements::Options( $optCurrency );
$body	= '
	<div class="row-fluid">
		<div class="span6">
			<label for="input_currency">'.$modalWords->labelCurrency.'</label>
			<select name="currency" id="input_currency" class="span12">'.$optCurrency.'</select>
		</div>
	</div>
</form>';

$modal		= new View_Helper_Bootstrap_Modal( $env );
$modal->setHeading( $modalWords->heading );
$modal->setBody( $body );
$modal->setFormAction( './admin/payment/mangopay/seller/wallet' );
$modal->setId( 'modal-admin-payment-mangopay-seller-wallet-add' );
$modal->setButtonLabelCancel( $iconCancel.'&nbsp;'.$modalWords->buttonCancel );
$modal->setButtonLabelSubmit( $iconSave.'&nbsp;'.$modalWords->buttonSubmit );
$trigger	= new View_Helper_Bootstrap_Modal_Trigger( $env );
$trigger->setModalId( 'modal-admin-payment-mangopay-seller-wallet-add' );
$trigger->setLabel( $iconAdd.'&nbsp;'.$w->buttonAdd );
$trigger->setAttributes( array( 'class' => 'btn btn-success' ) );

return UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', $iconWallet.'&nbsp;Portmoneys' ),
	UI_HTML_Tag::create( 'div', array(
		$list,
		UI_HTML_Tag::create( 'div', array(
			$trigger
		), array( 'class' => 'buttonbar' ) ),
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) ).$modal;
