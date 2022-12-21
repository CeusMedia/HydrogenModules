<?php

return '<h3>Manage:My:Mangopay</h3>

<div class="row-fluid">
	<div class="span6">
		'.View_Helper_Panel_Mangopay_Wallets::renderStatic( $env, $wallets, array(
			'linkItem'	=> './manage/my/mangopay/wallet/view/%s?backwardTo=manage/my/mangopay',
			'linkBack'	=> '',
			'linkAdd'	=> '',
		) ).'
	</div>
	<div class="span6">
		'.View_Helper_Panel_Mangopay_BankAccounts::renderStatic( $env, $bankAccounts).'
	</div>
</div>
<div class="row-fluid">
	<div class="span6">
		'.View_Helper_Panel_Mangopay_Cards::renderStatic( $env, $cards, array(
			'linkItem'	=> './manage/my/mangopay/card/view/%s?backwardTo=manage/my/mangopay',
			'linkBack'	=> '',
			'linkAdd'	=> './manage/my/mangopay/card/add?backwardTo=manage/my/mangopay',
		) ).'
	</div>
	<div class="span6">
		'.View_Helper_Panel_Mangopay_Transactions::renderStatic( $env, $transactions ).'
	</div>
</div>
';

?>
