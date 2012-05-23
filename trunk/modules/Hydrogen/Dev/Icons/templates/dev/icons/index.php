<?php

$baseUri	= 'http://localhost/';
$basePath	= '/var/www/';

$selected	= $this->env->getRequest()->get( 'project' );

$projects	= array(
	'lib/cmIcons/tango/'						=> 'Tango',
	'lib/cmIcons/famfamfam/mini/'				=> 'famfamfam mini',
	'lib/cmIcons/famfamfam/silk/'				=> 'famfamfam silk',
	'lib/cmIcons/iconbase/aero/'				=> 'iconbase aero',
	'lib/cmIcons/iconbase/docunium/'			=> 'iconbase docunium',
	'lib/cmIcons/dryicons/classy/'				=> 'dryicons classy',
	'lib/cmIcons/dryicons/handy/'				=> 'dryicons handy',
	'lib/cmIcons/dryicons/colorful-stickers/'	=> 'dryicons colorful-stickers',
	'lib/cmIcons/sublink/SweetiePlus-v2/'		=> 'SweetiePlus',
);

$list	= array();
foreach( $projects as $path => $label ){
	$class	= $path == $selected ? 'active' : NULL;
	$link	= UI_HTML_Tag::create( 'a', $label, array( 'href' => './dev/icons/?project='.urlencode( $path ) ) );
	$list[]	= UI_HTML_Tag::create( 'li', $link, array( 'class' => $class ) );
}
$list	= UI_HTML_Tag::create( 'ul', $list );

$panelFilter	= '
<fieldset>
	<legend>...</legend>
	'.$list.'
</fieldset>
';

	$skip	= array( 'animations' );


function listFolder( $path, $uri, $skip = array() ){
	$folders	= Folder_Lister::getFolderList( $path );
	$list		= array();
	foreach( $folders as $folder ){
		if( in_array( $folder->getFilename(), $skip ) )
			continue;
		$label	= $folder->getFilename();
		$inner	= listFolder( $folder->getPathname(), $uri.$label.'/', $skip );
		$list[$label]	= '<fieldset><legend>'.$label.'</legend>'.$inner.'</fieldset>';
	}
	ksort( $list );
	$list	= array_values( $list );
	foreach( new File_RegexFilter( $path, "/(png|gif|ico|svg)$/i" ) as $file ){
		$url		= $uri.$file->getFilename();
		$label		= $file->getFilename();
		$icon		= UI_HTML_Tag::create( 'img', NULL, array( 'src' => $url, 'title' => $label, 'alt' => $label ) );
#		$label		= UI_HTML_Tag::create( 'div', $file->getFilename(), array( 'class' => 'label' ) );
		$list[$label]	= UI_HTML_Tag::create( 'div', $icon, array( 'class' => 'item' ) );
	}
	ksort( $list );
	return join( $list );
}	



$list	= '';
if( $selected ){
	$list	= listFolder( $basePath.$selected, $baseUri.$selected, $skip );
}

return '
<style>
fieldset {
	border: 2px 0px 0px 0px;
	clear: left;
	}
div.item {
	display: block;
	float: left;
	min-width: 40px;
	min-height: 40x;
	margin: 2px;
	border: 2px solid white;
	border-radius: 5px;
	text-align: center;
	}
div.item:hover {
	border-color: green;
	}
div.item:active {
	border-color: red;
	background-color: black;
	color: white;
	}
div.item img {
	margin-top: 15px;
	}
div.label {
	font-size: 0.9em;
	line-height: 1em;
	}
</style>
<div class="column-left-20">
	'.$panelFilter.'
</div>
<div class="column-left-80">
	'.$list.'
</div>
<div class="column-clear"></div>
';
?>
