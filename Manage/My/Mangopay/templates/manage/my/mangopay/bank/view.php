<?php

$panelFacts		= $view->loadTemplateFile( 'manage/my/mangopay/bank/view.facts.php' );
$panelPayin		= $view->loadTemplateFile( 'manage/my/mangopay/bank/view.payin.php' );
$panelMandates	= $view->loadTemplateFile( 'manage/my/mangopay/bank/view.mandates.php' );
$panelRemove	= $view->loadTemplateFile( 'manage/my/mangopay/bank/view.remove.php' );

return '
<div class="row-fluid">
	<div class="span6">
		'.$panelFacts.'
		'.$panelPayin.'
	</div>
	<div class="span6">
		'.$panelMandates.'
		'.$panelRemove.'
		'./*$panelTransactions.*/'
	</div>
</div>';
