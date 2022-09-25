<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

if( !$sellerUser->Id || empty( $sellerUser->HeadquartersAddress ) )
	return;

$w	= (object) $words['panel-wallets'];

$iconAdd	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus' ) );
$iconCancel	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
$iconSave	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );
$iconWallet	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-briefcase' ) );
$iconBank	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-bank' ) );

$helperMoney		= new View_Helper_Mangopay_Entity_Money( $this->env );
$helperMoney->setFormat( View_Helper_Mangopay_Entity_Money::FORMAT_AMOUNT_SPACE_CURRENCY );
$helperMoney->setNumberFormat( View_Helper_Mangopay_Entity_Money::NUMBER_FORMAT_COMMA );

$gotCurrencies	= [];

$list		= HtmlTag::create( 'div', array(
	HtmlTag::create( 'h5', 'Noch kein Portmoney vorhanden.' ),
	HtmlTag::create( 'p', 'Portmoneys für die verschiedenen Währungen werden automatisch hinzugefügt, wenn eine Zahlung in einer bestimmten Währung eingeht.<br/>Sie müssen hier also nicht unbedingt Portmoneys hinzufügen.' ),
	HtmlTag::create( 'p', 'Um die Einrichtung vollständig abzuschließen ist es jedoch ratsam, ein Portmoney für die Standardwährung des Shops anzulegen.' ),
), array( 'class' => 'alert alert-info' ) );

if( $sellerWallets ){
	$list	= [];
	foreach( $sellerWallets as $wallet ){
		$gotCurrencies[]	= $wallet->Currency;
		$buttonPayOut	= HtmlTag::create( 'button', $iconBank.'&nbsp;auszahlen', array(
			'type'		=> 'button',
			'class'		=> 'btn btn-mini',
			'disabled'	=> $wallet->Balance->Amount > 0 ? NULL : 'disabled',
		) );
//		$wallet->Description	= $wallet->Id;
		$id			= HtmlTag::create( 'small' , $wallet->Id );
		$title		= HtmlTag::create( 'div', $wallet->Description, array( 'class' => 'autocut' ) );
		$balance	= HtmlTag::create( 'strong', $helperMoney->set( $wallet->Balance ) );
		$currency	= HtmlTag::create( 'abbr', $wallet->Currency, array( 'title' => $words['currencies'][$wallet->Currency] ) );
		$list[]	= HtmlTag::create( 'tr', array(
//			HtmlTag::create( 'td', $id ),
			HtmlTag::create( 'td', $title ),
			HtmlTag::create( 'td', $currency, array() ),
			HtmlTag::create( 'td', $balance, array( 'style' => 'text-align: right' ) ),
			HtmlTag::create( 'td', $buttonPayOut ),
		) );
	}
	$cols	= HtmlElements::ColumnGroup( array( /*'60', */'', '70', '100', '100' ) );
	$thead	= HtmlTag::create( 'thead', HtmlTag::create( 'tr', array(
//		HtmlTag::create( 'th', $w->headId ),
		HtmlTag::create( 'th', $w->headTitle ),
		HtmlTag::create( 'th', $w->headCurrency ),
		HtmlTag::create( 'th', $w->headBalance, array( 'style' => 'text-align: right' ) ),
		HtmlTag::create( 'th', $w->headActions ),
	) ) );
	$tbody	= HtmlTag::create( 'tbody', $list );
	$list	= HtmlTag::create( 'table', $cols.$thead.$tbody, array( 'class' => 'table table-fixed' ) );
}

$modalWords		= (object) $words['modal-wallet-add'];
$optCurrency	= $words['currencies'];
foreach( $gotCurrencies as $key )
	unset( $optCurrency[$key] );
foreach( $optCurrency as $key => $value )
	$optCurrency[$key]	= $key.' - '.$value;
$optCurrency	= HtmlElements::Options( $optCurrency );
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

return HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', $iconWallet.'&nbsp;Portmoneys' ),
	HtmlTag::create( 'div', array(
		$list,
		HtmlTag::create( 'div', array(
			$trigger
		), array( 'class' => 'buttonbar' ) ),
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) ).$modal;
