<?php
$w			= (object) $words['add'];

$optFile	= array( '' => '' );
foreach( $files as $file )
	$optFile[$file->fileName]	= $file->fileName;
$optFile	= UI_HTML_Elements::Options( $optFile );

$optClass	= array( '' => '' );
foreach( $classes as $class )
	$optClass[$class]	= $class;
$optClass	= UI_HTML_Elements::Options( $optClass );

$optStatus	= UI_HTML_Elements::Options( $words['states'], 1 );
return '
<!-- templates/admin/mail/attachment/add.php -->
<div class="content-panel content-panel-form">
	<div class="content-panel-inner">
		<h3>'.$w->heading.'</h3>
		<form action="./admin/mail/attachment/add" metod="post">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_file" class="mandatory required">'.$w->labelFile.'</label>
					<select id="input_file" name="file" class="span12" required="required">'.$optFile.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_class" class="mandatory required">'.$w->labelClass.'</label>
					<select id="input_class" name="class" class="span12" required="required">'.$optClass.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span4">
					<label for="input_status">'.$w->labelStatus.'</label>
					<select id="input_status" name="status" class="span12">'.$optStatus.'</select>
				</div>
			</div>
			<div class="buttonbar">
				<button type="submit" name="add" class="btn btn-success"><i class="icon-ok icon-white"></i>&nbsp;'.$w->buttonSave.'</button>
			</div>
		</form>
	</div>
</div>
';
?>
