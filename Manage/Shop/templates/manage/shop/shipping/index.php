<?php

$panelPrices	= $view->loadTemplateFile( 'manage/shop/shipping/index.prices.php' );
$panelZones		= $view->loadTemplateFile( 'manage/shop/shipping/index.zones.php' );
$panelGrades	= $view->loadTemplateFile( 'manage/shop/shipping/index.grades.php' );

$tabs	= View_Manage_Shop::renderTabs( $env, 'shipping' );

return $tabs.'
<!--<h3>Versandkosten</h3>-->
'.$panelPrices.'
<div class="row-fluid">
	<div class="span6">'.$panelZones.'</div>
	<div class="span6">'.$panelGrades.'</div>
</div>';
