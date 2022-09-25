<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w		= (object) $words['index'];

$iconAdd	= HtmlTag::create( 'i', '', array( 'class' => 'icon-plus icon-white' ) );
if( $env->getModules()->has( 'UI_Font_FontAwesome' ) )
	$iconAdd	= HtmlTag::create( 'b', '', array( 'class' => 'fa fa-fw fa-plus' ) );

$list	= '<div class=alert">'.$w->empty.'</div>';



if( $posts ){
	$list	= [];
	foreach( $posts as $post ){
		$link	= HtmlTag::create( 'a', $post->title, array( 'href' => './manage/blog/edit/'.$post->postId ) );
		$list[]	= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', $link ),
			HtmlTag::create( 'td', $words['states'][$post->status] ),
			HtmlTag::create( 'td', $post->category->title ),
		) );
	}
	$colgroup	= HtmlElements::ColumnGroup( "", "15%", "20%" );
	$thead		= HtmlTag::create( 'thead', HtmlElements::TableHeads( array( $w->headTitle, $w->headStatus, $w->headCategory ) ) );
	$tbody		= HtmlTag::create( 'tbody', $list );
	$list		= HtmlTag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-striped' ) );
}

\CeusMedia\Bootstrap\Icon::$defaultSet	= 'FontAwesome';
$pagination	= new \CeusMedia\Bootstrap\PageControl( "./manage/blog/", $page, $pages );


return '
<div class="content-panel content-panel-list">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		'.$list.'
		<div class="buttonbar">
			<div class="btn-group">
				<a href="./manage/blog/add" class="btn btn-success">'.$iconAdd.'&nbsp;neuer Eintrag</a>
			</div>
			<div class="btn-group">
				'.$pagination.'
			</div>
		</div>
	</div>
</div>';
