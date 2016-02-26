<?php

$optStatus	= array(
	0	=> 'nicht sichtbar',
	1	=> 'sichtbar',
);
$optStatus	= UI_HTML_Elements::Options( $optStatus, $testimonial->status );

$panelContent = '
<div class="content-panel content-panel-form">
	<h3>Eintrag ändern</h3>
		<div class="content-panel-inner">
		<form action="./manage/testimonial/edit/'.$testimonial->testimonialId.'" method="post" class="form-changes-auto">
			<div class="row-fluid">
				<div class="span3">
					<label for="input_username">Autor <small class="muted">(<u>sichtbar</u>)</small></label>
					<input type="text" name="username" id="input_username" class="span12" value="'.htmlentities( $testimonial->username, ENT_QUOTES, 'UTF-8' ).'" required="required"/>
				</div>
				<div class="span5">
					<label for="input_email">E-Mail <small class="muted">(unsichtbar)</small></label>
					<input type="text" name="email" id="input_email" class="span12" value="'.htmlentities( $testimonial->email, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span3">
					<label for="input_status">Status</label>
					<select name="status" id="input_status" class="span12">'.$optStatus.'</select>
				</div>
				<div class="span1">
					<label for="input_rank">Rang</label>
					<input type="text" name="rank" id="input_rank" class="span12" value="'.htmlentities( $testimonial->rank, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_title">Überschrift</label>
					<input type="text" name="title" id="input_title" class="span12" value="'.htmlentities( $testimonial->title, ENT_QUOTES, 'UTF-8' ).'" required/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_description">Inhalt <small class="muted">(wird nicht angezeigt - Rohtext vom Autor)</small></label>
					<textarea name="description" id="input_description" class="span12 TinyMCE" data-tinymce-mode="minimal" rows="12">'.htmlentities( $testimonial->description, ENT_QUOTES, 'UTF-8' ).'</textarea>
				</div>
			</div>
			<br/>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_abstract">Testauszug mit zentraler Aussage <small class="muted">(<u>sichtbar</u> - möglichst einzeilig)</small></label>
					<textarea name="abstract" id="input_abstract" class="span12" rows="2" required="required">'.htmlentities( $testimonial->abstract, ENT_QUOTES, 'UTF-8' ).'</textarea>
				</div>
			</div>
			<div class="buttonbar">
				<a href="./manage/testimonial" class="btn btn-small"><i class="icon-arrow-left"></i> zurück</a>
				<button type="submit" name="save" class="btn not-btn-small btn-primary"><i class="icon-ok icon-white"></i> speichern</button>
				<a href="./manage/testimonial/remove/'.$testimonial->testimonialId.'" class="btn btn-small btn-danger"><i class="icon-remove icon-white"></i> entfernen</a>
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
