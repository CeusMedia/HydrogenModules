<?php
$w			= (object) $words['index'];

if( $indexMode === "matrix" )
	$helper		= new View_Helper_Info_Gallery_Matrix( $env );
else{
	$helper		= new View_Helper_Info_Gallery_List( $env );
	$helper->setBaseUriPath( $baseUriPath );
}
$list		= $helper->render();

extract( $view->populateTexts( array( 'index.top', 'index.bottom' ), 'html/info/gallery/' ) );

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
