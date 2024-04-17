<?php
use CeusMedia\HydrogenFramework\View;

/** @var View $view */
/** @var array $words */
/** @var array<object> $categories */

$w	= (object) $words['index'];

$listMain	= $view->renderTree( $categories, isset( $category ) ? $category->categoryId : NULL );

return '
		<div class="content-panel">
			<div class="pull-right">
				<a href="./manage/catalog/bookstore/category/add" class="btn btn-success btn-mini" title="'.$w->buttonAdd.'"><i class="icon-plus icon-white"></i></a>
			</div>
			<h4>Kategorien</h4>
			<div class="content-panel-inner">
				'.$listMain.'
			</div>
		</div>
';
