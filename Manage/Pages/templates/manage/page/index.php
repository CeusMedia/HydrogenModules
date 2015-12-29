<?php

$panelTree	= $view->loadTemplateFile( 'manage/page/tree.php' );

extract( $view->populateTexts( array( 'index' ), 'manage/page/' ) );

//  --  LAYOUT  --  //
return '
<div class="row-fluid">
	<div id="manage-page-tree" class="span3">
		'.$panelTree.'
	</div>
	<div id="manage-page-main" class="span9">
		<div style="float: left; width: 100%">
			'.$textIndex.'
		</div>
	</div>
</div>';
?>
