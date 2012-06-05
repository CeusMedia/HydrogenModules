<?php

$categories		= array_combine( $categories, $categories );
$categories['']	= $words['index']['categoryNone'];
$filters		= array( 'type' => array( Model_Module::TYPE_CUSTOM, Model_Module::TYPE_COPY, Model_Module::TYPE_LINK ) );

$listSections	= $this->renderModuleSections( $modules, $categories, $filters );

if( !$listSections )
	$listSections	= '<em>Keine <a href="./manage/module">Module</a> installiert.</em>';

return '
<h3 class="position">
	<span>'.$words['index']['heading'].'</span>
</h3><br/>
<div class="module-overview">
	'.$listSections.'
	<div class="column-clear"></div>
</div>';

?>
