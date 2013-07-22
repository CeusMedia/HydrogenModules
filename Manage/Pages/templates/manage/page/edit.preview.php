<?php

$divIFrame	= UI_HTML_Tag::create( 'div', "", array( 'id' => "page-preview-iframe-container" ) );
$divMask	= UI_HTML_Tag::create( 'div', "", array( 'id' => "page-preview-mask" ) );

if( 1 /*config:useMask*/ )
	$divContainer	= UI_HTML_Tag::create( 'div', $divIFrame.$divMask, array( 'id' => "page-preview-container" ) );
else
	$divContainer	= UI_HTML_Tag::create( 'div', $divIFrame, array( 'id' => "page-preview-container" ) );

$divPreview		= UI_HTML_Tag::create( 'div', $divContainer, array(
	'id'		=> "page-preview",
	'data-url'	=> $pagePreviewUrl
) );

return $divPreview;

return '
<div id="page-preview" data-url="'.$pagePreviewUrl.'">
	<div id="page-preview-container">
		<div id="page-preview-iframe-container"></div>
		<div id="page-preview-mask"></div>
	</div>
</div>
';
?>
