<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w		= (object) $words['index'];

$list	= [];
foreach( $accounts as $account ){
	$link	= HtmlTag::create( 'a', $account->title, array( 'href' => './work/finance/bank/account/edit/'.$account->bankAccountId ) );
	$list[]	= HtmlTag::create( 'li', $link );
}
$list	= HtmlTag::create( 'ul', $list );

return '
<fieldset>
	<legend>'.$w->legend.'</legend>
	'.$list.'
	<div class="buttonbar">
		'.HtmlElements::LinkButton( './work/finance/bank/account/add', 'neues Konto', 'button icon add' ).'
	</div>
</fieldset>';
