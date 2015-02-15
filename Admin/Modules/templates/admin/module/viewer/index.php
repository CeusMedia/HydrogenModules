<?php

$categories		= array_combine( $categories, $categories );
$categories['']	= $words['index']['categoryNone'];

switch( (int) $filterModuleScope ){
	case 2:
		$filters	= array( 'type' => array( Model_Module::TYPE_CUSTOM, Model_Module::TYPE_COPY, Model_Module::TYPE_LINK ) );
		break;
	case 1:
		$filters	= array( 'type' => array( Model_Module::TYPE_SOURCE ) );
		break;
	case 0;
	default:
		$filters	= array();
		break;
}

$listSections	= $this->renderModuleSections( $modules, $categories, $filters );

return '
<div id="search">
	<input type="text" name="query" id="input_query" placeholder="Suche" autocomplete="off"/>
	<div id="search-reset"></div>
</div>
<div id="module-scope-selector">
    <div id="module-scope-selector-radio">
        <input type="radio" id="filter_type0" name="type" value="0"/><label for="filter_type0">alle</label>
        <input type="radio" id="filter_type1" name="type" value="1"/><label for="filter_type1">nicht installiert</label>
        <input type="radio" id="filter_type2" name="type" value="2"/><label for="filter_type2">installiert</label>
    </div>
</div>
<!--  for Bootstrap + FontAwesome
<div id="module-scope-selector">
	<div class="btn-group">
		<button class="btn btn-small" href="./admin/module/viewer/setScope/0"><b class="fa fa-asterisk fa-fw"></b>&nbsp;alle</button>
		<button class="btn btn-small" href="./admin/module/viewer/setScope/1"><b class="fa fa-asterisk fa-fw"></b>&nbsp;nicht installiert</button>
		<button class="btn btn-small" href="./admin/module/viewer/setScope/2"><b class="fa fa-asterisk fa-fw"></b>&nbsp;installiert</button>
	</div>
</div>-->
<script>
$(document).ready(function(){
	var container = $("#module-scope-selector-radio");
	container.find("#filter_type'.$filterModuleScope.'").prop("checked", "checked");
	container.buttonset();
	container.find("input").bind("change", function(){
		var url = "./admin/module/viewer/setScope/" + $(this).val();
		document.location.href = url;
	});
});
</script>
<h3 class="position">
	<span>'.$words['index']['heading'].'</span>
</h3><br/>
<div class="module-overview">
	'.$listSections.'
	<div class="column-clear"></div>
</div>
';

?>
