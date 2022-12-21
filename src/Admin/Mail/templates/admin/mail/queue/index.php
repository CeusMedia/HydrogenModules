<?php

use CeusMedia\HydrogenFramework\Environment\Web;
use CeusMedia\HydrogenFramework\View;

/** @var Web $env */
/** @var View $view */
/** @var array<array<string,string>> $words */

$w		= (object) $words['index'];

$panelFilter	= $view->loadTemplateFile( 'admin/mail/queue/index.filter.php' );
$panelList		= $view->loadTemplateFile( 'admin/mail/queue/index.list.php' );

[$textTop, $textBottom]	= array_values( $view->populateTexts( ['top', 'bottom'], 'html/admin/mail/queue/' ) );

return $textTop.'
<div class="row-fluid">
	<div class="span3">
		'.$panelFilter.'
	</div>
	<div class="span9">
		'.$panelList.'
	</div>
</div>
<style>
.list-item-mail {}
</style>'.$textBottom;
