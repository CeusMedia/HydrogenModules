<?php
if( !in_array( 'upload', $rights ) )
	return '';
return '
<div class="content-panel">
	<h4>'.$words['upload']['heading'].'</h4>
	<div class="content-panel-inner">
		<form action="./info/file/upload/'.$folderId.'" method="post" enctype="multipart/form-data">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_upload">'.$words['upload']['labelFile'].'</label>
					<input type="file" name="upload" id="input_upload"/>
				</div>
			</div>
			<div class="buttonbar">
				<button type="submit" name="save" class="btn btn-small btn-success"><i class="icon-plus icon-white"></i> '.$words['upload']['buttonSave'].'</button>
			</div>
		</form>
	</div>
</div>';
?>
