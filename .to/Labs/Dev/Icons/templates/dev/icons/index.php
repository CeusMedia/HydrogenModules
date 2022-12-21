<?php

use CeusMedia\Common\FS\File\RegexFilter as RegexFileFilter;
use CeusMedia\Common\FS\Folder\Lister as FolderLister;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$baseUri	= 'http://localhost/';
$basePath	= '/var/www/';

$selected	= $env->getRequest()->get( 'project' );

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

$list	= [];
foreach( $projects as $path => $label ){
	$class	= $path == $selected ? 'active' : NULL;
	$link	= HtmlTag::create( 'a', $label, ['href' => './dev/icons/?project='.urlencode( $path )] );
	$list[]	= HtmlTag::create( 'li', $link, ['class' => $class] );
}
$list	= HtmlTag::create( 'ul', $list );

$panelFilter	= '
<fieldset>
	<legend>...</legend>
	'.$list.'
</fieldset>
';

	$skip	= ['animations'];


function listFolder( $path, $uri, $skip = [] ){
	$folders	= FolderLister::getFolderList( $path );
	$list		= [];
	foreach( $folders as $folder ){
		if( in_array( $folder->getFilename(), $skip ) )
			continue;
		$label	= $folder->getFilename();
		$inner	= listFolder( $folder->getPathname(), $uri.$label.'/', $skip );
		$list[$label]	= '<fieldset><legend>'.$label.'</legend>'.$inner.'</fieldset>';
	}
	ksort( $list );
	$list	= array_values( $list );
	foreach( new RegexFileFilter( $path, "/(png|gif|ico|svg)$/i" ) as $file ){
		$url		= $uri.$file->getFilename();
		$label		= $file->getFilename();
		$icon		= HtmlTag::create( 'img', NULL, ['src' => $url, 'title' => $label, 'alt' => $label] );
#		$label		= HtmlTag::create( 'div', $file->getFilename(), ['class' => 'label'] );
		$list[$label]	= HtmlTag::create( 'div', $icon, ['class' => 'item'] );
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
