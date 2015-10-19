<?php

/*  --  ARTICLES IN CATEGORY  --  */
$listArticles	= '<em class="muted">Keine vorhanden.</em>';
if( $articles ){
	$rows	= array();
	foreach( $articles as $article ){
		$url	= './manage/catalog/article/edit/'.$article->articleId;
		$link	= UI_HTML_Tag::create( 'a', $article->title, array( 'href' => $url, 'title' => $article->volume ) );
		$rows[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $link ),
			UI_HTML_Tag::create( 'td', $article->volume, array( 'style' => 'text-align: right' ) ),
		) );
	}
	$heads		= UI_HTML_Tag::create( 'thead', UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'th', 'Veröffentlichung' ),
		UI_HTML_Tag::create( 'th', 'Band', array( 'style' => 'text-align: right' ) ),
	) ) );
	$tbody			= UI_HTML_Tag::create( 'tbody', $rows );
	$listArticles	= UI_HTML_Tag::create( 'table', $heads.$tbody, array( 'class' => 'table table-striped table-small' ) );
}

return '
		<div class="content-panel">
			<div class="content-panel-inner">
				'.$listArticles.'
			</div>
		</div>
';
