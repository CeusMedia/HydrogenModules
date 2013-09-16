<?php
$w	= (object) $words['index'];

$listMain	= $this->renderTree( $categories );
$tabs		= $this->renderMainTabs();

return '
'.$tabs.'
<div class="row-fluid">
	<div class="span6">
		'.$listMain.'
	</div>
	<div class="span6">
		<a href="./manage/catalog/category/add" class="btn btn-primary"><i class="icon-plus icon-white"></i> '.$w->buttonAdd.'</a>
	</div>
</div>
';
?>
