<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

if( empty( $modulesUpdate ) )
	return "";

$listModulesUpdatable	= [];
$listMessenger			= [];
foreach( $modulesUpdate as $module ){
	$desc		= explode( "\n", $module->description );
	$desc		= trim( array_shift( $desc ) );

	$label		= $desc ? '<acronym title="'.$desc.'">'.$module->title.'</acronym>' : $module->title;
	$label		= '<span class="module">'.$label.'</span>';

	$attributes	= array( 'href' => './admin/module/viewer/index/'.$module->id );
	$link		= HtmlTag::create( 'a', $module->title, $attributes );

	$versions	= '<span class="muted" style="float: right">'.$module->versionInstalled.'&nbsp;&rArr;&nbsp;'.$module->versionAvailable.'</span>';

	$listMessenger[]	= $link;
	$listModulesUpdatable[$module->title]	= HtmlTag::create( 'li', $link.'&nbsp;'.$versions );
}
ksort( $listModulesUpdatable );
if( $listMessenger )
	$view->env->getMessenger()->noteNotice( 'Aktualisierung verfügbar für: '.join( ", ", $listMessenger ) );
$panel	= '
<fieldset style="position: relative">
	<legend class="info">Module aktualisierbar <span class="small">('.count( $listModulesUpdatable ).')</span></legend>
	<div style="max-height: 160px; overflow: auto">
		<ul>'.join( $listModulesUpdatable ).'</ul>
	</div>
</fieldset>';
$env->getRuntime()->reach( 'Template: index/index - updates' );
return $panel;
?>
