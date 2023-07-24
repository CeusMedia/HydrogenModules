<?php
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/** @var WebEnvironment $env */
/** @var array $words */
/** @var object[] $categories */

$language	= $env->getLanguage()->getLanguage();
$helper		= new View_Helper_Catalog( $env );

$total		= count( $categories );
$edge		= ceil( $total / 2 );
$position	= $helper->renderPositionFromCategory();
$list1		= $helper->renderCategoryList( array_slice( $categories, 0, $edge ), $language );
$list2		= $helper->renderCategoryList( array_slice( $categories, $edge ), $language );

return '
	<script>
ModuleCatalog = {
	setupCategoryIndex: function(selector){
		var container = typeof selector == "undefined" ? $("body") : $(selector);
		container.find("span.hitarea:not(.empty)").click(function(){
			if($(this).hasClass("closed")){
				$(this).removeClass("closed").addClass("open");
				$(this).parent().children("ul").eq(0).show();
			}else{
				$(this).removeClass("open").addClass("closed");
				$(this).parent().children("ul").eq(0).hide();
			}
		});
	}
};
$(document).ready(function(){
	ModuleCatalog.setupCategoryIndex("#categoryList");
});
	</script>
	<div id="categoryList">
		<h2>'.$words['index']['heading'].'</h2>
		'.$position.'<br/>
<!--		<h3>Fachbereiche</h3>-->
		<small>Bitte wählen Sie einen Fachbereich aus, um dessen Serien und Veröffentlichungen zu sehen.</small><br/>
		<br/>
		<div class="column">
			'.$list1.'
		</div>
		<div class="column">
			'.$list2.'
		</div>
	</div>';
