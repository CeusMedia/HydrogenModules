<?php

$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
$iconList		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-list' ) );
$iconAdd		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus' ) );
$iconPayin		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-sign-in' ) );
$iconPayout		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-sign-out' ) );
$iconTransfer	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-exchange' ) );

/*
$list	= new View_Helper_Accordion( 'user-transations' );
$list->setSingleOpen( TRUE );
foreach( $transactions as $item ){
	$id			= UI_HTML_Tag::create( 'small', $item->Id.':', array( 'class' => 'muted' ) );
	$title		= $id.'&nbsp;'.$this->formatMoney( $item->DebitedFunds );
	$content	= ltrim( print_m( $item, NULL, NULL, TRUE ), '<br/>' );
	$list->add( 'user-transation-'.$item->Id, $title, $content );
}
$panelTransactions	= '
<div class="content-panel">
	<h3>Transactions</h3>
	<div class="content-panel-inner">
		'.$list->render().'
	</div>
</div>';*/
$panelTransactions	= View_Helper_Panel_Mangopay_Transactions::renderStatic( $env, $transactions );

$linkBack	= './'.( $backwardTo ? $backwardTo : 'manage/my/mangopay/wallet' );

$buttonCancel	= '<a href="'.$linkBack.'" class="btn btn-small">'.$iconList.' zurück zur Liste</a>';
$buttonPayIn	= '<a href="./manage/my/mangopay/wallet/payin/'.$walletId.'" class="btn btn-primary not-btn-small">'.$iconPayin.' Einzahlung</a>';
$buttonPayOut	= '<a href="./manage/my/mangopay/wallet/payOut/'.$walletId.'" class="btn btn-primary not-btn-small">'.$iconPayout.' Auszahlung</a>';
$buttonTransfer	= '<a href="./manage/my/mangopay/wallet/transfer/'.$walletId.'" class="btn btn-info">'.$iconTransfer.' Überweisen</a>';

$helperMoney	= new View_Helper_Mangopay_Entity_Money( $env );
$helperMoney->setFormat( View_Helper_Mangopay_Entity_Money::FORMAT_AMOUNT_SPACE_CURRENCY );
$helperMoney->setNumberFormat( View_Helper_Mangopay_Entity_Money::NUMBER_FORMAT_COMMA );
$helperMoney->set( $wallet->Balance );

$panelView	= '
<div class="content-panel">
	<h3><i class="fa fa-fw fa-briefcase"></i> Wallet</h3>
	<div class="content-panel-inner panel-mangopay-view">
		<div class="row-fluid">
			<div class="span12">
				<label>Bezeichnung</label>
				<div class="value">'.$wallet->Description.'</div>
			</div>
		</div>
		<div class="row-fluid">
			<div class="span6">
				<label>Balance</label>
				<div class="value">'.$helperMoney.'</div>
			</div>
			<div class="span6">
				<label>existiert seit</label>
				<div class="value">'.View_Helper_TimePhraser::convertStatic( $env, $wallet->CreationDate, TRUE ).'</div>
			</div>
		</div>
	<!--	'.print_m( $wallet, NULL, NULL, TRUE ).'-->
		<div class="buttonbar">
			'.$buttonCancel.'
			'.$buttonPayIn.'
			'.$buttonPayOut.'
			'.$buttonTransfer.'
		</div>
	</div>
</div>';


$listCards	= UI_HTML_Tag::create( 'div', 'Keine Kreditkarten registriert.', array( 'class' => 'alert alert-warning' ) );
if( $cards ){
	$helperCardLogo		= new View_Helper_Mangopay_Entity_CardProviderLogo( $env );
	$helperCardLogo->setSize( View_Helper_Mangopay_Entity_CardProviderLogo::SIZE_SMALL );
	$helperCardLogo->setNodeName( 'span' );
	$helperCardNumber	= new View_Helper_Mangopay_Entity_CardNumber( $env );
	$list	= array();
	foreach( $cards as $item ){
	//print_m( $item );die;
		$logo	= $helperCardLogo->setProvider( $item->CardProvider )->render();
		$number	= $helperCardNumber->set( $item->Alias )->render();
		$title	= UI_HTML_Tag::create( 'div', $item->Tag, array( 'class' => 'card-title' ) );
		$label	= $logo.$number.$title;
		$list[]	= UI_HTML_Tag::create( 'div', $label, array(
			'class'		=> 'card-list-item-small',
			'onclick'	=> 'document.location.href="./manage/my/mangopay/wallet/payin/card/'.$walletId.'/'.$item->Id.'";',
		) );
	}
	$logo	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus' ) );
	$title	= UI_HTML_Tag::create( 'span', 'Karte hinzufügen', array( 'class' => 'card-title' ) );
	$label	= $logo.$title;
	$list[]	= UI_HTML_Tag::create( 'div', $label, array(
		'class'		=> 'card-list-item-small',
		'onclick'	=> 'document.location.href="./manage/my/mangopay/card/registration?forwardTo=manage/my/mangopay/wallet/'.$walletId.'";',
	) );
	$listCards	= UI_HTML_Tag::create( 'div', $list );
}
$panelPayInCards	= '
<div class="content-panel">
	<h3><i class="fa fa-fw fa-credit-card"></i> Einzahlen mit Kreditkarte</h3>
	<div class="content-panel-inner">
		'.$listCards.'
	</div>
</div>';

$listBankAccounts	= UI_HTML_Tag::create( 'div', 'Keine Bankkonten registriert.', array( 'class' => 'alert alert-warning' ) );
if( $bankAccounts ){
	$list	= array();
	foreach( $bankAccounts as $item ){
//	print_m( $item );die;
		$logo	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-bank' ) );
		$title	= UI_HTML_Tag::create( 'span', $item->OwnerName, array( 'class' => 'card-title' ) );
		$label	= $logo.$title;
		$list[]	= UI_HTML_Tag::create( 'div', $label, array(
			'class'		=> 'card-list-item-small',
			'onclick'	=> 'document.location.href="./manage/my/mangopay/bank/payin/'.$item->Id.'/'.$walletId.'?from=manage/my/mangopay/wallet/view/'.$walletId.'";',
		) );
	}
	$listBankAccounts	= UI_HTML_Tag::create( 'div', $list );
}
$panelPayInBank	= '
<div class="content-panel">
	<h3><i class="fa fa-fw fa-credit-card"></i> Einzahlen mit Bankkonto</h3>
	<div class="content-panel-inner">
		'.$listBankAccounts.'
		<div class="buttonbar">
			<a href="./manage/my/mangopay/bank/add?from=manage/my/mangopay/wallet/'.$walletId.'" class="btn btn-success">'.$iconAdd.' Bankkonto registrieren</a>
		</div>
	</div>
</div>';

$panelPayin	= $view->loadTemplateFile( 'manage/my/mangopay/wallet/view.payin.web.php' );

return '
<div class="row-fluid">
	<div class="span6">
		'.$panelView.'
	</div>
	<div class="span6">
		'.$panelPayin.'
	</div>
</div>
<div class="row-fluid">
	<div class="span6">
		'.$panelPayInCards.'
	</div>
	<div class="span6">
		'.$panelPayInBank.'
	</div>
</div>
<div class="row-fluid">
	<div class="span12">
		'.$panelTransactions.'
	</div>
</div>';
