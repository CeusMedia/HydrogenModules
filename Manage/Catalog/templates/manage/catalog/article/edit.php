<?php
$w			= (object) $words['edit'];

#$articles	= array_slice( $articles, 0, 50 );

$tabsMain	= $this->renderMainTabs();
$list		= $this->renderList( $articles, $article->article_id );

$tabs		= array();
$panes		= array(
	'details'		=> $this->loadTemplateFile( 'manage/catalog/article/edit.details.php', array( 'w' => $w ) ),
	'authors'		=> $this->loadTemplateFile( 'manage/catalog/article/edit.authors.php', array( 'w' => $w ) ),
	'categories'	=> $this->loadTemplateFile( 'manage/catalog/article/edit.categories.php', array( 'w' => $w ) ),
	'cover'			=> $this->loadTemplateFile( 'manage/catalog/article/edit.cover.php', array( 'w' => $w ) ),
	'documents'		=> $this->loadTemplateFile( 'manage/catalog/article/edit.documents.php', array( 'w' => $w ) ),
	'tags'			=> $this->loadTemplateFile( 'manage/catalog/article/edit.tags.php', array( 'w' => $w ) ),
);

$current	= $this->env->getSession()->get( 'manage.catalog.article.tab' );
if( !$current )
	$current	= @array_shift( array_keys( $words['tabs'] ) );
foreach( $words['tabs'] as $key => $label ){
	$attributes	= array(
		'href'			=> '#tab-'.$key,
		'data-toggle'	=> 'tab',
		'onclick'		=> "setArticleTab('".$key."');",
	);
	$link	= UI_HTML_Tag::create( 'a', $label, $attributes );
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

return '
'.$tabsMain.'
<div class="row-fluid">
	<div class="span2">
		<h4>Filter</h4>
		'.$view->loadTemplateFile( 'manage/catalog/article/filter.php' ).'
	</div>
	<div class="span3">
		<h4>Artikel</h4>
		'.$list.'
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
