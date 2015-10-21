<?php
$w			= (object) $words['edit'];

$list		= '<div><em class="muted">Keine Veröffentlichungen vorhanden.</em></div>';
if( $articles ){
	$list		= array();
//	$rows		= array();
	foreach( $articles as $article ){
		$url	= './manage/catalog/article/edit/'.$article->articleId;
		$label	= htmlentities( $article->title, ENT_QUOTES, 'UTF-8' );
		$link	= UI_HTML_Tag::create( 'a', $article->title, array( 'href' => $url ) );
		$list[]	= UI_HTML_Tag::create( 'li', $link );
//		$rows[]	= UI_HTML_Tag::create( 'tr', array(
//			UI_HTML_Tag::create( 'td', $link, array( 'class' => '' ) ),
//		) );
	}
	$list	= UI_HTML_Tag::create( 'ul', $list );
//	$list	= UI_HTML_Tag::create( 'table', $rows, array( 'class' => 'table table-striped' ) );
}

return '
<div class="content-panel">
	<!--<h4>Veröffentlichungen</h4>-->
	<div class="content-panel-inner">
		'.$list.'
	</div>
</div>';
?>
