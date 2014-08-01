<?php
$w	= (object) $words['upload'];
return '
<!-- templates/admin/mail/attachment/upload.php -->
<div class="content-panel content-panel-form">
    <div class="content-panel-inner">
		<h3>'.$w->heading.'</h3>
		<form action="./admin/mail/attachment/upload" method="post" enctype="multipart/form-data">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_file">'.$w->labelFile.'</label>
					<input type="file" name="file"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<div class="hint">
						<small><em class="muted">'.$w->hintMimeType.'</em></small>
					</div>
				</div>
			</div>
			<div class="buttonbar">
				<button type="submit" name="add" class="btn btn-primary"><i class="icon-ok icon-white"></i>&nbsp;'.$w->buttonSave.'</button>
			</div>
		</form>
	</div>
</div>
';
?>
