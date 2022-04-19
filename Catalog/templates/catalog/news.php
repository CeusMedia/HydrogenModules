<?php
$env->getRuntime()->reach( 'Template: Catalog/News: start' );

extract( $view->populateTexts( array( 'news.top', 'news.bottom' ), 'html/catalog/' ) );
$helper	= new View_Helper_Catalog( $env );

$list	= array();
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

?>
