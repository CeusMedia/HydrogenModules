<?php

$list	= array();
foreach( $bankAccounts as $bankAccount ){
	$link	= UI_HTML_Tag::create( 'a', $bankAccount->Id, array( 'href' => './manage/my/mangopay/bank/view/'.$bankAccount->Id ) );
	$list[]	= UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'td', $link ),
	) );
}
$tbody	= UI_HTML_Tag::create( 'tbody', $list );
$table	= UI_HTML_Tag::create( 'table', $tbody, array( 'class' => 'table table-striped' ) );
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

return '
<div class="row-fluid">
	<div class="span6">
		<div class="content-panel">
			<h3>User </h3>
			<div class="content-panel-inner">
				'.print_m( $user, NULL, NULL, TRUE ).'
			</div>
		</div>
	</div>
	<div class="span6">
		'.$panelBankAccounts.'
	</div>
</div>';
