<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$list	= [];
foreach( $categories as $item ){
	$count	= HtmlTag::create( 'span', '('.count( $item->images ).')', ['class' => 'muted'] );
	$href	= './manage/catalog/gallery/editCategory/'.$item->galleryCategoryId;
	$link	= HtmlTag::create( 'a', $item->title.'&nbsp;'.$count, ['href' => $href, 'class' => 'autocut'] );
	$class	= $item->status == 0 ? 'warning' : ( $item->status > 0 ? 'success' : 'error' );
	$list[]	= HtmlTag::create( 'tr', array(
		HtmlTag::create( 'td', $link, array(
			'class' => isset( $categoryId ) && $item->galleryCategoryId == $categoryId ? 'active' :  NULL,
		) ),
	), ['class' => $class] );
}
//$colgroup	= HtmlElements::ColumnGroup( "80%", "20%" );
$tbody		= HtmlTag::create( 'tbody', $list );
$list		= HtmlTag::create( 'table', /*$colgroup.*/$tbody, ['class' => 'table'] );


$buttonAdd		= HtmlTag::create( 'a', '<i class="icon-plus icon-white"></i>&nbsp;'.$words['index']['buttonAdd'], [
	'href'	=> './manage/catalog/gallery/addCategory',
	'class'	=> "btn btn-success btn-small"
] );

return '
<div class="content-panel">
	<h3>'.$words['index']['heading'].' <small class="muted">('.count( $categories ).')</small></h3>
	<div class="content-panel-inner">
		'.$list.'
		<div class="buttonbar">
			'.$buttonAdd.'
		</div>
	</div>
</div>
';
