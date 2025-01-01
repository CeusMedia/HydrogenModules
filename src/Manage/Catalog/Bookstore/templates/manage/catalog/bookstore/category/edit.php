<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\View;

/** @var View $view */
/** @var array $words */
/** @var array<object> $articles */

$w	= (object) $words['edit'];

$tabsMain	= $view->renderMainTabs();

$tabs		= [];
$panes		= [
	'details'	=> $view->loadTemplateFile( 'manage/catalog/bookstore/category/edit.details.php', ['w' => $w] ),
	'articles'	=> $view->loadTemplateFile( 'manage/catalog/bookstore/category/edit.articles.php', ['w' => $w] ),
];

$current	= $this->env->getSession()->get( 'manage.catalog.bookstore.category.tab' );
if( !$current )
	$current	= @array_shift( array_keys( $words['tabs'] ) );
foreach( $words['tabs'] as $key => $label ){
	$attributes	= array(
		'href'			=> '#tab-'.$key,
		'data-toggle'	=> 'tab',
		'onclick'		=> "ModuleManageCatalogBookstore.setCategoryTab('".$key."');",
	);
	$count	= '';
	if( $key === "articles" )
		$count	= HtmlTag::create( 'span', count( $articles ), ['class' => 'badge badge-info'] );
	$link	= HtmlTag::create( 'a', $label.'&nbsp;'.$count, $attributes );
	$class	= $current == $key ? "active" : NULL;
	$tabs[]	= HtmlTag::create( 'li', $link, ['class' => $class] );
	$attributes		= array(
		'class'		=> "tab-pane".( $current == $key ? " active" : "" ),
		'id'		=> 'tab-'.$key
	);
	$panes[$key]    = HtmlTag::create( 'div', $panes[$key], $attributes );
}
$tabs   = HtmlTag::create( 'ul', $tabs, ['class' => 'nav nav-tabs'] );
$panes  = HtmlTag::create( 'div', $panes, ['class' => 'tab-content'] );

$panelList	= $view->loadTemplateFile( 'manage/catalog/bookstore/category/list.php' );

return '
'.$tabsMain.'
<div class="row-fluid">
	<div class="span6">
		'.$panelList.'
	</div>
	<div class="span6">
		<div class="tabbable /*tabs-left*/">
			'.$tabs.'
			'.$panes.'
		</div>
	</div>
</div>
';
