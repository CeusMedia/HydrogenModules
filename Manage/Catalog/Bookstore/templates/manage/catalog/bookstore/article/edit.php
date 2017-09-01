<?php
$w			= (object) $words['edit'];

#$articles	= array_slice( $articles, 0, 50 );

$tabsMain	= $this->renderMainTabs();

$tabs		= array();
$panes		= array(
	'details'		=> $this->loadTemplateFile( 'manage/catalog/bookstore/article/edit.details.php', array( 'w' => $w ) ),
	'authors'		=> $this->loadTemplateFile( 'manage/catalog/bookstore/article/edit.authors.php', array( 'w' => $w ) ),
	'categories'	=> $this->loadTemplateFile( 'manage/catalog/bookstore/article/edit.categories.php', array( 'w' => $w ) ),
	'cover'			=> $this->loadTemplateFile( 'manage/catalog/bookstore/article/edit.cover.php', array( 'w' => $w ) ),
	'documents'		=> $this->loadTemplateFile( 'manage/catalog/bookstore/article/edit.documents.php', array( 'w' => $w ) ),
	'tags'			=> $this->loadTemplateFile( 'manage/catalog/bookstore/article/edit.tags.php', array( 'w' => $w ) ),
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
		$count	= '&nbsp;'.UI_HTML_Tag::create( 'span', count( $articleTags ), array( 'class' => 'badge badge-info' ) );
	if( $key === "categories" && $articleCategories )
		$count	= '&nbsp;'.UI_HTML_Tag::create( 'span', count( $articleCategories ), array( 'class' => 'badge badge-info' ) );
	if( $key === "authors" && $articleAuthors )
		$count	= '&nbsp;'.UI_HTML_Tag::create( 'span', count( $articleAuthors ), array( 'class' => 'badge badge-info' ) );
	if( $key === "documents" && $articleDocuments )
		$count	= '&nbsp;'.UI_HTML_Tag::create( 'span', count( $articleDocuments ), array( 'class' => 'badge badge-info' ) );

	$link	= UI_HTML_Tag::create( 'a', $label.$count, $attributes );
	$class	= $current == $key ? "active" : NULL;
	$tabs[]	= UI_HTML_Tag::create( 'li', $link, array( 'class' => $class ) );
	$attributes		= array(
		'class'		=> "tab-pane".( $current == $key ? " active" : "" ),
		'id'		=> 'tab-'.$key
	);
	$panes[$key]	= UI_HTML_Tag::create( 'div', $panes[$key], $attributes );
}
$tabs	= UI_HTML_Tag::create( 'ul', $tabs, array( 'class' => 'nav nav-tabs' ) );
$panes	= UI_HTML_Tag::create( 'div', $panes, array( 'class' => 'tab-content' ) );

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
