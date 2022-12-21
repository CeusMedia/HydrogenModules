<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w		= (object) $words['index'];

$iconAdd	= HtmlTag::create( 'i', '', ['class' => 'icon-plus icon-white'] );
if( $env->getModules()->has( 'UI_Font_FontAwesome' ) )
	$iconAdd	= HtmlTag::create( 'b', '', ['class' => 'fa fa-fw fa-plus'] );

$list	= '<div class=alert">'.$w->empty.'</div>';
if( $categories ){
	$list	= [];
	foreach( $categories as $item ){
		$link	= HtmlTag::create( 'a', $item->title, ['href' => './manage/blog/category/edit/'.$item->categoryId] );
		$list[]	= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', $link ),
		) );
	}
	$colgroup	= HtmlElements::ColumnGroup( "100%" );
	$thead		= HtmlTag::create( 'thead', HtmlElements::TableHeads( [$w->headTitle] ) );
	$tbody		= HtmlTag::create( 'tbody', $list );
	$list		= HtmlTag::create( 'table', $colgroup.$thead.$tbody, ['class' => 'table table-striped'] );
}

$tabs	= $view->renderTabs( '/category' );

return '
'.$tabs.'
<div class="content-panel content-panel-list">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		'.$list.'
		<div class="buttonbar">
			<div class="btn-group">
				<a href="./manage/blog/category/add" class="btn btn-success">'.$iconAdd.'&nbsp;'.$w->buttonAdd.'</a>
			</div>
		</div>
	</div>
</div>';
