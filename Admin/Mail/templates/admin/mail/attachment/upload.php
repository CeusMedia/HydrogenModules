<?php
$w	= (object) $words['upload'];

$iconUpload	= new UI_HTML_Tag( 'i', '', array( 'class' => 'icon-folder-open icon-white' ) );

extract( $view->populateTexts( array( 'top', 'bottom' ), 'html/admin/mail/attachment/' ) );

$panelFiles		= $this->loadTemplateFile( 'admin/mail/attachment/index.files.php' );

$tabs	= View_Admin_Mail_Attachment::renderTabs( $env, 'upload' );

$maxSize	= Alg_UnitFormater::formatBytes( min( Logic_Upload::getMaxUploadSize() ) );

return $tabs.$textTop.'
<div class="row-fluid">
	<div class="span6">
		<div class="content-panel content-panel-form">
			<div class="content-panel-inner">
				<h3>'.$w->heading.'</h3>
				<form action="./admin/mail/attachment/upload" method="post" enctype="multipart/form-data">
					<div class="row-fluid">
						<div class="span12">
							<label for="input_file">'.$w->labelFile.'</label>
							'.View_Helper_Input_File::render( 'file', $iconUpload, TRUE ).'
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<div class="hint">
								<small><em class="muted">'.sprintf( $w->hintMaxSize, $maxSize ).'</em></small>
							</div>
							<div class="hint">
								<small><em class="muted">'.$w->hintMimeType.'</em></small>
							</div>
						</div>
					</div>
					<div class="buttonbar">
						<button type="submit" name="upload" class="btn btn-primary"><i class="icon-ok icon-white"></i>&nbsp;'.$w->buttonSave.'</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	<div class="span6">
		'.$panelFiles.'
	</div>
</div>
'.$textBottom;
?>
