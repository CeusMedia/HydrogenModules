<?php

$tabTemplates	= array(
	0	=> 'edit.settings.php',
	1	=> 'edit.preview.php',
	2	=> 'edit.content.php',
	3	=> 'edit.meta.php',
);
$tabs	= $view->renderTabs( $words['tabs'], $tabTemplates, $tab );

$panelTree	= $view->loadTemplateFile( 'manage/page/tree.php' );

//  --  LAYOUT  --  //
return '
<div class="row-fluid">
	<div id="manage-page-tree" class="span3">
		'.$panelTree.'
	</div>
	<div id="manage-page-main" class="span9">
		<div style="float: left; width: 100%">
			<div class="content-panel">
				<div class="content-panel-inner">
					<form action="./manage/page/edit/'.$current.'/'.$version.'" method="post" class="cmFormChange-auto form-changes-auto">
						'.$tabs.'
						<div class="buttonbar">
							<button type="submit" name="save" class="btn btn-small btn-success"><i class="icon-ok icon-white"></i> speichern</button>
							<button type="reset" class="btn btn-small">zurücksetzen</button>
							<a href="./manage/page/copy/'.$current.'" class="btn btn-small">kopieren</a>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>';
?>
