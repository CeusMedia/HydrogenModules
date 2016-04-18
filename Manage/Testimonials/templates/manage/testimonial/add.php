<?php
$w		= (object) $words['add'];

$optStatus	= UI_HTML_Elements::Options( $words['states'], $testimonial->status );

$panelContent = '
<div class="content-panel content-panel-form">
	<h3>Neuer Eintrag</h3>
	<div class="content-panel-inner">
		<form action="./manage/testimonial/add" method="post">
			<div class="row-fluid">
				<div class="span3">
					<label for="input_username">'.$w->labelAuthor.' <small class="muted">'.$w->labelAuthor_suffix.'</small></label>
					<input type="text" name="username" id="input_username" class="span12" value="'.htmlentities( $testimonial->username, ENT_QUOTES, 'UTF-8' ).'" required="required"/>
				</div>
				<div class="span3">
					<label for="input_email">'.$w->labelEmail.' <small class="muted">'.$w->labelEmail_suffix.'</small></label>
					<input type="text" name="email" id="input_email" class="span12" value="'.htmlentities( $testimonial->email, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span3">
					<label for="input_status">'.$w->labelStatus.'</label>
					<select name="status" id="input_status" class="span12">'.$optStatus.'</select>
				</div>
				<div class="span1">
					<label for="input_rank">'.$w->labelRang.'</label>
					<input type="text" name="rank" id="input_rank" class="span12" value="'.htmlentities( $testimonial->rank, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_title">'.$w->labelTitle.'</label>
					<input type="text" name="title" id="input_title" class="span12" value="'.htmlentities( $testimonial->title, ENT_QUOTES, 'UTF-8' ).'" required/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_description">'.$w->labelDescription.'</label>
					<textarea name="description" id="input_description" class="span12 TinyMCE" data-tinymce-mode="minimal" rows="12">'.htmlentities( $testimonial->description, ENT_QUOTES, 'UTF-8' ).'</textarea>
				</div>
			</div>
			<br/>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_abstract">'.$w->labelAbstract.' <small class="muted">'.$w->labelAbstract_suffix.'</small></label>
					<textarea name="abstract" id="input_abstract" class="span12" rows="2" required="required">'.htmlentities( $testimonial->abstract, ENT_QUOTES, 'UTF-8' ).'</textarea>
				</div>
			</div>
			<div class="buttonbar">
				<a href="./manage/testimonial" class="btn btn-small"><i class="icon-arrow-left"></i> '.$w->buttonCancel.'</a>
				<button type="submit" name="save" class="btn not-btn-small btn-primary"><i class="icon-ok icon-white"></i> '.$w->buttonSave.'</button>
			</div>
		</form>
	</div>
</div>';

return '
<div class="row-fluid">
	<div class="span12">
		'.$panelContent.'
	</div>
</div>';

?>
