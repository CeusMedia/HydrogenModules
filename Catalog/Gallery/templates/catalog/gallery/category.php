<?php

//$categories	= '...[categories]...';
//$categories	= $view->renderCategoryList( $categories );

//$images		= '...[images]...';

$list   = [];
$pathThumbs		= $pathImages."thumbnail/".$category->path."/";

$imageMatrix	= $view->renderImageMatrix( $category, $images );

/*
foreach( $images as $i ){
	if( $i->status == 1 ){
		$title	= $i->title ? $i->title : $i->filename;
		$label	= UI_HTML_Tag::create( 'p', $title );
		$image	= UI_HTML_Tag::create( 'img', NULL, array(
			'src'	=> $pathThumbs.$i->filename,
			'alt'	=> $title,
			'title'	=> $title
		) );
		$item	= UI_HTML_Tag::create( 'div', $image, array( 'class' => 'not-span4 thumb' ) );
		$link	= UI_HTML_Tag::create( 'a', $item, array(
			'href'	=> $pathModule.'image/'.$i->galleryImageId,
			'title'	=> $title,
			'class'	=> ''
		) );
		$box	= UI_HTML_Tag::create( 'div', $link, array( 'style' => 'width: 220px; height: 160px; float: left; margin: auto auto; vertical-align: middle; ' ) );
		$list[]	= UI_HTML_Tag::create( 'li', $box, array( 'class' => 'not-thumb', ) );
	}
}
$images	= UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'not-thumbnails unstyled' ) );
*/

$w	= (object) $words['categories'];

extract( $this->populateTexts( array( 'top', 'content', 'bottom' ), 'html/catalog/gallery/category/' ) );

return '

'.$textTop.'
<div class="row-fluid">
	<div class="span3" id="catalog-gallery-category-list">
		<h3>'.$w->heading.'</h3>
		'.$categoryList.'
	</div>
	<div class="span9" id="catalog-gallery-image-list">
		<h3>'.$category->title.'</h3>
		'.$imageMatrix.'
	</div>
</div>
'.$textBottom.'
';

?>
