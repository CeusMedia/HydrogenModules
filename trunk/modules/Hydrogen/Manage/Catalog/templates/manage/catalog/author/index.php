<?php
$w			= (object) $words['index'];

$tabs		= $this->renderMainTabs();
$list		= $this->renderList( $authors );

return '
'.$tabs.'
<div class="row-fluid">
	<div class="span4">
		<a class="btn btn-small btn-primary" href="./manage/catalog/author/add"><i class="icon-plus icon-white"></i></a>
		<input type="text" placeholder="Suchen..." id="input_search">
		'.$list.'
	</div>
	<div class="span8">
		<a href="./manage/catalog/author/add" class="btn btn-primary"><i class="icon-plus icon-white"></i> '.$w->buttonAdd.'</a>
	</div>
</div>
';
?>
