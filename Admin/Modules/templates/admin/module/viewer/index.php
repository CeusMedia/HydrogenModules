<?php

$categories		= array_combine( $categories, $categories );
$categories['']	= $words['index']['categoryNone'];
$filters		= array();

$listSections	= $this->renderModuleSections( $modules, $categories, $filters );

return '
<div id="search">
	<input type="text" name="query" id="input_query" placeholder="Suche" autocomplete="off"/>
	<div id="search-reset"></div>
</div>
<div id="filter">
    <div id="radio">
        <input type="radio" id="filter_type0" name="type" value="0"/><label for="filter_type0">Alle</label>
        <input type="radio" id="filter_type1" name="type" value="1"/><label for="filter_type1">...</label>
        <input type="radio" id="filter_type2" name="type" value="2"/><label for="filter_type2">Installiert</label>
    </div>
</div>
<script>
$(document).ready(function(){
	$("#radio").buttonset();
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
