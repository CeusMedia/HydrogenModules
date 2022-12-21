<?php

$panelFacts		= $view->loadTemplateFile( 'manage/my/mangopay/bank/view.facts.php' );
$panelGuide		= $view->loadTemplateFile( 'manage/my/mangopay/bank/view.guide.php' );
$panelMandates	= $view->loadTemplateFile( 'manage/my/mangopay/bank/view.mandates.php' );
$panelRemove	= $view->loadTemplateFile( 'manage/my/mangopay/bank/view.remove.php' );

return '
<h2><a class="muted" href="./manage/my/mangopay/bank">Bankkonto</a> '.$bankAccount->OwnerName.'</h2>
<div class="row-fluid">
	<div class="span6">
		'.$panelGuide.'
		'.$panelMandates.'
	</div>
	<div class="span6">
		'.$panelFacts.'
		'.$panelRemove.'
		'./*$panelTransactions.*/'
	</div>
</div>';
