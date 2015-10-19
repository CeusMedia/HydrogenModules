<?php
$w			= (object) $words['edit'];

$tabsMain	= $this->renderMainTabs();

$tabs       = array();
$panes      = array(
	'details'	=> $this->loadTemplateFile( 'manage/catalog/author/edit.details.php', array( 'w' => $w ) ),
	'articles'	=> $this->loadTemplateFile( 'manage/catalog/author/edit.articles.php', array( 'w' => $w ) ),
);

$current	= $this->env->getSession()->get( 'manage.catalog.author.tab' );
if( !$current )
	$current	= @array_shift( array_keys( $words['tabs'] ) );
foreach( $words['tabs'] as $key => $label ){
	$attributes	= array(
		'href'			=> '#tab-'.$key,
		'data-toggle'	=> 'tab',
		'onclick'		=> "ModuleManageCatalog.setAuthorTab('".$key."');",
	);
	$link	= UI_HTML_Tag::create( 'a', $label, $attributes );
	$class	= $current == $key ? "active" : NULL;
	$tabs[]	= UI_HTML_Tag::create( 'li', $link, array( 'class' => $class ) );
	$attributes		= array(
		'class'		=> "tab-pane".( $current == $key ? " active" : "" ),
		'id'		=> 'tab-'.$key
	);
	$panes[$key]    = UI_HTML_Tag::create( 'div', $panes[$key], $attributes );
}
$tabs   = UI_HTML_Tag::create( 'ul', $tabs, array( 'class' => 'nav nav-tabs' ) );
$panes  = UI_HTML_Tag::create( 'div', $panes, array( 'class' => 'tab-content' ) );

$panelList	= $view->loadTemplateFile( 'manage/catalog/author/list.php' );

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
