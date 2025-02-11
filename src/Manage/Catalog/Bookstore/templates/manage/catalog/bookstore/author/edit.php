<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var array $words */
/** @var array<object> $articles */
/** @var \CeusMedia\HydrogenFramework\View $view */

$w			= (object) $words['edit'];

$tabsMain	= $view->renderMainTabs();

$tabs		= [];
$panes		= [
	'details'	=> $view->loadTemplateFile( 'manage/catalog/bookstore/author/edit.details.php', ['w' => $w] ),
	'articles'	=> $view->loadTemplateFile( 'manage/catalog/bookstore/author/edit.articles.php', ['w' => $w] ),
];

$current	= $this->env->getSession()->get( 'manage.catalog.bookstore.author.tab' );
if( !$current ){
	$tabKeys	= array_keys( $words['tabs'] );
	$current	= @array_shift( $tabKeys );
}
foreach( $words['tabs'] as $key => $label ){
	$attributes	= array(
		'href'			=> '#tab-'.$key,
		'data-toggle'	=> 'tab',
		'onclick'		=> "ModuleManageCatalogBookstore.setAuthorTab('".$key."');",
	);
	$count	= '';
	if( $key === "articles" && $articles )
		$count	= '&nbsp;'.HtmlTag::create( 'span', count( $articles ), ['class' => 'badge badge-info'] );
	$link	= HtmlTag::create( 'a', $label.$count, $attributes );
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

$panelList	= $view->loadTemplateFile( 'manage/catalog/bookstore/author/list.php' );

return '
'.$tabsMain.'
<div class="row-fluid">
	<div class="span4">
		'.$panelList.'
	</div>
	<div class="span8">
		<div class="tabbable /*tabs-left*/">
			'.$tabs.'
			'.$panes.'
		</div>
	</div>
</div>
';
