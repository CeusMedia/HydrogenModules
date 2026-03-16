<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var View_Manage_Content_Image $view */
/** @var array $words */
/** @var string $path */
/** @var bool $canAddFolder */
/** @var bool $canAddImage */
/** @var bool $canEditFolder */
/** @var bool $canEditImage */
/** @var bool $canProcess */
/** @var bool $canRemoveFolder */
/** @var bool $canRemoveImage */
/** @var bool $canScale */

$w	= (object) $words['index.folder'];

$listImages		= $view->listImages( $path, 120, 80 );
if( !$listImages )
	$listImages		= '<div><em><small class="muted">'.$w->noEntries.'</small></em></div><br/>';

$linkEditFolder	= '';
if( $canEditFolder && $path != "." )
	$linkEditFolder	= '&nbsp;&nbsp;<a class="btn btn-mini" href="./manage/content/image/editFolder" title="ändern" alt="ändern"><i class="icon-pencil"></i></a>';

$labelFolder	= preg_replace( "/^\.\//", "", $path );

$buttons	= [];
if( $canAddImage ){
	$icon		= '<i class="icon-plus icon-white"></i>&nbsp;';
	$icon		= '<i class="fa fa-fw fa-upload"></i>&nbsp;';
	$buttons[]	= HtmlTag::create( 'a', $icon.$w->buttonAddFile, [
		'href'	=> "./manage/content/image/addImage",
		'class'	=> "btn btn-small not-btn-info btn-success"
	] );
}
$buttonbar	= '';
if( [] !== $buttons ){
	$buttonbar	= HtmlTag::create( 'div', $buttons, ['class' => 'buttonbar'] );
}

$scriptNavListResize	= '
function resizeNavList(selector, offsetBottom){
	var list = $(selector);
	if(list.length){
		offsetBottom = offsetBottom ? offsetBottom : 0;
		var offset = list.offset().top;
		var height = $(window).height();
		var size = height - offset - offsetBottom;
		if(list.height() > size){
			list.height(size);
		}
	}
}

$(document).ready(function(){
//	resizeNavList(".nav-resizing", 60);
})';

$scriptImageClick	= '
$(document).ready(function(){
	$(".thumbs>li").on("click", function(){
		var url = "./manage/content/image/editImage/"+$(this).data("image-hash");
		document.location.href = url;
	});
});';
//if( !$canEditImage )
//	$scriptImageClick	= '';

return '
<script>'.$scriptImageClick.'</script>
<script>'.$scriptNavListResize.'</script>
<div class="content-panel">
	<h3>'.sprintf( $w->heading, $labelFolder.$linkEditFolder ).'</h4>
	<div class="content-panel-inner">
		<div style="position: not-relative">
			'.$listImages.'
			<div style="clear: left"></div>
		</div>
		'.$buttonbar.'
	</div>
</div>';
