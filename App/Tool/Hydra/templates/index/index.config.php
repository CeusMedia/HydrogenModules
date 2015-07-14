<?php
if( empty( $remoteConfig ) )
	return "";
$listModules	= array();
foreach( $modulesInstalled as $module ){
	$list	= array();
	foreach( $module->config as $item ){
		if( preg_match( '/password|secret/', $item->key ) )
			$item->value	= str_repeat( '*', strlen( $item->value ) );
		$value	= $item->value;
		switch( $item->type ){
			case 'boolean':
			case 'bool':
				$value	= '<em style="color: #444">'.( ( (bool) $value ) ? "yes" : "no" ).'</em>';
				break;
			case 'integer':
			case 'int':
			case 'float':
				$value	= '<span style="font-family: monospace; font-size: 1.2em;">'.$value.'</span>';
				break;
			default:
				$value	= strlen( trim( $value ) ) ? htmlentities( $value ) : '&empty;';
		}
		$list[$item->key]	= '<dt>'.$item->key.'</dt><dd>'.$value.'</dd>';
	}
	natcasesort( $list );
	if( $list ){
		$url		= './admin/module/editor/index/'.$module->id;
		$button		= UI_HTML_Elements::LinkButton( $url, '', 'button tiny edit' );
		$button		= UI_HTML_Tag::create( 'div', $button, array( 'style' => "position: absolute; right: 3px; top: 1px;" ) );
		$list		= UI_HTML_Tag::create( 'dl', $list, array( 'class' => 'index-config' ) );
		$url		= './admin/module/viewer/index/'.$module->id;
		$link		= UI_HTML_Tag::create( 'a', $module->title, array( 'href' => $url, 'class' => 'module' ) );
		$heading	= UI_HTML_Tag::create( 'h4', $link/*.$button*/, array( 'class' => 'index-config-module' ) );
		$listModules[]	= $heading.$list;
	}
}


$panel	= '
<fieldset>
	<legend class="info">Konfiguration</legend>
	<div style="max-height: 320px; overflow: auto;">
		'.join( $listModules ).'
	</div>
</fieldset>';
$env->clock->profiler->tick( 'Template: index/index - config' );
return empty( $listModules ) ? "" : $panel;
?>
