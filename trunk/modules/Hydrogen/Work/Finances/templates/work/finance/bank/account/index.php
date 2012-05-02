<?php

$w		= (object) $words['index'];

$list	= array();
foreach( $accounts as $account ){
	$link	= UI_HTML_Tag::create( 'a', $account->title, array( 'href' => './work/finance/bank/account/edit/'.$account->bankAccountId ) );
	$list[]	= UI_HTML_Tag::create( 'li', $link );
}
$list	= UI_HTML_Tag::create( 'ul', $list );

return '
<fieldset>
	<legend>'.$w->legend.'</legend>
	'.$list.'
	<div class="buttonbar">
		'.UI_HTML_Elements::LinkButton( './work/finance/bank/account/add', 'neues Konto', 'button icon add' ).'
	</div>
</fieldset>';
?>
