<?php
$w		= (object) $words['edit'];

$tabTemplates	= array(
	'settings'	=> 'edit.settings.php',
	'preview'	=> 'edit.preview.php',
	'content'	=> 'edit.content.php',
	'meta'		=> 'edit.meta.php',
	'sitemap'	=> 'edit.sitemap.php',
);
//if( !$appHasMetaModule )
//	unset( $words['tabs']['meta'] );

$tabs		= $view->renderTabs( $words['tabs'], $tabTemplates, $tab );

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
?>
