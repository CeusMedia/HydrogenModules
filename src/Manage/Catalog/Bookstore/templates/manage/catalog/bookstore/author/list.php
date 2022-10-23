<?php
$w			= (object) $words['index'];

$list		= $this->renderList( $authors, isset( $author ) ? $author->authorId : NULL );

return '
		<div class="content-panel">
			<h4>Autoren</h4>
			<div class="content-panel-inner">
				<div class="pull-right">
					<a href="./manage/catalog/bookstore/author/add" class="btn btn-success btn-mini" title="'.$w->buttonAdd.'"><i class="icon-plus icon-white"></i></a>
				</div>
				<input type="text" placeholder="Suchen..." id="input_search">
				'.$list.'
			</div>
		</div>
';
?>
