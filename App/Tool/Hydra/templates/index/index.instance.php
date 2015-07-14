<?php
$panelConfig			= $view->loadTemplate( 'index', 'index.config' );						//  @todo test
$panelModulesUpdatable	= $view->loadTemplate( 'index', 'index.module.updates' );
$panelModulesInstalled	= $view->loadTemplate( 'index', 'index.module.installed' );

/*  --  LIST: MODULES INSTALLED  --  */

/*  --  LIST: MODULES MISSING  --  */
$listModulesMissing	= array();
foreach( $modulesMissing as $moduleId ){
	$label	= $title	= $moduleId;
	if( array_key_exists( $moduleId, $modulesAll ) ){
		$descLines	= explode( "\n", $modulesAll[$moduleId]->description );
		$descFirst	= addslashes( trim( array_shift( $descLines ) ) );
		$title		= $modulesAll[$moduleId]->title;
		$label		= $descFirst ? '<acronym title="'.$descFirst.'">'.$title.'</acronym>' : $title;
	}
	$label	= '<span class="module">'.$label.'</span>';
	$link	= '<a href="./admin/module/viewer/index/'.$moduleId.'">'.$label.'</a>';
	$listModulesMissing[$title]	= '<li>'.$link.'</li>';	
}

/*  --  LIST: MODULES POSSIBLE  --  */
$listModulesPossible	= array();
foreach( $modulesPossible as $moduleId ){
	$label	= $title	= $moduleId;
	if( array_key_exists( $moduleId, $modulesAll ) ){
		$descLines	= explode( "\n", $modulesAll[$moduleId]->description );
		$descFirst	= addslashes( trim( array_shift( $descLines ) ) );
		$title		= $modulesAll[$moduleId]->title;
		$label		= $descFirst ? '<acronym title="'.$descFirst.'">'.$title.'</acronym>' : $title;
	}
	$label	= '<span class="module">'.$label.'</span>';
	$link	= '<a href="./admin/module/viewer/index/'.$moduleId.'">'.$label.'</a>';
	$listModulesPossible[$title]	= '<li>'.$link.'</li>';	
}
natcasesort( $listModulesMissing );
natcasesort( $listModulesPossible );

$panelModulesRelated	= "";
$sumReleations	= count( $listModulesMissing ) + count( $listModulesPossible );
if( 1 || $sumReleations ){
	$list	= array();
	if( $listModulesMissing )
		$list[]	= '<dt>Fehlen</dt><dd><ul>'.join( $listModulesMissing ).'</ul></dd>';
	if( $listModulesPossible )
		$list[]	= '<dt>Unterstützt</dt><dd><ul>'.join( $listModulesPossible ).'</ul></dd>';

	$panelModulesRelated	= '
<fieldset style="position: relative">
	<legend class="info">Modulbeziehungen <span class="small">('.$sumReleations.')</span></legend>
	<div style="max-height: 160px; overflow: auto">
		<dl>'.join( $list ).'</dl>
	</div>
</fieldset>';
}


$panelModules	= '
<fieldset style="position: relative">
	<legend class="info">Module</legend>
<!--	<div style="position: absolute; right: 8px; top: 16px;">
		'.UI_HTML_Elements::LinkButton( './admin/module/installer', '', 'button tiny icon add' ).'
	</div>
	<div style="max-height: 160px; overflow: auto">-->
		<ul>
			<li>'.count( $modulesInstalled ).' installiert</li>
			<li>'.count( $modulesMissing ).' fehlen</li>
			<li>'.count( $modulesPossible ).' möglich</li>
			<li>'.count( $modulesUpdate ).' aktualisierbar</li>
</ul>
<!--	</div>
--></fieldset>';

$panelInfo	= $view->loadTemplate( 'index', 'index.info' );

$panelGraph	= '';
if( $modulesInstalled )
	$panelGraph	= ';
	<fieldset>
		<legend>Graph der Module der Instanz</legend>
		<div style="overflow: auto; width: 100%">
			<a href="./index/showInstanceModuleGraph/'.$instanceId.'">
				<img style="max-width: 100%" src="./index/showInstanceModuleGraph/'.$instanceId.'" type="image/svg+xml" />
			</a>
		</div>
	</fieldset>';
	
return '
<script>
$(document).ready(function(){
	$(".index-config-module a").bind("click",function(event){event.stopPropagation();});
	$(".index-config-module").bind("click",function(){$(this).next().slideToggle();});
});
</script>
<br/>
<div class="index">
	<div class="column-left-25">
		'.$panelList.'
		'.$panelSystem.'
	</div>
	<div class="column-right-75">
		<div class="column-left-66">
			'.$panelInfo.'
			'.$panelConfig.'
		</div>
		<div class="column-right-33">
			'.$panelModules.'
			'.$panelModulesUpdatable.'
			'.$panelModulesInstalled.'
			'.$panelModulesRelated.'
		</div>
		<div class="column-clear"></div>
	</div>
	<div class="column-clear"></div>
	'.$panelGraph.'
</div>';
?>
