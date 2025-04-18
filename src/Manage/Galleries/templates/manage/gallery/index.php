<?php

/** @var View_Manage_Gallery $view */
/** @var array<string,array<string,string>> $words */

$w	= (object) $words['index'];

$galleries	= $view->renderList();

extract( $view->populateTexts( ['top', 'bottom'], 'html/manage/gallery' ) );

return $textTop.'
<div class="row-fluid">
	<div id="layout-gallery-list" class="span3">
		<div class="content-panel">
			<h3>'.$w->heading.'</h3>
			<div class="content-panel-inner">
				'.$galleries.'
				<div class="buttonbar">
					<a href="./manage/gallery/add" class="btn btn-small not-btn-info btn-success"><i class="icon-plus icon-white"></i> '.$w->buttonAdd.'</a>
				</div>
			</div>
		</div>
	</div>
</div>
'.$textBottom;
