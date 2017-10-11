<?php

$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
$iconList		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-list' ) );
$iconSave		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );
$iconPrint		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-print' ) );

if( isset( $payin ) ){
	$helperMoney	= new View_Helper_Mangopay_Entity_Money( $env );
	$helperMoney->setFormat( View_Helper_Mangopay_Entity_Money::FORMAT_AMOUNT_SPACE_CURRENCY );
	$helperMoney->setNumberFormat( View_Helper_Mangopay_Entity_Money::NUMBER_FORMAT_COMMA );
	$helperMoney->set( $payin->PaymentDetails->DeclaredDebitedFunds );

	$helperIBAN	= new View_Helper_Mangopay_Entity_IBAN( $env );
	$helperBIC	= new View_Helper_Mangopay_Entity_BIC( $env );

	$linkBack	= $from ? $from : './manage/my/mangopay/bank/view/'.$bankAccountId;

	$panelShow	= '
<div class="content-panel" id="panel-mangopay-bank-payin">
	<h3>Bankeinzahlung: Auftrag</h3>
	<div class="content-panel-inner">
		<dl class="dl-horizontal">
			<dt>Kontoinhaber</dt>
			<dd>'.$payin->PaymentDetails->BankAccount->OwnerName.'</dd>
			<dt>IBAN</dt>
			<dd>'.$helperIBAN->set( $payin->PaymentDetails->BankAccount->Details->IBAN ).'</dd>
			<dt>BIC</dt>
			<dd>'.$helperBIC->set( $payin->PaymentDetails->BankAccount->Details->BIC ).'</dd>
			<dt>Betrag</dt>
			<dd>'.$helperMoney.'</dd>
			<dt>Referenz</dt>
			<dd>'.$payin->PaymentDetails->WireReference.'</dd>
		</dl>
		<div class="buttonbar">
			<a href="'.$linkBack.'" class="btn">'.$iconCancel.' zurück</a>
			<button type="button" class="btn btn-info" onclick="window.print()">'.$iconPrint.' drucken</a>
		</div>
	</div>
</div>';
	return $panelShow;
}
else{
	$optWallet	= array();
	foreach( $wallets as $item )
		$optWallet[$item->Id]	= $item->Description.' ('.$view->formatMoney( $item->Balance, ' ', 0 ).')';

	$optWallet	= UI_HTML_Elements::Options( $optWallet, $walletId );

	$linkBack	= $from ? $from : './manage/my/mangopay/bank/view/'.$bankAccountId;

	$panelCreate	= '
<div class="content-panel">
	<h3>Überweisung vom Bankkonto</h3>
	<div class="content-panel-inner">
		<form action="./manage/my/mangopay/bank/payin/'.$bankAccountId.'" method="post">
			<input type="hidden" name="from" value="'.$from.'"/>
			<div class="row-fluid">
				<div class="span6">
					<label for="input_amount">Amount</label>
					<input type="number" step="0.01" min="1" max="1000" id="input_amount" name="amount" class="span10" value="'.htmlentities( $amount, ENT_QUOTES, 'UTF-8' ).'"/>&nbsp;<big>&euro;</big>
				</div>
				<div class="span6">
					<label for="input_walletId">Wallet</label>
					<select id="input_walletId" name="walletId">'.$optWallet.'</select>
				</div>
			</div>
			<div class="buttonbar">
				<a href="'.$linkBack.'" class="btn">'.$iconCancel.' zurück</a>
				<button type="submit" name="save" value="payin" class="btn btn-primary">'.$iconSave.' Überweisung anmelden</button>
			</div>
		</form>
	</div>
</div>';
	return $panelCreate;
}

?>
