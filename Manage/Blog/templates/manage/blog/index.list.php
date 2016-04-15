<?php
$w		= (object) $words['index'];

$iconAdd	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-plus icon-white' ) );
if( $env->getModules()->has( 'UI_Font_FontAwesome' ) )
	$iconAdd	= UI_HTML_Tag::create( 'b', '', array( 'class' => 'fa fa-fw fa-plus' ) );

$list	= '<div class=alert">'.$w->empty.'</div>';



if( $posts ){
	$list	= array();
	foreach( $posts as $post ){
		$link	= UI_HTML_Tag::create( 'a', $post->title, array( 'href' => './manage/blog/edit/'.$post->postId ) );
		$list[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $link ),
			UI_HTML_Tag::create( 'td', $words['states'][$post->status] ),
			UI_HTML_Tag::create( 'td', $post->category->title ),
		) );
	}
	$colgroup	= UI_HTML_Elements::ColumnGroup( "", "15%", "20%" );
	$thead		= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array( $w->headTitle, $w->headStatus, $w->headCategory ) ) );
	$tbody		= UI_HTML_Tag::create( 'tbody', $list );
	$list		= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-striped' ) );
}

\CeusMedia\Bootstrap\Icon::$iconSet	= 'FontAwesome';
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
