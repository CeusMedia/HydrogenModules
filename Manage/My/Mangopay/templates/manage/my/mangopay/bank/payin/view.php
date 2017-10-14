<?php
$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
$iconList		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-list' ) );
$iconSave		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );
$iconPrint		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-print' ) );

$helperMoney	= new View_Helper_Mangopay_Entity_Money( $env );
$helperMoney->setFormat( View_Helper_Mangopay_Entity_Money::FORMAT_AMOUNT_SPACE_CURRENCY );
$helperMoney->setNumberFormat( View_Helper_Mangopay_Entity_Money::NUMBER_FORMAT_COMMA );
$helperMoney->set( $payin->PaymentDetails->DeclaredDebitedFunds );

$helperIBAN	= new View_Helper_Mangopay_Entity_IBAN( $env );
$helperBIC	= new View_Helper_Mangopay_Entity_BIC( $env );

$linkBack	= $from ? $from : './manage/my/mangopay/bank/view/'.$bankAccountId;

return '
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
