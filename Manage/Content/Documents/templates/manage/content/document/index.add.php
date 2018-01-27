<?php

$w		= (object) $words['add'];

$iconUpload     = UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-folder-open icon-white' ) );
$iconSave		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-plus icon-white' ) );
if( $env->getModules()->get( 'UI_Font_FontAwesome' ) ){
	$iconUpload		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-folder-open' ) );
	$iconSave		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );
}

$fieldFilename	= '';
if( count( $documents ) ){
	$optFilename	= array( '' => '' );
	foreach( $documents as $entry )
		$optFilename[$entry]	= $entry;
	$optFilename	= UI_HTML_Elements::Options( $optFilename );
	$fieldFilename	= '
	<div class="row-fluid">
		<div class="span12">
			<label for="input_filename">'.$w->labelFilename.'</label>
			<select name="filename" id="input_filename" class="span12">'.$optFilename.'</select>
		</div>
	</div>';
}

if( !in_array( 'add', $rights ) )
	return;
return '
<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<form action="./manage/content/document/add" method="post" enctype="multipart/form-data">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_upload">'.$w->labelFile.'</label>
					'.View_Helper_Input_File::renderStatic( $env, 'upload', $iconUpload, TRUE ).'
				</div>
			</div>
			'.$fieldFilename.'
			<div class="buttonbar">
				<button type="submit" name="save" class="btn btn-small btn-primary">'.$iconSave.' '.$w->buttonSave.'</button>
			</div>
		</form>
	</div>
</div>';
?>
