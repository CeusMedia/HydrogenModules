<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w			= (object) $words['edit'];

#$articles	= array_slice( $articles, 0, 50 );

$tabsMain	= $this->renderMainTabs();

$tabs		= [];
$panes		= array(
	'details'		=> $this->loadTemplateFile( 'manage/catalog/bookstore/article/edit.details.php', ['w' => $w] ),
	'authors'		=> $this->loadTemplateFile( 'manage/catalog/bookstore/article/edit.authors.php', ['w' => $w] ),
	'categories'	=> $this->loadTemplateFile( 'manage/catalog/bookstore/article/edit.categories.php', ['w' => $w] ),
	'cover'			=> $this->loadTemplateFile( 'manage/catalog/bookstore/article/edit.cover.php', ['w' => $w] ),
	'documents'		=> $this->loadTemplateFile( 'manage/catalog/bookstore/article/edit.documents.php', ['w' => $w] ),
	'tags'			=> $this->loadTemplateFile( 'manage/catalog/bookstore/article/edit.tags.php', ['w' => $w] ),
);

$current	= $this->env->getSession()->get( 'manage.catalog.bookstore.article.tab' );
if( !$current )
	$current	= @array_shift( array_keys( $words['tabs'] ) );
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
</div>
';
?>
