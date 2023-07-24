<?php
use CeusMedia\Common\FS\File\INI\Reader as IniFileReader;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

if( !$instance->configPath )
	$instance->configPath   = 'config/';
if( !$instance->configFile )
	$instance->configFile   = 'config.ini';

$fileConfig	= $instance->uri.$instance->configPath.$instance->configFile;
if( file_exists( $fileConfig ) ){
	$config	= new IniFileReader( $fileConfig, FALSE );
	foreach( $config->getProperties() as $key => $value ){
		$comment	= $config->getComment( $key );
		$cellKey	= HtmlTag::create( 'td', $key );
		if( is_bool( $value ) )
			$value	= $value ? "<em>TRUE</em>" : "<em>FALSE</em>";
		$cellValue	= HtmlTag::create( 'td', $value );
		$list[]		= HtmlTag::create( 'tr', $cellKey.$cellValue );
	}
	$tbody	= HtmlTag::create( 'tbody', $list, ["style" => "max-height: 200px; overflow: auto"] );
	$table	= HtmlTag::create( 'table', $tbody );
	return '
<fieldset>
	<legend>Conf</legend>
	'.$table.'
	<em><small class="muted">This functionality is in development and not in any final state.</small></em>
</fieldset>';
}
return "";
?>
