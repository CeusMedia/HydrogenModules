<?php
$list	= array();
foreach( $categories as $item ){
	$count	= new UI_HTML_Tag( 'span', '('.count( $item->images ).')', array( 'class' => 'muted' ) );
	$href	= './manage/catalog/gallery/editCategory/'.$item->galleryCategoryId;
	$link	= new UI_HTML_Tag( 'a', $item->title.'&nbsp;'.$count, array( 'href' => $href, 'class' => 'autocut' ) );
	$class	= $item->status == 0 ? 'warning' : ( $item->status > 0 ? 'success' : 'error' );
	$list[]	= UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'td', $link, array(
			'class' => isset( $categoryId ) && $item->galleryCategoryId == $categoryId ? 'active' :  NULL,
		) ),
	), array( 'class' => $class ) );
}
//$colgroup	= UI_HTML_Elements::ColumnGroup( "80%", "20%" );
$tbody		= UI_HTML_Tag::create( 'tbody', $list );
$list		= UI_HTML_Tag::create( 'table', /*$colgroup.*/$tbody, array( 'class' => 'table' ) );


$buttonAdd		= UI_HTML_Tag::create( 'a', '<i class="icon-plus icon-white"></i>&nbsp;'.$words['index']['buttonAdd'], array(
	'href'	=> './manage/catalog/gallery/addCategory',
	'class'	=> "btn btn-success btn-small"
) );

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
