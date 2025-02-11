<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var array $words */
/** @var array<object> $articles */

$w			= (object) $words['edit'];

$list		= '<div><em class="muted">Keine Veröffentlichungen vorhanden.</em></div>';
if( $articles ){
	$list		= [];
//	$rows		= [];
	foreach( $articles as $article ){
		$url	= './manage/catalog/bookstore/article/edit/'.$article->articleId;
		$label	= htmlentities( $article->title, ENT_QUOTES, 'UTF-8' );
		$link	= HtmlTag::create( 'a', $article->title, ['href' => $url] );
		$list[]	= HtmlTag::create( 'li', $link );
//		$rows[]	= HtmlTag::create( 'tr', [
//			HtmlTag::create( 'td', $link, ['class' => ''] ),
//		] );
	}
	$list	= HtmlTag::create( 'ul', $list );
//	$list	= HtmlTag::create( 'table', $rows, ['class' => 'table table-striped'] );
}

return '
<div class="content-panel">
	<!--<h4>Veröffentlichungen</h4>-->
	<div class="content-panel-inner">
		'.$list.'
	</div>
</div>';
