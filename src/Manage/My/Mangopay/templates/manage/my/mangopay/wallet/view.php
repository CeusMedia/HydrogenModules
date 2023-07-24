<?php

$panelFacts			= $view->loadTemplateFile( 'manage/my/mangopay/wallet/view.facts.php' );
$panelPayinBankWeb	= $view->loadTemplateFile( 'manage/my/mangopay/wallet/payin/bank/web.php' );
$panelPayinCardWeb	= $view->loadTemplateFile( 'manage/my/mangopay/wallet/payin/card/web.php' );
$panelPayinBanks	= $view->loadTemplateFile( 'manage/my/mangopay/wallet/payin/bank/index.php' );
$panelPayinCards	= $view->loadTemplateFile( 'manage/my/mangopay/wallet/payin/card/index.php' );
$panelTransactions	= View_Helper_Panel_Mangopay_Transactions::renderStatic( $env, $transactions );

return '
<h2><a class="muted" href="./manage/my/mangopay/wallet">Portmoney</a> '.$wallet->Description.'</h2>
<div class="row-fluid">
	<div class="span6">
		'.$panelPayinCardWeb.'
		'.$panelPayinBankWeb.'
		'.$panelPayinCards.'
	</div>
	<div class="span6">
		'.$panelFacts.'
		'.$panelPayinBanks.'
	</div>
</div>
<!--<div class="row-fluid">
	<div class="span12">
		'.$panelTransactions.'
	</div>
</div>-->';
