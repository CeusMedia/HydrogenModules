<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use CeusMedia\HydrogenFramework\View;

/** @var WebEnvironment $env */
/** @var View $view */
/** @var array $words */

/** @var array $articleTags */
/** @var array $articleCategories */
/** @var array $articleAuthors */
/** @var array $articleDocuments */

$w			= (object) $words['edit'];

#$articles	= array_slice( $articles, 0, 50 );

$tabsMain	= $view->renderMainTabs();

$tabs		= [];
$panes		= [
	'details'		=> $view->loadTemplateFile( 'manage/catalog/bookstore/article/edit.details.php', ['w' => $w] ),
	'authors'		=> $view->loadTemplateFile( 'manage/catalog/bookstore/article/edit.authors.php', ['w' => $w] ),
	'categories'	=> $view->loadTemplateFile( 'manage/catalog/bookstore/article/edit.categories.php', ['w' => $w] ),
	'cover'			=> $view->loadTemplateFile( 'manage/catalog/bookstore/article/edit.cover.php', ['w' => $w] ),
	'documents'		=> $view->loadTemplateFile( 'manage/catalog/bookstore/article/edit.documents.php', ['w' => $w] ),
	'tags'			=> $view->loadTemplateFile( 'manage/catalog/bookstore/article/edit.tags.php', ['w' => $w] ),
];

$current	= $env->getSession()->get( 'manage.catalog.bookstore.article.tab' );
if( !$current ){
	$keys		= array_keys( $words['tabs'] );
	$current	= @array_shift( $keys );
}
foreach( $words['tabs'] as $key => $label ){
	$attributes	= array(
		'href'			=> '#tab-'.$key,
		'data-toggle'	=> 'tab',
		'onclick'		=> "ModuleManageCatalogBookstore.setArticleTab('".$key."');",
	);
	$count	= '';
	if( $key === "tags" && $articleTags )
		$count	= '&nbsp;'.HtmlTag::create( 'span', count( $articleTags ), ['class' => 'badge badge-info'] );
	if( $key === "categories" && $articleCategories )
		$count	= '&nbsp;'.HtmlTag::create( 'span', count( $articleCategories ), ['class' => 'badge badge-info'] );
	if( $key === "authors" && $articleAuthors )
		$count	= '&nbsp;'.HtmlTag::create( 'span', count( $articleAuthors ), ['class' => 'badge badge-info'] );
	if( $key === "documents" && $articleDocuments )
		$count	= '&nbsp;'.HtmlTag::create( 'span', count( $articleDocuments ), ['class' => 'badge badge-info'] );

	$link	= HtmlTag::create( 'a', $label.$count, $attributes );
	$class	= $current == $key ? "active" : NULL;
	$tabs[]	= HtmlTag::create( 'li', $link, ['class' => $class] );
	$attributes		= array(
		'class'		=> "tab-pane".( $current == $key ? " active" : "" ),
		'id'		=> 'tab-'.$key
	);
	$panes[$key]	= HtmlTag::create( 'div', $panes[$key], $attributes );
}
$tabs	= HtmlTag::create( 'ul', $tabs, ['class' => 'nav nav-tabs'] );
$panes	= HtmlTag::create( 'div', $panes, ['class' => 'tab-content'] );

$panelFilter	= $view->loadTemplateFile( 'manage/catalog/bookstore/article/filter.php' );
$panelList		= $view->loadTemplateFile( 'manage/catalog/bookstore/article/list.php' );

return '
'.$tabsMain.'
<div class="row-fluid">
	<div class="span2">
		'.$panelFilter.'
	</div>
	<div class="span3">
		'.$panelList.'
	</div>
	<div class="span7">
		<div class="tabbable /*tabs-left*/">
			'.$tabs.'
			'.$panes.'
		</div>
	</div>
</div>';
