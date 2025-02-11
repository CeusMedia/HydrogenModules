<?php

use CeusMedia\HydrogenFramework\Environment;

/** @var array<string,array<string,string>> $words */
/** @var Environment $env */
/** @var View_Info_Gallery $view */
/** @var string $indexMode */
/** @var string $baseUriPath */

$w			= (object) $words['index'];

if( $indexMode === "matrix" )
	$helper		= new View_Helper_Info_Gallery_Matrix( $env );
else{
	$helper		= new View_Helper_Info_Gallery_List( $env );
	$helper->setBaseUriPath( $baseUriPath );
}
$list		= $helper->render();

extract( $view->populateTexts( ['index.top', 'index.bottom'], 'html/info/gallery/' ) );

return $textIndexTop.'
<div id="galleries-index" class="galleries-index-'.$indexMode.'">
	'.$list.'
</div>
'.$textIndexBottom.'
<style>
.galleries-index-list div.span3 {
	text-align: center;
	}
</style>';
