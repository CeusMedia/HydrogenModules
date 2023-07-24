<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$noPreview	= FALSE;
$controllerActions	= [
	'Auth::login',
	'Auth::logout',
];
$controllers	= [
	'Auth',
	'Manage_Page',
];

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

$divIFrame	= HtmlTag::create( 'div', "", ['id' => "page-preview-iframe-container"] );
$divMask	= HtmlTag::create( 'div', "", ['id' => "page-preview-mask"] );

if( 1 /*config:useMask*/ )
	$divContainer	= HtmlTag::create( 'div', $divIFrame.$divMask, ['id' => "page-preview-container"] );
else
	$divContainer	= HtmlTag::create( 'div', $divIFrame, ['id' => "page-preview-container"] );

$divPreview		= HtmlTag::create( 'div', $divContainer, [
	'id'		=> "page-preview",
	'data-url'	=> $pagePreviewUrl,
] );

$linkPage		= HtmlTag::create( 'a', $pageUrl, ['href' => $pageUrl] );

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
