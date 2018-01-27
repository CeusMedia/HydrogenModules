<?php

$helper	= new View_Helper_Catalog_Bookstore( $env );
$words	= (object) $words['tag'];

$list	= '<small class="muted"><em>'.$words->empty.'</em></small>';
if( $articles ){
	$articles	= array_slice( $articles, 0, 20 );
	$list	= array();
	foreach( $articles as $article )
		$list[]	= $helper->renderArticleListItem( $article->article );
	$list	= UI_HTML_Tag::create( 'div', $list, array( 'class' => 'articleList' ) );
}

extract( $view->populateTexts( array( 'tag.top', 'tag.bottom' ), 'html/catalog/bookstore/' ) );

$heading	= sprintf( $words->heading, htmlentities( $tag->tag, ENT_QUOTES, 'UTF-8' ) );

return $textTagTop.'
<h2>'.$heading.'</h2>
<br/>
'.$list.'
'.$textTagBottom;

?>