<?php

/** @var Environment $env */
/** @var View $view */
/** @var array $words */

use CeusMedia\HydrogenFramework\View;

$env->getRuntime()->reach( 'Template: Catalog/Bookstore/News: start' );

extract( $view->populateTexts( ['news.top', 'news.bottom'], 'html/catalog/bookstore/' ) );
$helper	= new View_Helper_Catalog_Bookstore( $env );

$list	= [];
foreach( $articles as $article ){
	$list[]	= $helper->renderArticleListItem( $article );
}
$list	= join( "<br/>", $list );

$env->getRuntime()->reach( 'Template: Catalog/News: done' );
return $textNewsTop.'
<br/>
<div class="articleList">
	'.$list.'
</div>
'.$textNewsBottom;

