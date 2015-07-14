<?php

$w		= (object) $words['add'];

$optFilename	= array( '' => '' );
foreach( $documents as $entry )
	$optFilename[$entry]	= $entry;
$optFilename	= UI_HTML_Elements::Options( $optFilename );

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
					<input type="file" name="upload" id="input_upload"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_filename">'.$w->labelFilename.'</label>
					<select name="filename" id="input_filename">'.$optFilename.'</select>
				</div>
				<div class="buttonbar">
					<button type="submit" name="save" class="btn btn-small btn-success"><i class="icon-plus icon-white"></i> '.$w->buttonSave.'</button>
				</div>
			</div>
		</form>
	</div>
</div>';
?>
