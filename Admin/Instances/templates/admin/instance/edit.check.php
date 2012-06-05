<?php

$list	= array();

if( !preg_match( '/^\//', $instance->path ) )
	$instance->path	= getEnv( 'DOCUMENT_ROOT' ).'/'.$instance->path;

if( !$instance->configPath )
	$instance->configPath	= 'config/';
if( !$instance->configFile )
	$instance->configFile	= 'config.ini';


$pathConfig	= '';
$fileConfig	= $instance->path.$instance->configPath.$instance->configFile;

#print_m( $instance );
#die;

$iconStatus0	= UI_HTML_Elements::Image( 'http://localhost/lib/cmIcons/famfamfam/silk/error.png', '' );
$iconStatus1	= UI_HTML_Elements::Image( 'http://localhost/lib/cmIcons/famfamfam/silk/cross.png', '' );
$iconStatus2	= UI_HTML_Elements::Image( 'http://localhost/lib/cmIcons/famfamfam/silk/tick.png', '' );

//  --  CHECK: INSTANCE PATH  --  //
$status	= 1;
$hint	= 'Der Instanzordner existiert nicht.';
if( file_exists( $instance->path ) ){
	$status	= 2;
	$hint	= 'OK';
}
$icon	= UI_HTML_Tag::create( 'acronym', ${'iconStatus'.$status}, array( 'title' => $hint ) );
$buttonCreate	= UI_HTML_Elements::LinkButton( './admin/instance/createPath/'.$instance->id, 'erzeugen', 'button add create', NULL, $status != 1 );
$list[]	= '<tr class="status-'.$status.'"><td>Instanzordner</td><td>'.$instance->path.'</td><td>'.$icon.'</td><td>'.$buttonCreate.'</td></tr>';

//  --  CHECK: CONFIG PATH  --  //
$status	= 0;
$hint	= 'Der Instanzordner muss vorher erstellt werden.';
if( file_exists( $instance->path ) ){
	$status	= 1;
	$hint	= 'Der Konfigurationsordner existiert nicht.';
	if( file_exists( $instance->path.$instance->configPath ) ){
		$status = 2;
		$hint	= 'OK';
	}
}
$icon	= UI_HTML_Tag::create( 'acronym', ${'iconStatus'.$status}, array( 'title' => $hint ) );
$buttonCreate	= UI_HTML_Elements::LinkButton( './admin/instance/createPath/'.$instance->id.'/'.base64_encode( $instance->configPath ), 'erzeugen', 'button add create', NULL, $status != 1 );
$list[]	= '<tr class="status-'.$status.'"><td>Konfigurationsordner</td><td>'.$instance->configPath.'</td><td>'.$icon.'</td><td>'.$buttonCreate.'</td></tr>';

//  --  CHECK: CONFIG FILE  --  //
$status	= 0;
$hint	= 'Der Konfigurationsordner muss vorher erstellt werden.';
if( file_exists( $instance->path.$instance->configPath ) ){
	$status	= 1;
	$hint	= 'Die Konfigurationsdatei existiert nicht.';
	if( file_exists( $fileConfig ) ){
		$status	= 2;
		$hint	= 'OK';
	}
}
$icon	= UI_HTML_Tag::create( 'acronym', ${'iconStatus'.$status}, array( 'title' => $hint ) );
$buttonCreate	= UI_HTML_Elements::LinkButton( './admin/instance/createConfig/'.$instance->id, 'erzeugen', 'button add create', NULL, $status != 1 );
$list[]	= '<tr class="status-'.$status.'"><td>Konfigurationdatei</td><td>'.$instance->configPath.$instance->configFile.'</td><td>'.$icon.'</td><td>'.$buttonCreate.'</td></tr>';


//  --  CHECK: TEMPLATE FOLDER  --  //
$status	= 0;
$hint	= 'Die Konfigurationsdatei muss vorher erstellt werden.';
$path	= '';
if( file_exists( $fileConfig ) ){
	$config	= parse_ini_file( $fileConfig, FALSE );
	$hint	= 'Der Template-Ordner ist nicht konfiguriert.';
	if( !empty( $config['path.templates'] ) ){
		$path	= $config['path.templates'];
		$status	= 1;
		$hint	= 'Der Template-Ordner existiert nicht.';
		if( file_exists( $instance->path.$config['path.templates'] ) ){
			$status	= 2;
			$hint	= 'OK';
		}
	}
}
$icon	= UI_HTML_Tag::create( 'acronym', ${'iconStatus'.$status}, array( 'title' => $hint ) );
$buttonCreate	= UI_HTML_Elements::LinkButton( './admin/instance/createPath/'.$instance->id.'/'.base64_encode( $path ), 'erzeugen', 'button add create', NULL, $status != 1 );
$list[]	= '<tr class="status-'.$status.'"><td>Template-Ordner</td><td>'.$path.'</td><td>'.$icon.'</td><td>'.$buttonCreate.'</td></tr>';

$panelCheck	= '
<style>
li.database-status-1,
li.database-status-0 {
	background-repeat: no-repeat;
	padding: 2px 6px 2px 26px;
	background-position: 4px 2px;
	}
li.database-status-1 {
	background-image: url(http://localhost/lib/cmIcons/famfamfam/silk/tick.png);
	background-color: #DFFFDF;
	border: 1px solid #9FDF9F;
	}
li.database-status-0 {
	background-image: url(http://localhost/lib/cmIcons/famfamfam/silk/error.png);
	background-color: #FFDFDF;
	border: 1px solid #DF9F9F;
	}

tr.status-2 {
	background-color: #DFFFDF;
	}
tr.status-1 {
	background-color: #FFDFDF;
	}
tr.status-0 {
	background-color: #FFFFDF;
	}
</style>
<fieldset>
	<legend>Grundinstallation</legend>
	<table class="list">
		<tr><th>...</th><th>...</th><th>Status</th><th>Aktion</th></tr>
		'.join( $list ).'
	</table>
</fieldset>	
';

return $panelCheck;
?>