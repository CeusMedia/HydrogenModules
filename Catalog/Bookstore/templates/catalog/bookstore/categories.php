<?php

$language	= $env->getLanguage()->getLanguage();

$filter		= '
		<div class="well">
			<div class="row-fluid">
				<div class="span6">
					<label for="input_search">Fachbereich oder Serie suchen</label>
					<input type="search" name="search" id="input_search" class="span12" autocomplete="off"/>
				</div>
				<div class="span6" style="text-align: right">
					<br/>
					<button type="button" class="btn btn-small" onclick="ModuleCatalogBookstoreCategoryIndex.openAllBranches();">alle ausklappen</button>
					<button type="button" class="btn btn-small" onclick="ModuleCatalogBookstoreCategoryIndex.closeAllBranches();">alle einklappen</button>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<small class="muted">Diese Suchfunktion filtert lediglich Fachbereiche und Serien, jedoch nicht die Veröffentlichungen.<br/>
						Um nach Veröffentlichungen zu suchen, benutzen Sie bitte die <a href="./search">Suche</a>!
					</small>
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
		<small>Bitte wählen Sie einen Fachbereich aus, um dessen Serien und Veröffentlichungen zu sehen.</small><br/>
		<br/>
		'.$filter.'
		'.$list.'
	</div>';

?>
