<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var View_Manage_Content_Image $view */
/** @var array $words */
/** @var ?string $path */
/** @var bool $canAddFolder */
/** @var bool $canAddImage */
/** @var bool $canEditFolder */
/** @var bool $canEditImage */
/** @var bool $canProcess */
/** @var bool $canRemoveFolder */
/** @var bool $canRemoveImage */
/** @var bool $canScale */

$w				= (object) $words['index.list'];
$listFolders	= $view->listFolders( /*dirname( $imagePath )*/ $path );

$buttons	= [];
if( $canAddFolder ){
	$icon		= '<i class="icon-plus icon-white"></i>&nbsp;';
	$buttons[]	= HtmlTag::create( 'a', $icon.$w->buttonAddFolder, [
		'href'	=> "./manage/content/image/addFolder",
		'class'	=> "btn btn-small not-btn-info btn-success"
	] );
}
$buttonbar	= '';
if( [] !== $buttons ){
	$buttonbar	= HtmlTag::create( 'div', $buttons, ['class' => 'buttonbar'] );
}

$panelFolders	= '
<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		'.$listFolders.'
		'.$buttonbar.'
	</div>
</div>';

return $panelFolders;
