<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w			= (object) $words['edit'];

$list		= '<div><em class="muted">Keine Veröffentlichungen vorhanden.</em></div>';
if( $articles ){
	$list		= [];
//	$rows		= [];
	foreach( $articles as $article ){
		$url	= './manage/catalog/article/edit/'.$article->articleId;
		$label	= htmlentities( $article->title, ENT_QUOTES, 'UTF-8' );
		$link	= HtmlTag::create( 'a', $article->title, array( 'href' => $url ) );
		$list[]	= HtmlTag::create( 'li', $link );
//		$rows[]	= HtmlTag::create( 'tr', array(
//			HtmlTag::create( 'td', $link, array( 'class' => '' ) ),
//		) );
	}
	$list	= HtmlTag::create( 'ul', $list );
//	$list	= HtmlTag::create( 'table', $rows, array( 'class' => 'table table-striped' ) );
}

return '
<div class="content-panel">
	<!--<h4>Veröffentlichungen</h4>-->
	<div class="content-panel-inner">
		'.$list.'
	</div>
</div>';
?>
