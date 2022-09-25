<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$noPreview	= FALSE;
$controllerActions	= array(
	'Auth::login',
	'Auth::logout',
);
$controllers	= array(
	'Auth',
	'Manage_Page',
);

$logicPage	= new Logic_Page( $env );

if( !$isAccessible )
	$noPreview	= TRUE;
else if( in_array( $page->controller.'::'.$page->action, $controllerActions ) )
	$noPreview	= TRUE;
else if( in_array( $page->controller, $controllers ) )
	$noPreview	= TRUE;

if( $noPreview ){
	return '
<div class="alert alert-info">
	<big>Keine Vorschau für diese Seite.</big><br/>
</div>';
}

$divIFrame	= HtmlTag::create( 'div', "", array( 'id' => "page-preview-iframe-container" ) );
$divMask	= HtmlTag::create( 'div', "", array( 'id' => "page-preview-mask" ) );

if( 1 /*config:useMask*/ )
	$divContainer	= HtmlTag::create( 'div', $divIFrame.$divMask, array( 'id' => "page-preview-container" ) );
else
	$divContainer	= HtmlTag::create( 'div', $divIFrame, array( 'id' => "page-preview-container" ) );

$divPreview		= HtmlTag::create( 'div', $divContainer, array(
	'id'		=> "page-preview",
	'data-url'	=> $pagePreviewUrl,
) );

$linkPage		= HtmlTag::create( 'a', $pageUrl, array( 'href' => $pageUrl ) );

return $linkPage.$divPreview;

return '
<div>Adresse: <a href="'.$pagePreviewUrl.'" target="_blank">'.$pagePreviewUrl.'</a></div>
<div id="page-preview" data-url="'.$pagePreviewUrl.'">
	<div id="page-preview-container">
		<div id="page-preview-iframe-container"></div>
		<div id="page-preview-mask"></div>
	</div>
</div>
';
?>
