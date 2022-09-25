<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$panelView	= '
<div class="content-panel panel-mangopay-view" id="panel-mangopay-user-view">
	<h3>Update User</h3>
	<div class="content-panel-inner">
			<div class="row-fluid">
				<div class="span2">
					<label>Firstname</label>
					<div class="span12 value">'.htmlentities( $user->FirstName, ENT_QUOTES, 'UTF-8' ).'</div>
				</div>
				<div class="span3">
					<label>Lastname</label>
					<div class="span12 value">'.htmlentities( $user->LastName, ENT_QUOTES, 'UTF-8' ).'</div>
				</div>
				<div class="span3">
					<label>Birthday</label>
					<div class="span12 value">'.date( 'Y-m-d', $user->Birthday ).'</div>
				</div>
				<div class="span4">
					<label>E-Mail</label>
					<div class="span12 value">'.htmlentities( $user->Email, ENT_QUOTES, 'UTF-8' ).'</div>
				</div>
			</div>
			<h4>Address</h4>
			<div class="row-fluid">
				<div class="span3">
					<label>Country</label>
					<div class="span12 value">'.htmlentities( $user->Address->Country, ENT_QUOTES, 'UTF-8' ).'</div>
				</div>
				<div class="span3">
					<label>Region</label>
					<div class="span12 value">'.htmlentities( $user->Address->Region, ENT_QUOTES, 'UTF-8' ).'</div>
				</div>
				<div class="span2">
					<label>Postal Code</label>
					<div class="span12 value">'.htmlentities( $user->Address->PostalCode, ENT_QUOTES, 'UTF-8' ).'</div>
				</div>
				<div class="span4">
					<label>City</label>
					<div class="span12 value">'.htmlentities( $user->Address->City, ENT_QUOTES, 'UTF-8' ).'</div>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span6">
					<label>Address Line 1</label>
					<div class="span12 value">'.htmlentities( $user->Address->AddressLine1, ENT_QUOTES, 'UTF-8' ).'</div>
				</div>
				<div class="span6">
					<label>Address Line 2</label>
					<div class="span12 value">'.htmlentities( $user->Address->AddressLine2, ENT_QUOTES, 'UTF-8' ).'</div>
				</div>
			</div>
			<div class="buttonbar">
				<a href="./manage/my/mangopay/user/edit" class="btn"><i class="fa fa-fw fa-pencil"></i>&nbsp;ändern</a>
			</div>
		</form>
	</div>
</div>';

$list	= [];
foreach( $bankAccounts as $bankAccount ){
	$link	= HtmlTag::create( 'a', $bankAccount->Id, array( 'href' => './manage/my/mangopay/bank/view/'.$bankAccount->Id ) );
	$list[]	= HtmlTag::create( 'tr', array(
		HtmlTag::create( 'td', $link ),
	) );
}
$tbody	= HtmlTag::create( 'tbody', $list );
$table	= HtmlTag::create( 'table', $tbody, array( 'class' => 'table table-striped' ) );
$panelBankAccounts	= '
<div class="content-panel">
	<h3>Bank Accounts</h3>
	<div class="content-panel-inner">
		'.$table.'
		<div class="buttonbar">
			<a href="./manage/my/mangopay/bank/add" class="btn btn-success">add</a>
		</div>
	</div>
</div>';


$list	= new View_Helper_Accordion( 'user-transations' );
$list->setSingleOpen( TRUE );
foreach( $transactions as $item ){
	$id			= HtmlTag::create( 'small', $item->Id.':', array( 'class' => 'muted' ) );
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


$rows		= [];
foreach( $wallets as $wallet ){
	$link	= HtmlTag::create( 'a', $wallet->Id, array( 'href' => './manage/my/mangopay/wallet/view/'.$wallet->Id ) );
	$rows[]	= HtmlTag::create( 'tr', array(
		HtmlTag::create(' td', $link, array( 'class' => 'cell-wallet-id' ) ),
		HtmlTag::create(' td', $wallet->Description, array( 'class' => 'cell-wallet-title' ) ),
		HtmlTag::create(' td', $this->formatMoney( $wallet->Balance ), array( 'class' => 'cell-wallet-balance' ) ),
	) );
}
$colgroup	= HtmlElements::ColumnGroup( "120", "", "120" );
$thead		= HtmlTag::create( 'thead', HtmlElements::TableHeads( array( 'ID', 'Wallet Name', 'Betrag' ) ) );
$tbody		= HtmlTag::create( 'tbody', $rows );
$table		= HtmlTag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-striped' ) );
$panelWallets	= '
<div class="content-panel">
	<h3>Wallets</h3>
	<div class="content-panel-inner">
		'.$table.'
	</div>
</div>';

return $panelView.'
<div class="row-fluid">
	<div class="span6">
		'.$panelWallets.'
	</div>
	<div class="span6">
		'.$panelBankAccounts.'
	</div>
</div>
<div class="row-fluid">
	<div class="span12">
		'.$panelTransactions.'
	</div>
</div>';
