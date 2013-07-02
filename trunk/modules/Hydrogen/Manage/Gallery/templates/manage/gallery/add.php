<?php

$buttonCancel	= UI_HTML_Elements::LinkButton( './gallery/'.$folder, 'zurück', 'button cancel' );
$buttonAdd	= UI_HTML_Elements::Button( 'add', 'hinzufügen', 'button add' );


$optPrefix	= array();
$optPrefix	= UI_HTML_Elements::Options( $optPrefix );

$panelImport	= '';

#print_m( $config->getAll( 'module.gallery_compact.' ) );
$path	= $config->get( 'path.images' ).$config->get( 'module.gallery_compact.path' );


$model		= new Model_Gallery( $env );
$galleries	= $model->getAll();
$galleryMap	= array();
foreach( $galleries as $gallery ){
	$galleryMap[$gallery->folder]	= $gallery;
}


$index	= Folder_RecursiveLister::getFolderList( $path );
$list	= array();
foreach( $index as $entry ){
	$key	= substr( $entry->getPathname(), strlen( $path ) );
	if( !array_key_exists( $key, $galleryMap ) )
		$list[]	= $key;
}
asort( $list );
#	print_m( array_keys( $galleryMap ) );
#	print_m( $list );
#	die;

if( $list ){
	foreach( $list as $nr => $entry ){
		$url		= './gallery/add/?folder='.$entry.'&title='.basename( $entry );
		$link		= UI_HTML_Tag::create( 'a', $entry, array( 'href' => $url ) );
		$list[$nr]	= UI_HTML_Tag::create( 'li', $link );
	}
	$list	= UI_HTML_Tag::create( 'ul', join( $list ) );
	$panelImport	= '
<fieldset>
	<legend class="icon import">Import</legend>
	'.$list.'
</fieldset>';
	
}


return '
<h3>Gallery::add</h3>
	
<form name="form_gallery-add" action="./gallery/add" method="post">
	<fieldset>
		<legend></legend>
		<ul class="input">
			<li class="column-left-30">
				<label for="input_folder">Ordner</label><br/>
				<input type="text" name="folder" id="input_folder" class="max mandatory" value="'.htmlentities( $request->get( 'folder' ) ).'"/>
			</li>
			<li class="column-left-10">
				<label for="input_prefix">Präfix</label><br/>
				<select name="prefix" id="input_prefix" class="max">'.$optPrefix.'</select>
			</li>
			<li class="column-left-60">
				<label for="input_title">Titel</label><br/>
				<input type="text" name="title" id="input_title" class="max" value="'.htmlentities( $request->get( 'title' ) ).'"/>
			</li>
			<li class="column-clear">
				<label for="input_content">Beschreibung</label><br/>
				<textarea type="text" name="content" id="input_content" class="max"></textarea>
			</li>
		</ul>
		<div class="buttonbar">
			'.$buttonCancel.$buttonAdd.'
		</div>
	</fieldset>
</form>
'.$panelImport.'
';
?>