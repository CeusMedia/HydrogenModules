<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$urlIcons	= 'https://cdn.ceusmedia.de/img/';
$imgEdit	= HtmlTag::create( 'img', NULL, ['src' => $urlIcons.'famfamfam/silk/pencil.png', 'verändern'] );
$total	= 0;
$number	= 0;
$rows	= [];
foreach( $banks as $bank ){
	if( $bank->title == "Fonds" ){
		$rows[]	= '<tr><th>Fonds</th><th></th><th></th></tr>';
		foreach( $bank->funds as $fund ){
			$value	= $fund->pieces * $fund->price->price;
			$total	+= $value;
			$number	++;
			$linkEdit	= HtmlTag::create( 'a', $fund->title, ['href' => './work/finance/fund/edit/'.$fund->fundId] );
			$scope	= $fund->scope ? $words['scopes'][$fund->scope] : '';
			$rows[]	= '<tr><td class="account-title">'.$linkEdit.'</td><td>'.$scope.'</td><td class="currency">'.number_format( $value, 2, ',', '.' ).'&nbsp;&euro;</td></tr>';
		}
	}
	else{
		$linkEdit	= HtmlTag::create( 'a', $bank->title, ['href' => './work/finance/bank/edit/'.$bank->bankId] );
		$rows[]	= '<tr><th>'.$linkEdit.'</th><th></th><th></th></tr>';
		foreach( $bank->accounts as $account ){
			$total	+= $account->value;
			$number	++;
			$linkEdit	= HtmlTag::create( 'a', $account->title, ['href' => './work/finance/bank/account/edit/'.$account->bankAccountId] );
			$scope	= $account->scope ? $words['scopes'][$account->scope] : '';
			$rows[]	= '<tr><td class="account-title">'.$linkEdit.'</td><td>'.$scope.'</td><td class="currency">'.number_format( $account->value, 2, ',', '.' ).'&nbsp;&euro;</td></tr>';
		}
	}
}
if( $number > 1 )
	$rows[]	= '<tr class="total"><td colspan="2">Total: '.count( $banks ).' Bank(en) mit '.$number.' Konten</td><td class="currency">'.number_format( $total, 2, ',', '.' ).'&nbsp;&euro;</td></tr>';
$table	= '<table>'.join( $rows ).'</table>';

$panelList	= '
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

$w			= (object) $words['filter'];

$optType	= HtmlElements::Options( $words['types'], $session->get( 'filter_finance_type' ) );
$optScope	= HtmlElements::Options( $words['scopes'], $session->get( 'filter_finance_scope' ) );
/*			<li class="column-left-10">
				<label for="input_currency">'.$w->labelCurrency.'</label><br/>
				<select type="text" name="currency" id="input_currency" class="max">'.$optCurrency.'</select/>
			</li>
*/

$panelFilter	= '
<form action="./work/finance/filter" method="post">
	<fieldset>
		<legend>'.$w->legend.'</legend>
		<ul class="input">
			<li class="not-column-left-25">
				<label for="input_type">'.$w->labelType.'</label><br/>
				<select type="text" name="type" id="input_type" class="max">'.$optType.'</select/>
			</li>
			<li class="not-column-left-25">
				<label for="input_scope">'.$w->labelScope.'</label><br/>
				<select type="text" name="scope" id="input_scope" class="max">'.$optScope.'</select/>
			</li>
		</ul>
		<div class="buttonbar">
			'.HtmlElements::Button( 'filter', $w->buttonFilter, 'button filter' ).'
		</div>
	</fieldset>
</form>';

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
<div class="column-left-20">
	'.$panelFilter.'
</div><div class="column-left-80">
	'.$panelList.'
</div>';
