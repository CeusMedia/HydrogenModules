<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$urlIcons	= 'https://cdn.ceusmedia.de/img/';
$imgEdit	= HtmlTag::create( 'img', NULL, ['src' => $urlIcons.'famfamfam/silk/pencil.png', 'verändern'] );
	$total	= 0;
	$number	= 0;
	$rows	= [];
	foreach( $banks as $bank ){
		$linkEdit	= HtmlTag::create( 'a', $bank->title, ['href' => './work/finance/bank/edit/'.$bank->bankId] );
		$rows[]	= '<tr><th>'.$linkEdit.'</th><th></th><th></th></tr>';
		foreach( $bank->accounts as $account ){
			$total	+= $account->value;
			$number	++;
			$linkEdit	= HtmlTag::create( 'a', $account->title, ['href' => './work/finance/bank/account/edit/'.$account->bankAccountId] );
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
		'.HtmlElements::LinkButton( './work/finance/bank/add', 'neue Bank', 'button add' ).'
		'.HtmlElements::LinkButton( './work/finance/bank/account/add', 'neues Konto', 'button add' ).'
		'.HtmlElements::LinkButton( './work/finance/fund/add', 'neuer Fond', 'button add' ).'
		'.HtmlElements::LinkButton( './work/finance/bank/update', 'aktualisieren', 'button update' ).'
	</div>
</fieldset>';
