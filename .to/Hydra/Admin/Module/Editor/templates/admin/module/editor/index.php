<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;

$categories		= array_combine( $categories, $categories );
$categories['']	= $words['index']['categoryNone'];
$filters		= ['type' => [Model_Module::TYPE_CUSTOM, Model_Module::TYPE_COPY, Model_Module::TYPE_LINK]];

$listSections	= $this->renderModuleSections( $modules, $categories, $filters );

if( !$listSections )
	$listSections	= '
<p>
	<em>In dieser Instanz sind gerade <b>keine <a href="./admin/module/viewer">Module</a> installiert</b>.</em>
</p>
<p>
	'.HtmlElements::LinkButton( './admin/module/search', 'Modul suchen', 'button module search' ).'
	'.HtmlElements::LinkButton( './admin/module/viewer', 'alle Module betrachten', 'button module view' ).'
	'.HtmlElements::LinkButton( './admin/module/installer', 'Modul installieren', 'button module add' ).'
</p>';

return '
<div id="search">
	<input type="text" name="query" id="input_query" placeholder="Suche" autocomplete="off"/>
	<div id="search-reset"></div>
</div>
<h3 class="position">
	<span>'.$words['index']['heading'].'</span>
</h3><br/>
<div class="module-overview">
	'.$listSections.'
	<div class="column-clear"></div>
</div>';
?>
