<?php

$w		= (object) $words['add'];

$optFilename	= array( '' => '' );
foreach( $documents as $entry )
	$optFilename[$entry]	= $entry;
$optFilename	= UI_HTML_Elements::Options( $optFilename );

$iconUpload     = UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-folder-open icon-white' ) );

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
					'.View_Helper_Input_File::render( 'upload', $iconUpload, TRUE ).'
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_filename">'.$w->labelFilename.'</label>
					<select name="filename" id="input_filename" class="span12">'.$optFilename.'</select>
				</div>
			</div>
			<div class="buttonbar">
				<button type="submit" name="save" class="btn btn-small btn-primary"><i class="icon-plus icon-white"></i> '.$w->buttonSave.'</button>
			</div>
		</form>
	</div>
</div>';
?>
