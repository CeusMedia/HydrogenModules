<?php

$words	= $env->getLanguage()->getWords( 'manage/product' );
$w		= (object) $words['index'];

$iconsStatus	= array(
	-1	=> UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-remove' ) ),
	0	=> UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-pencil' ) ),
	1	=> UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-ok' ) ),
);

$list	= array();
foreach( $products as $item ){
	$class		= isset( $productId ) && $productId == $item->productId ? 'active' : NULL;
	$label		= $iconsStatus[(int) $item->status].'&nbsp;'.$item->title;
	$link		= UI_HTML_Tag::create( 'a', $label, array( 'href' => './manage/catalog/provision/product/edit/'.$item->productId ) );
	$list[]		= UI_HTML_Tag::create( 'li', $link, array( 'class' => $class ) );
}
$list	= UI_HTML_Tag::create( 'ul', $list, array( 'class' => "nav nav-pills nav-stacked" ) );

$iconAdd	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-plus icon-white' ) );
$buttonAdd	= UI_HTML_Tag::create( 'a', $iconAdd.'&nbsp;'.$w->buttonAdd, array(
	'href'	=> './manage/catalog/provision/product/add',
	'class'	=> 'btn btn-success',
) );

return '
<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<div class="row-fluid">
			<div class="span9">
				'.$list.'
				<div class="buttonbar">
					'.$buttonAdd.'
				</div>
			</div>
		</div>
	</div>
</div>';
