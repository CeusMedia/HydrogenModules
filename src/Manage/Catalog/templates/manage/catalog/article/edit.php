<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w			= (object) $words['edit'];

#$articles	= array_slice( $articles, 0, 50 );

$tabsMain	= $view->renderMainTabs();

$tabs		= [];
$panes		= array(
	'details'		=> $view->loadTemplateFile( 'manage/catalog/article/edit.details.php', ['w' => $w] ),
	'authors'		=> $view->loadTemplateFile( 'manage/catalog/article/edit.authors.php', ['w' => $w] ),
	'categories'	=> $view->loadTemplateFile( 'manage/catalog/article/edit.categories.php', ['w' => $w] ),
	'cover'			=> $view->loadTemplateFile( 'manage/catalog/article/edit.cover.php', ['w' => $w] ),
	'documents'		=> $view->loadTemplateFile( 'manage/catalog/article/edit.documents.php', ['w' => $w] ),
	'tags'			=> $view->loadTemplateFile( 'manage/catalog/article/edit.tags.php', ['w' => $w] ),
);

$current	= $this->env->getSession()->get( 'manage.catalog.article.tab' );
if( !$current ){
	$tabKeys	= array_keys( $words['tabs'] );
	$current	= @array_shift( $tabKeys );
}

foreach( $words['tabs'] as $key => $label ){
	$attributes	= array(
		'href'			=> '#tab-'.$key,
		'data-toggle'	=> 'tab',
		'onclick'		=> "ModuleManageCatalog.setArticleTab('".$key."');",
	);
	$link	= HtmlTag::create( 'a', $label, $attributes );
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

$panelFilter	= $view->loadTemplateFile( 'manage/catalog/article/filter.php' );
$panelList		= $view->loadTemplateFile( 'manage/catalog/article/list.php' );

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
