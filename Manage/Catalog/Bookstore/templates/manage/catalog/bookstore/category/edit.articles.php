<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconUp		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-chevron-up' ) );
$iconDown	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-chevron-down' ) );

/*  --  ARTICLES IN CATEGORY  --  */
$listArticles	= '<em class="muted">Keine vorhanden.</em>';
if( $articles ){
	$rows	= [];
	foreach( $articles as $article ){
		$url	= './manage/catalog/bookstore/article/edit/'.$article->articleId;
		$link	= HtmlTag::create( 'a', $article->title, array( 'href' => $url, 'title' => $article->volume ) );
		$buttonUp	= HtmlTag::create( 'a', $iconUp, array(
			'href'	=> './manage/catalog/bookstore/category/rankArticle/'.$category->categoryId.'/'.$article->articleId.'/up',
			'class'	=> 'btn btn-mini',
		) );
		$buttonDown	= HtmlTag::create( 'a', $iconDown, array(
			'href'	=> './manage/catalog/bookstore/category/rankArticle/'.$category->categoryId.'/'.$article->articleId.'/down',
			'class'	=> 'btn btn-mini',
		) );
		$buttons	= HtmlTag::create( 'div', array( $buttonUp, $buttonDown ), array( 'class' => 'btn-group' ) );
		$rows[]	= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', $article->rank.'. '.$link, array( 'class' => 'autocut' ) ),
			HtmlTag::create( 'td', $article->volume, array( 'style' => 'text-align: right' ) ),
			HtmlTag::create( 'td', $buttons, array( 'style' => 'text-align: right' ) ),
		) );
	}
	$heads		= HtmlTag::create( 'thead', HtmlTag::create( 'tr', array(
		HtmlTag::create( 'th', 'Veröffentlichung' ),
		HtmlTag::create( 'th', 'Band', array( 'style' => 'text-align: right' ) ),
		HtmlTag::create( 'th', 'Rank' ),
	) ) );
	$tbody			= HtmlTag::create( 'tbody', $rows );
	$colgroup		= HtmlElements::ColumnGroup( array( '', '20%', '15%' ) );
	$listArticles	= HtmlTag::create( 'table', $colgroup.$heads.$tbody, array( 'class' => 'table table-striped table-small table-condensed' ) );
}

return '
		<div class="content-panel">
			<div class="content-panel-inner">
				'.$listArticles.'
			</div>
		</div>
';
