<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w	= (object) $words['edit'];

$tabsMain	= $this->renderMainTabs();

$tabs       = [];
$panes      = array(
	'details'	=> $this->loadTemplateFile( 'manage/catalog/category/edit.details.php', ['w' => $w] ),
	'articles'	=> $this->loadTemplateFile( 'manage/catalog/category/edit.articles.php', ['w' => $w] ),
);

$current	= $this->env->getSession()->get( 'manage.catalog.category.tab' );
if( !$current )
	$current	= @array_shift( array_keys( $words['tabs'] ) );
foreach( $words['tabs'] as $key => $label ){
	$attributes	= array(
		'href'			=> '#tab-'.$key,
		'data-toggle'	=> 'tab',
		'onclick'		=> "ModuleManageCatalog.setCategoryTab('".$key."');",
	);
	$link	= HtmlTag::create( 'a', $label, $attributes );
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

$panelList	= $view->loadTemplateFile( 'manage/catalog/category/list.php' );

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
