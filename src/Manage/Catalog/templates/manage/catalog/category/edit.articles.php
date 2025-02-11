<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/*  --  ARTICLES IN CATEGORY  --  */
$listArticles	= '<em class="muted">Keine vorhanden.</em>';
if( $articles ){
	$rows	= [];
	foreach( $articles as $article ){
		$url	= './manage/catalog/article/edit/'.$article->articleId;
		$link	= HtmlTag::create( 'a', $article->title, ['href' => $url, 'title' => $article->volume] );
		$rows[]	= HtmlTag::create( 'tr', [
			HtmlTag::create( 'td', $link ),
			HtmlTag::create( 'td', $article->volume, ['style' => 'text-align: right'] ),
		] );
	}
	$heads		= HtmlTag::create( 'thead', HtmlTag::create( 'tr', [
		HtmlTag::create( 'th', 'Veröffentlichung' ),
		HtmlTag::create( 'th', 'Band', ['style' => 'text-align: right'] ),
	] ) );
	$tbody			= HtmlTag::create( 'tbody', $rows );
	$listArticles	= HtmlTag::create( 'table', $heads.$tbody, ['class' => 'table table-striped table-small'] );
}

return '
		<div class="content-panel">
			<div class="content-panel-inner">
				'.$listArticles.'
			</div>
		</div>
';
