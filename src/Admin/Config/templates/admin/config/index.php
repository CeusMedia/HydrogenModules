<?php

use CeusMedia\HydrogenFramework\Environment\Web;
use CeusMedia\HydrogenFramework\View;

/** @var Web $env */
/** @var View $view */
/** @var array<array<string,string>> $words */
/** @var object $server */

//$w	= (object) $words['index'];

[$textTop, $textBottom] = array_values( $view->populateTexts( ['top', 'bottom'], 'html/admin/config/index/' ) );

$panelFilter	= $view->loadTemplateFile( 'admin/config/index.filter.php' );
$panelList		= $view->loadTemplateFile( 'admin/config/index.list.php' );

return $textTop.'
<div class="row-fluid">
	<div class="span3">
		'.$panelFilter.'
	</div>
	<div class="span9">
		'.$panelList.'
	</div>
</div>'.$textBottom;
