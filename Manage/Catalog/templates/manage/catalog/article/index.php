<?php
$w			= (object) $words['index'];

//$articles	= array_slice( $articles, 0, 20 );

$tabs		= $this->renderMainTabs();
$list		= $this->renderList( $articles );

return '
'.$tabs.'
<div class="row-fluid">
	<div class="span2">
		<h4>Filter</h4>
		'.$view->loadTemplateFile( 'manage/catalog/article/filter.php' ).'
	</div>
	<div class="span3">
		<h4>Artikel&nbsp;<small class="muted"><em>(maximal 50)</em></small></h4>
		'.$list.'
	</div>
	<div class="span7">
		<a href="./manage/catalog/article/add" class="btn btn-primary"><i class="icon-plus icon-white"></i> '.$w->buttonAdd.'</a>
	</div>
</div>
';
?>
