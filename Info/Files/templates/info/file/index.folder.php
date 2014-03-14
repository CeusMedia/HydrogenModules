<?php
if( !in_array( 'addFolder', $rights ) )
	return '';
return '
<h4>'.$words['addFolder']['heading'].'</h4>
<form action="./info/file/addFolder/'.$folderId.'" method="post">
	<div class="row-fluid">
		<div class="span12">
			<label for="input_folder">'.$words['addFolder']['labelFolder'].'</label>
			<input type="text" name="folder" id="input_folder" required="required" value="'.$request->get( 'input_folder' ).'"/>
		</div>
	</div>
	<div class="buttonbar">
		<button type="submit" name="save" class="btn btn-small btn-success"><i class="icon-plus icon-white"></i> '.$words['addFolder']['buttonSave'].'</button>
	</div>
</form>';
?>