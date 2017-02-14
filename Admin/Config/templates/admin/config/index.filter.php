<?php

$w	= (object) $words['index-filter'];

$iconSearch		= new UI_HTML_Tag( 'i', '', array( 'class' => 'icon-zoom-in' ) );
$iconReset		= new UI_HTML_Tag( 'i', '', array( 'class' => 'icon-toom-out' ) );
if( $env->getModules()->has( 'UI_Font_FontAwesome' ) ){
	$iconSearch		= new UI_HTML_Tag( 'b', '', array( 'class' => 'fa fa-fw fa-search' ) );
	$iconReset		= new UI_HTML_Tag( 'b', '', array( 'class' => 'fa fa-fw fa-search-minus' ) );
}

$optCategory	= array( '' => '- alle -' );
foreach( $categories as $category => $nrModules )
	$optCategory[$category]	= $category.' ('.$nrModules.')';
$optCategory	= UI_HTML_Elements::Options( $optCategory, $filterCategory );

$optModuleId	= array( '' => '- wähle -' );
foreach( $filteredModules as $moduleId => $module )
	$optModuleId[$moduleId]	= $module->title.' ('.count( $module->config ).')';
$optModuleId	= UI_HTML_Elements::Options( $optModuleId, $filterModuleId );

return '
<div class="content-panel content-panel-form content-panel-filter">
	<h3>'.$w->heading. '</h3>
	<div class="content-panel-inner">
		<form action="./admin/config/filter" method="post">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_category">'.$w->labelCategory.'</label>
					<select name="category" id="input_category" class="span12 has-optionals" onchange="this.form.submit();">'.$optCategory.'</select>
				</div>
			</div>
			<div class="row-fluid" style="'.( !$filterCategory ? 'display: none' : '' ).'">
				<div class="span12">
					<label for="input_moduleId">'.$w->labelModuleId.'</label>
					<select name="moduleId" id="input_moduleId" class="span12" onchange="this.form.submit();">'.$optModuleId.'</select>
				</div>
			</div>
			<div class="buttonbar">
				<div class="btn-group">
					<button type="submit" name="search" class="btn btn-small btn-info">'.$iconSearch.'&nbsp;'.$w->buttonSearch.'</button>
					<a href="./admin/config/filter/reset" class="btn btn-small btn-inverse">'.$iconReset.'&nbsp;'.$w->buttonReset.'</a>
				</div>
			</div>
		</form>
	</div>
</div>';
