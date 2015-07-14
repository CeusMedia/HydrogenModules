<?php
if( !$instance->configPath )
	$instance->configPath   = 'config/';
if( !$instance->configFile )
	$instance->configFile   = 'config.ini';

$fileConfig	= $instance->uri.$instance->configPath.$instance->configFile;
if( file_exists( $fileConfig ) ){
	$config	= new File_INI_Reader( $fileConfig, FALSE );
	foreach( $config->getProperties() as $key => $value ){
		$comment	= $config->getComment( $key );
		$cellKey	= UI_HTML_Tag::create( 'td', $key );
		if( is_bool( $value ) )
			$value	= $value ? "<em>TRUE</em>" : "<em>FALSE</em>";
		$cellValue	= UI_HTML_Tag::create( 'td', $value );
		$list[]		= UI_HTML_Tag::create( 'tr', $cellKey.$cellValue );
	}
	$tbody	= UI_HTML_Tag::create( 'tbody', $list, array( "style" => "max-height: 200px; overflow: auto" ) );
	$table	= UI_HTML_Tag::create( 'table', $tbody );
	return '
<fieldset>
	<legend>Conf</legend>
	'.$table.'
	<em><small class="muted">This functionality is in development and not in any final state.</small></em>
</fieldset>';
}
return "";
?>
