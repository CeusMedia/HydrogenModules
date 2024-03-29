<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var array<array<string,string>> $words */
/** @var object $template */

$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-left'] );
$iconSave		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );

$w		= (object) $words['add'];

$buttonCancel	= HtmlTag::create( 'a', $iconCancel.'&nbsp;'.$w->buttonCancel, [
	'href'	=> './admin/mail/template',
	'class'	=> 'btn btn-small',
] );
$buttonSave		= HtmlTag::create( 'button', $iconSave.'&nbsp;'.$w->buttonSave, [
	'type'	=> 'submit',
	'name'	=> 'save',
	'class'	=> 'btn btn-primary',
] );

return '
<div class="row-fluid">
	<div class="span12">
		<div class="content-panel">
			<h3>'.$w->heading.'</h3>
			<div class="content-panel-inner">
				<form action="./admin/mail/template/add" method="post">
					<div class="row-fluid">
						<div class="span12">
							<label for="input_template_title">'.$w->labelTitle.'</label>
							<input type="text" name="template_title" id="input_template_title" class="span12" value="'.htmlentities( $template->title, ENT_QUOTES, 'UTF-8' ).'"/>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<label for="input_template_plain">'.$w->labelText.'</label>
							<textarea name="template_plain" id="input_template_plain" class="span12 ace-auto" rows="10">'.htmlentities( $template->plain, ENT_QUOTES, 'UTF-8' ).'</textarea>
							<br/>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<label for="input_template_html">'.$w->labelHtml.'</label>
							<textarea name="template_html" id="input_template_html" class="span12 ace-auto" rows="10">'.htmlentities( $template->html, ENT_QUOTES, 'UTF-8' ).'</textarea>
							<br/>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<label for="input_template_css">'.$w->labelStyle.'</label>
							<textarea name="template_css" id="input_template_css" class="span12 ace-auto" rows="10">'.htmlentities( $template->css, ENT_QUOTES, 'UTF-8' ).'</textarea>
							<br/>
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
