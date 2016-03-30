<?php
$w		= (object) $words['index'];

$list	= '<div class=alert">'.$w->empty.'</div>';
if( $posts ){
	$list	= array();
	foreach( $posts as $post ){
		$link	= UI_HTML_Tag::create( 'a', $post->title, array( 'href' => './manage/blog/edit/'.$post->postId ) );
		$list[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $link ),
		) );
	}
	$colgroup	= UI_HTML_Elements::ColumnGroup( "100%" );
	$thead		= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array( $w->headTitle ) ) );
	$tbody		= UI_HTML_Tag::create( 'tbody', $list );
	$list		= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-striped' ) );
}

return '
<div class="content-panel content-panel-list">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		'.$list.'
	</div>
</div>';
