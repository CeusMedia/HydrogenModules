<?php
$w		= (object) $words['index'];

$panelList	= $view->loadTemplateFile( 'manage/content/document/index.list.php' );
$panelAdd	= $view->loadTemplateFile( 'manage/content/document/index.add.php' );

extract( $view->populateTexts( ['top', 'bottom'], 'html/manage/content/document/' ) );

return $textTop.'
<div class="row-fluid">
	<div class="span8">
		'.$panelList.'
	</div>
	<div class="span4">
		'.$panelAdd.'
	</div>
</div>
'.$textBottom.'
<style>
#table-documents {
	table-layout: fixed;
	}
td.cell-timestamp,
td.cell-size {
	font-size: 0.9em;
	}
td.cell-size {
	text-align: right;
	}
</style>';
?>
