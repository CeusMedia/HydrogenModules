<?php

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
</div>';

$linkBack	= './'.( $backwardTo ? $backwardTo : 'manage/my/mangopay/wallet' );

$buttonCancel	= '<a href="'.$linkBack.'" class="btn btn-small"><b class="fa fa-arrow-left"></b> zurück</a>';
$buttonPayIn	= '<a href="./manage/my/mangopay/wallet/payin/'.$walletId.'" class="btn btn-primary not-btn-small"><b class="fa fa-sign-in"></b> Einzahlung</a>';
$buttonPayOut	= '<a href="./manage/my/mangopay/wallet/payOut/'.$walletId.'" class="btn btn-primary not-btn-small"><b class="fa fa-sign-out"></b> Auszahlung</a>';
$buttonTransfer	= '<a href="./manage/my/mangopay/wallet/transfer/'.$walletId.'" class="btn btn-info"><b class="fa fa-exchange"></b> Überweisen</a>';

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
	foreach( $cards as $card ){
	//print_m( $card );die;
		$logo	= $helperCardLogo->setProvider( $card->CardProvider )->render();
		$number	= $helperCardNumber->set( $card->Alias )->render();
		$title	= UI_HTML_Tag::create( 'div', $card->Tag, array( 'class' => 'card-title' ) );
		$item	= $logo.$number.$title;
		$list[]	= UI_HTML_Tag::create( 'div', $item, array(
			'class'		=> 'card-list-item-small',
			'onclick'	=> 'document.location.href="./manage/my/mangopay/wallet/payin/card/'.$walletId.'/'.$card->Id.'";',
		) );
	}
	$listCards	= UI_HTML_Tag::create( 'div', $list );
}
$panelPayInCards	= '
<div class="content-panel">
	<h3><i class="fa fa-fw fa-credit-card"></i> Einzahlen mit Kreditkarte</h3>
	<div class="content-panel-inner">
		'.$listCards.'
		<div class="buttonbar">
			<a href="./manage/my/mangopay/card/registration?forwardTo=manage/my/mangopay/wallet/'.$walletId.'" class="btn btn-success"><i class="fa fa-fw fa-plus"></i> andere Karte</a>
		</div>
	</div>
</div>';


return '
<div class="row-fluid">
	<div class="span4">
		'.$panelView.'
	</div>
	<div class="span4">
		'.$panelPayInCards.'
	</div>
</div>
<div class="row-fluid">
	<div class="span12">
		'.View_Helper_Panel_Mangopay_Transactions::renderStatic( $env, $transactions ).'
	</div>
</div>';
