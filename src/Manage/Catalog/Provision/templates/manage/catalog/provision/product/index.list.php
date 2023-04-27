<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$words	= $env->getLanguage()->getWords( 'manage/catalog/provision/product' );
$w		= (object) $words['index'];

$iconsStatus	= array(
	-1	=> HtmlTag::create( 'i', '', ['class' => 'icon-remove'] ),
	0	=> HtmlTag::create( 'i', '', ['class' => 'icon-pencil'] ),
	1	=> HtmlTag::create( 'i', '', ['class' => 'icon-ok'] ),
);

$list	= [];
foreach( $products as $item ){
	$class		= isset( $productId ) && $productId == $item->productId ? 'active' : NULL;
	$label		= $iconsStatus[(int) $item->status].'&nbsp;'.$item->title;
	$link		= HtmlTag::create( 'a', $label, ['href' => './manage/catalog/provision/product/edit/'.$item->productId] );
	$list[]		= HtmlTag::create( 'li', $link, ['class' => $class] );
}
$list	= HtmlTag::create( 'ul', $list, ['class' => "nav nav-pills nav-stacked"] );

$iconAdd	= HtmlTag::create( 'i', '', ['class' => 'icon-plus icon-white'] );
$buttonAdd	= HtmlTag::create( 'a', $iconAdd.'&nbsp;'.$w->buttonAdd, [
	'href'	=> './manage/catalog/provision/product/add',
	'class'	=> 'btn btn-success',
] );

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
