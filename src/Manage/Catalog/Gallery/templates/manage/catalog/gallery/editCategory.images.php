<?php

$w	= (object) $words['editCategory.images'];

$list	= $view->renderImageMatrix( $category, './manage/catalog/gallery/addImage/'.$categoryId );

return '
<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		'.$list.'
	</div>
</div>';

