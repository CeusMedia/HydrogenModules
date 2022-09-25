<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

if( empty( $remoteConfig ) )
	return "";
$listModules	= [];
foreach( $modulesInstalled as $module ){
	$list	= [];
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
		$button		= HtmlElements::LinkButton( $url, '', 'button tiny edit' );
		$button		= HtmlTag::create( 'div', $button, array( 'style' => "position: absolute; right: 3px; top: 1px;" ) );
		$list		= HtmlTag::create( 'dl', $list, array( 'class' => 'index-config' ) );
		$url		= './admin/module/viewer/index/'.$module->id;
		$link		= HtmlTag::create( 'a', $module->title, array( 'href' => $url, 'class' => 'module' ) );
		$heading	= HtmlTag::create( 'h4', $link/*.$button*/, array( 'class' => 'index-config-module' ) );
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
$env->getRuntime()->reach( 'Template: index/index - config' );
return empty( $listModules ) ? "" : $panel;
?>
