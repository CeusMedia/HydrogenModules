<?php

/** @var Environment $env */
/** @var array $words */
/** @var object[] $categories */

$language	= $env->getLanguage()->getLanguage();
$w			= (object) $words['categories'];

$filter		= '
		<div class="well">
			<div class="row-fluid">
				<div class="span6">
					<label for="input_search">'.$w->labelSearch.'</label>
					<input type="search" name="search" id="input_search" class="span12" autocomplete="off"/>
				</div>
				<div class="span6" style="text-align: right">
					<br/>
					<button type="button" class="btn btn-small" onclick="ModuleCatalogBookstoreCategoryIndex.openAllBranches();">'.$w->buttonExpandAll.'</button>
					<button type="button" class="btn btn-small" onclick="ModuleCatalogBookstoreCategoryIndex.closeAllBranches();">'.$w->buttonCollapseAll.'</button>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<small class="muted">'.$w->hintSearch.'</small>
				</div>
			</div>
		</div>';

$helper		= new View_Helper_Catalog_Bookstore( $env );
$position	= $helper->renderPositionFromCategory();
$total		= count( $categories );
$list		= $helper->renderCategoryList( $categories, $language );

return '
	<div id="categoryList">
		<h2>'.$words['index']['heading'].'</h2>
		'.$position.'<br/>
<!--		<h3>Fachbereiche</h3>-->
		<small>'.$words['index']['hint'].'</small><br/>
		<br/>
		'.$filter.'
		'.$list.'
	</div>';
