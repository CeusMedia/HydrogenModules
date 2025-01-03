<?php

/** @var View_Manage_Page $view */
/** @var View_Manage_Page $this */
/** @var array<string,array<string,string>> $words */
/** @var Entity_Page $page */

$w		= (object) $words['edit'];

$tabTemplates	= [
	'settings'	=> 'edit.settings.php',
	'preview'	=> 'edit.preview.php',
	'content'	=> 'edit.content.php',
	'meta'		=> 'edit.meta.php',
	'sitemap'	=> 'edit.sitemap.php',
];
//if( !$appHasMetaModule )
//	unset( $words['tabs']['meta'] );

//print_m( $tab );die;
//print_m( $env->getSession()->getAll() );die;

$tabs		= $view->renderTabs( $words['tabs'], $tabTemplates, $tab ?: current( array_keys( $tabTemplates ) ) );

$panelTree	= $view->loadTemplateFile( 'manage/page/tree.php' );

//  --  LAYOUT  --  //
return '
<div class="row-fluid">
	<div id="manage-page-tree" class="span3">
		'.$panelTree.'
	</div>
	<div id="manage-page-main" class="span9">
		<div style="float: left; width: 100%">
			'.$tabs.'
		</div>
	</div>
</div>';
