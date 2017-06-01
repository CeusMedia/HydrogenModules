<?php

$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
$iconSave		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );
$iconPreview	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-eye' ) );
$iconRemove		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );

$buttonCancel	= UI_HTML_Tag::create( 'a', $iconCancel.'&nbsp;abbrechen', array(
	'href'	=> './admin/mail/template',
	'class'	=> 'btn btn-small',
) );
$buttonSave		= UI_HTML_Tag::create( 'button', $iconSave.'&nbsp;speichern', array(
	'type'	=> 'submit',
	'name'	=> 'save',
	'class'	=> 'btn btn-primary',
) );
$buttonPreview	= UI_HTML_Tag::create( 'a', $iconPreview.'&nbsp;Vorschau', array(
	'href'	=> './admin/mail/template/preview/'.$template->mailTemplateId.'/html',
	'class'	=> 'btn btn-small btn-info',
) );
$buttonRemove	= UI_HTML_Tag::create( 'a', $iconRemove.'&nbsp;entfernen', array(
	'href'	=> './admin/mail/template/remove/'.$template->mailTemplateId,
	'class'	=> 'btn btn-small btn-inverse',
) );


$listStyles	= '<em class="muted">Keine.</em>';
if( $template->styles ){
	$list	= array();
	foreach( explode( ',', $template->styles ) as $style ){
		$button	= UI_HTML_Tag::create( 'a', '<i class="fa fa-fw fa-remove"></i>', array(
			'class'	=> 'btn btn-mini btn-inverse pull-right',
			'href'	=> './admin/mail/template/removeStyle/'.$template->mailTemplateId.'/'.base64_encode( $style ),
		) );
		$list[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $style ),
			UI_HTML_Tag::create( 'td', $button )
		) );
	}
	$listStyles	= UI_HTML_Tag::create( 'table', array(
		UI_HTML_Tag::create( 'tbody', $list ),
	), array(
		'class'	=> 'table table-condensed table-fixed',
	) );
}

return '
<div class="row-fluid">
	<div class="span12">
		<div class="content-panel">
			<h3>Edit Template</h3>
			<div class="content-panel-inner">
				<form action="./admin/mail/template/edit/'.$template->mailTemplateId.'" method="post" class="not-form-changes-auto">
					<div class="row-fluid">
						<div class="span12">
							<label for="input_template_title"></label>
							<input type="text" name="template_title" id="input_template_title" class="span12" value="'.htmlentities( $template->title, ENT_QUOTES, 'UTF-8' ).'"/>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<label for="input_template_plain"></label>
							<textarea name="template_plain" id="input_template_plain" class="span12 CodeMirror-auto" rows="4">'.htmlentities( $template->plain, ENT_QUOTES, 'UTF-8' ).'</textarea>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<label for="input_template_html"></label>
							<textarea name="template_html" id="input_template_html" class="span12 CodeMirror-auto" rows="14">'.htmlentities( $template->html, ENT_QUOTES, 'UTF-8' ).'</textarea>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span6">
							<label for="input_template_css"></label>
							<textarea name="template_css" id="input_template_css" class="span12 CodeMirror-auto" rows="10">'.htmlentities( $template->css, ENT_QUOTES, 'UTF-8' ).'</textarea>
						</div>
						<div class="span6">
							<h4>Style-Files</h4>
							'.$listStyles.'
							<label for="input_template_style">URL</label>
							<input type="text" name="template_style"/>
						</div>
					</div>
					<div class="buttonbar">
						'.$buttonCancel.'
						'.$buttonSave.'
						'.$buttonPreview.'
						'.$buttonRemove.'
					</div>
				</form>
			</div>
		</div>
	</div>
</div>';
?>
