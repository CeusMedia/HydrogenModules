<?php
$tabs	= View_Work_Mail_Check::renderTabs( $env, 'import' );

if( !empty( $type ) ){
	$optColumn	= array();
	foreach( $columns as $column )
		$optColumn[$column]	= $column;
	$optColumn	= UI_HTML_Elements::Options( $optColumn, $columns[0] );

	return $tabs.'
<div class="content-panel">
	<h3>Import (2/2)</h3>
	<div class="content-panel-inner">
		<form action="./work/mail/check/import" method="post" enctype="multipart/form-data">
			<div class="row-fluid">
				<div class="span9">
					<div class="row-fluid">
						<div class="span6">
							<label for="input_file">Dateiname</label>
							<input type="text" name="file" id="input_file" value="'.htmlentities( $name, ENT_QUOTES, 'UTF-8' ).'" class="span12" disabled="disabled"/>
						</div>
						<div class="span3">
							<label for="input_format">Format</label>
							<input type="text" name="format" id="input_format" value="'.$type.'" class="span12" disabled="disabled"/>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span6">
							<label for="input_group">Gruppe</label>
							<input type="text" name="group" id="input_group" value="" class="span12"/>
						</div>
						<div class="span3">
							<label for="input_size">Dateigröße</label>
							<input type="text" name="size" id="input_size" value="'.Alg_UnitFormater::formatBytes( $size ).'" class="span12" disabled="disabled"/>
						</div>
						<div class="span3">
							<label for="input_count">Einträge</label>
							<input type="text" name="count" id="input_count" value="'.$count.'" class="span12" disabled="disabled"/>
						</div>
					</div>
				</div>
				<div class="span3">
					<label for="input_column">E-Mail-Spalte</label>
					<select name="column" id="input_column" class="span12" size="4">'.$optColumn.'</select>
				</div>
			</div>
			<div class="buttonbar">
				<a href="./work/mail/check/import/abort" class="btn btn-small"><i class="fa fa-fw fa-arrow-left"></i>&nbsp;abbrechen</a>
				<button type="submit" name="save" class="btn btn-primary"><i class="icon-upload icon-white"></i>&nbsp;hochladen</button>
			</div>
		</form>
	</div>
</div>';
}
else{

return $tabs.'
<div class="row-fluid">
	<div class="span6">
		<div class="content-panel">
			<h3>Import (1/2)</h3>
			<div class="content-panel-inner">
				<form action="./work/mail/check/import" method="post" enctype="multipart/form-data">
					'.View_Helper_Input_File::render( 'file', 'Datei', TRUE ).'
					<div class="buttonbar">
						<button type="submit" name="save" class="btn btn-primary"><i class="icon-upload icon-white"></i>&nbsp;hochladen</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>';
}
?>
