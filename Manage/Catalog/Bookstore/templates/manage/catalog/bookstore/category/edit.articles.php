<?php
$iconUp		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-chevron-up' ) );
$iconDown	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-chevron-down' ) );

/*  --  ARTICLES IN CATEGORY  --  */
$listArticles	= '<em class="muted">Keine vorhanden.</em>';
if( $articles ){
	$rows	= array();
	foreach( $articles as $article ){
		$url	= './manage/catalog/bookstore/article/edit/'.$article->articleId;
		$link	= UI_HTML_Tag::create( 'a', $article->title, array( 'href' => $url, 'title' => $article->volume ) );
		$buttonUp	= UI_HTML_Tag::create( 'a', $iconUp, array(
			'href'	=> './manage/catalog/bookstore/category/rankArticle/'.$category->categoryId.'/'.$article->articleId.'/up',
			'class'	=> 'btn btn-mini',
		) );
		$buttonDown	= UI_HTML_Tag::create( 'a', $iconDown, array(
			'href'	=> './manage/catalog/bookstore/category/rankArticle/'.$category->categoryId.'/'.$article->articleId.'/down',
			'class'	=> 'btn btn-mini',
		) );
		$buttons	= UI_HTML_Tag::create( 'div', array( $buttonUp, $buttonDown ), array( 'class' => 'btn-group' ) );
		$rows[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $article->rank.'. '.$link, array( 'class' => 'autocut' ) ),
			UI_HTML_Tag::create( 'td', $article->volume, array( 'style' => 'text-align: right' ) ),
			UI_HTML_Tag::create( 'td', $buttons, array( 'style' => 'text-align: right' ) ),
		) );
	}
	$heads		= UI_HTML_Tag::create( 'thead', UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'th', 'Veröffentlichung' ),
		UI_HTML_Tag::create( 'th', 'Band', array( 'style' => 'text-align: right' ) ),
		UI_HTML_Tag::create( 'th', 'Rank' ),
	) ) );
	$tbody			= UI_HTML_Tag::create( 'tbody', $rows );
	$colgroup		= UI_HTML_Elements::ColumnGroup( array( '', '20%', '15%' ) );
	$listArticles	= UI_HTML_Tag::create( 'table', $colgroup.$heads.$tbody, array( 'class' => 'table table-striped table-small table-condensed' ) );
}

return '
		<div class="content-panel">
			<div class="content-panel-inner">
				'.$listArticles.'
			</div>
		</div>
';
