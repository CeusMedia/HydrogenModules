<?php
$w			= (object) $words['index'];

$list		= $this->renderList( $authors, isset( $author ) ? $author->authorId : NULL );

return '
		<div class="content-panel">
			<h4>'.$w->heading.'</h4>
			<div class="content-panel-inner">
				<div class="pull-right">
					<a href="./manage/catalog/author/add" class="btn btn-success btn-mini" title="'.$w->buttonAdd.'"><i class="icon-plus icon-white"></i></a>
				</div>
				<input type="text" placeholder="'.htmlentities( $w->placeholderSearch, ENT_QUOTES, 'UTF-8' ).'" id="input_search">
				'.$list.'
			</div>
		</div>
';
