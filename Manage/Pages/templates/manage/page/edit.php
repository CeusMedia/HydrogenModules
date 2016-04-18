<?php
$w		= (object) $words['edit'];

$tabTemplates	= array(
	0	=> 'edit.settings.php',
	1	=> 'edit.preview.php',
	2	=> 'edit.content.php',
	3	=> 'edit.meta.php',
);
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
