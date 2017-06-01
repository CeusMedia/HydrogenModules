<?php

$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
$iconSave		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );

$buttonCancel	= UI_HTML_Tag::create( 'a', $iconCancel.'&nbsp;abbrechen', array(
	'href'	=> './admin/mail/template',
	'class'	=> 'btn btn-small',
) );
$buttonSave		= UI_HTML_Tag::create( 'button', $iconSave.'&nbsp;hinzufügen', array(
	'type'	=> 'submit',
	'name'	=> 'save',
	'class'	=> 'btn btn-primary',
) );

return '
<div class="row-fluid">
	<div class="span12">
		<div class="content-panel">
			<h3>Add Template</h3>
			<div class="content-panel-inner">
				<form action="./admin/mail/template/add" method="post">
					<div class="row-fluid">
						<div class="span12">
							<label for="input_template_title"></label>
							<input type="text" name="template_title" id="input_template_title" class="span12" value="'.htmlentities( $template->title, ENT_QUOTES, 'UTF-8' ).'"/>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<label for="input_template_plain"></label>
							<textarea name="template_plain" id="input_template_plain" class="span12" rows="12">'.htmlentities( $template->plain, ENT_QUOTES, 'UTF-8' ).'</textarea>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<label for="input_template_html"></label>
							<textarea name="template_html" id="input_template_html" class="span12" rows="12">'.htmlentities( $template->html, ENT_QUOTES, 'UTF-8' ).'</textarea>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<label for="input_template_css"></label>
							<textarea name="template_css" id="input_template_css" class="span12 CodeMirror-auto" rows="12">'.htmlentities( $template->css, ENT_QUOTES, 'UTF-8' ).'</textarea>
						</div>
					</div>
					<div class="buttonbar">
						'.$buttonCancel.$buttonSave.'
					</div>
				</form>
			</div>
		</div>
	</div>
</div>';
?>
