<?php
$urlIcons	= 'http://img.int1a.net/';
$imgEdit	= UI_HTML_Tag::create( 'img', NULL, array( 'src' => $urlIcons.'famfamfam/silk/pencil.png', 'verändern' ) );
	$total	= 0;
	$number	= 0;
	$rows	= array();
	foreach( $banks as $bank ){
		$linkEdit	= UI_HTML_Tag::create( 'a', $bank->title, array( 'href' => './work/finance/bank/edit/'.$bank->bankId ) );
		$rows[]	= '<tr><th>'.$linkEdit.'</th><th></th><th></th></tr>';
		foreach( $bank->accounts as $account ){
			$total	+= $account->value;
			$number	++;
			$linkEdit	= UI_HTML_Tag::create( 'a', $account->title, array( 'href' => './work/finance/bank/account/edit/'.$account->bankAccountId ) );
			$rows[]	= '<tr><td class="account-title">'.$linkEdit.'</td><td></td><td class="currency">'.number_format( $account->value, 2, ',', '.' ).'&nbsp;&euro;</td></tr>';
		}
	}
	if( $number > 1 )
		$rows[]	= '<tr class="total"><td colspan="2">Total: '.count( $banks ).' Bank(en) mit '.$number.' Konten</td><td class="currency">'.number_format( $total, 2, ',', '.' ).'&nbsp;&euro;</td></tr>';
	$table	= '<table>'.join( $rows ).'</table>';
	return '
<style>
tr.total td {
	font-weight: bold;
	border-top: 2px solid gray;
	}
tr td.account-title {
	padding-left: 15px;
	}
tr .currency {
	text-align: right;
	}
</style>
<fieldset>
	<legend>Bankkonten</legend>
	'.$table.'
	<div class="buttonbar">
		'.UI_HTML_Elements::LinkButton( './work/finance/bank/add', 'neue Bank', 'button add' ).'
		'.UI_HTML_Elements::LinkButton( './work/finance/bank/account/add', 'neues Konto', 'button add' ).'
		'.UI_HTML_Elements::LinkButton( './work/finance/fund/add', 'neuer Fond', 'button add' ).'
		'.UI_HTML_Elements::LinkButton( './work/finance/bank/update', 'aktualisieren', 'button update' ).'
	</div>
</fieldset>';
?>