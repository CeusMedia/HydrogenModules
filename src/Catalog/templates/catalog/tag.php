<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/** @var WebEnvironment $env */
/** @var View_Catalog $view */
/** @var array $words */
/** @var object[] $articles */

$helper	= new View_Helper_Catalog( $env );
$words	= (object) $words['tag'];

$list	= '<small class="muted"><em>'.$words->empty.'</em></small>';
if( $articles ){
	$articles	= array_slice( $articles, 0, 20 );
	$list	= [];
	foreach( $articles as $article )
		$list[]	= $helper->renderArticleListItem( $article->article );
	$list	= HtmlTag::create( 'div', $list, ['class' => 'articleList'] );
}

extract( $view->populateTexts( ['tag.top', 'tag.bottom'], 'html/catalog/' ) );

$heading	= sprintf( $words->heading, htmlentities( $tag->tag, ENT_QUOTES, 'UTF-8' ) );

return $textTagTop.'
<h2>'.$heading.'</h2>
<br/>
'.$list.'
'.$textTagBottom;
