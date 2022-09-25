<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w			= (object) $words['edit'];

$tabsMain	= $this->renderMainTabs();

$tabs       = [];
$panes      = array(
	'details'	=> $this->loadTemplateFile( 'manage/catalog/bookstore/author/edit.details.php', array( 'w' => $w ) ),
	'articles'	=> $this->loadTemplateFile( 'manage/catalog/bookstore/author/edit.articles.php', array( 'w' => $w ) ),
);

$current	= $this->env->getSession()->get( 'manage.catalog.bookstore.author.tab' );
if( !$current )
	$current	= @array_shift( array_keys( $words['tabs'] ) );
foreach( $words['tabs'] as $key => $label ){
	$attributes	= array(
		'href'			=> '#tab-'.$key,
		'data-toggle'	=> 'tab',
		'onclick'		=> "ModuleManageCatalogBookstore.setAuthorTab('".$key."');",
	);
	$count	= '';
	if( $key === "articles" && $articles )
		$count	= '&nbsp;'.HtmlTag::create( 'span', count( $articles ), array( 'class' => 'badge badge-info' ) );
	$link	= HtmlTag::create( 'a', $label.$count, $attributes );
	$class	= $current == $key ? "active" : NULL;
	$tabs[]	= HtmlTag::create( 'li', $link, array( 'class' => $class ) );
	$attributes		= array(
		'class'		=> "tab-pane".( $current == $key ? " active" : "" ),
		'id'		=> 'tab-'.$key
	);
	$panes[$key]    = HtmlTag::create( 'div', $panes[$key], $attributes );
}
$tabs   = HtmlTag::create( 'ul', $tabs, array( 'class' => 'nav nav-tabs' ) );
$panes  = HtmlTag::create( 'div', $panes, array( 'class' => 'tab-content' ) );

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
?>
