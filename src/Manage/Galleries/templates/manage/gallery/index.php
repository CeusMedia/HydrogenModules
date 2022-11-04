<?php
$galleries	= $this->renderList();

extract( $view->populateTexts( ['top', 'bottom'], 'html/manage/gallery' ) );

return $textTop.'
<div class="row-fluid">
	<div id="layout-gallery-list" class="span3">
		<div class="content-panel">
			<h3>'.$words['index']['heading'].'</h3>
			<div class="content-panel-inner">
				'.$galleries.'
				<div class="buttonbar">
					<a href="./manage/gallery/add" class="btn btn-small not-btn-info btn-success"><i class="icon-plus icon-white"></i> '.$words['index']['buttonAdd'].'</a>
				</div>
			</div>
		</div>
	</div>
</div>
'.$textBottom;
?>
