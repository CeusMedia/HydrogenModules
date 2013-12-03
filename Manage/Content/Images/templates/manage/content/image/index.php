<?php

$listFolders	= $view->listFolders( $path );

function listImages( $env, $pathImages, $path, $extensions, $maxWidth, $maxHeight ){
	$list			= array();
	$index			= new DirectoryIterator( $pathImages.$path );
	$thumbnailer	= new View_Helper_Thumbnailer( $env );
	foreach( $index as $entry ){
		if( !$entry->isFile() )
			continue;
		$extension	= strtolower( pathinfo( $entry->getFilename(), PATHINFO_EXTENSION ) );
		if( !preg_match( $extensions, $entry->getFilename() ) )
			continue;
		$imagePath	= substr( $entry->getPathname(), strlen( $pathImages ) );
		$thumb		= $thumbnailer->get( $entry->getPathname(), $maxWidth, $maxHeight );
		$image		= UI_HTML_Tag::create( 'img', NULL, array( 'src' => $thumb ) );
		$label		= UI_HTML_Tag::create( 'div', $entry->getFilename() );
		$thumbnail	= UI_HTML_Tag::create( 'div', $image.$label );
		$list[$entry->getFilename()]	= UI_HTML_Tag::create( 'li', $thumbnail, array( 'data-image' => addslashes( $imagePath ) ) );
	}
	natcasesort( $list );
	if( $list )
		return UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'thumbs' ) );
}
$listImages	= '<div><em><small class="muted">Keine Bilder in diesem Ordner gefunden.</small></em></div>';
if( $path && $files = listImages( $env, $basePath, $path, $extensions, 120, 80 ) )
	$listImages	= $files;

$linkEditFolder	= '';
if( $path != "." )
	$linkEditFolder	= '&nbsp;&nbsp;<a class="btn btn-mini" href="./manage/content/image/editFolder?path='.$path.'" title="ändern" alt="ändern"><i class="icon-pencil"></i></a>';

return '
<script>
$(document).ready(function(){
	$(".thumbs>li").bind("click", function(){
		var url = "./manage/content/image/editImage?path="+$(this).data("image");
		document.location.href = url;
	});
});
</script>
<div class="row-fluid">
	<div class="span3">
		<h4>Ordner</h4>
		'.$listFolders.'
		<a href="./manage/content/image/addFolder?path='.$path.'" class="btn btn-info btn-small"><i class="icon-plus icon-white"></i> neuer Ordner</a>
	</div>
	<div class="span9">
		<h4><span class="muted">Ordner: </span>'.$path.$linkEditFolder.'</h4>
		<div style="position: not-relative">
			'.$listImages.'
			<div style="clear: left"></div>
		</div>
		<br/>
		<a href="./manage/content/image/addImage?path='.$path.'" class="btn btn-info btn-small"><i class="icon-plus icon-white"></i> neues Bild hochladen</a>
	</div>
</div>
';
?>
