<?php

$categories		= array_combine( $categories, $categories );
$categories['']	= $words['index']['categoryNone'];
$filters		= array();

$listSections	= $this->renderModuleSections( $modules, $categories, $filters );

return '
<h3 class="position">
	<span>'.$words['index']['heading'].'</span>
</h3><br/>
<div class="module-overview">
	'.$listSections.'
	<div class="column-clear"></div>
</div>';

?>