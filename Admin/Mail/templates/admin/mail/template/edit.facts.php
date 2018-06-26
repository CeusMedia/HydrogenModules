<?php

$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
$iconSave		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );
$iconPreview	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-eye' ) );
$iconRemove		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );
$iconPreview	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-eye' ) );
$iconExport		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-download' ) );

$modal			= new View_Helper_Bootstrap_Modal( $env );
$modal->setHeading( $words['modal-preview']['heading'] );
$modal->setBody( '<iframe src="./admin/mail/template/preview/'.$template->mailTemplateId.'/html"></iframe>' );
$modal->setId( 'modal-admin-mail-template-preview' );
$modal->setFade( FALSE );
//	$modal->setButtonLabelCancel( $iconCancel.'&nbsp;'.$modalWords->buttonCancel );
//	$modal->setButtonLabelSubmit( $iconSave.'&nbsp;'.$modalWords->buttonSubmit );
$trigger	= new View_Helper_Bootstrap_Modal_Trigger( $env );
$trigger->setModalId( 'modal-admin-mail-template-preview' );
$trigger->setLabel( $iconPreview.'&nbsp;'.$words['edit']['buttonPreview'] );
$trigger->setAttributes( array( 'class' => 'btn btn-info' ) );
$buttonPreview	= $trigger->render();

$buttonCancel	= UI_HTML_Tag::create( 'a', $iconCancel.'&nbsp;'.$words['edit']['buttonCancel'], array(
	'href'	=> './admin/mail/template',
	'class'	=> 'btn btn-small',
) );
$buttonSave		= UI_HTML_Tag::create( 'button', $iconSave.'&nbsp;'.$words['edit']['buttonSave'], array(
	'type'	=> 'submit',
	'name'	=> 'save',
	'class'	=> 'btn btn-primary',
) );
$buttonRemove	= UI_HTML_Tag::create( 'a', $iconRemove.'&nbsp;'.$words['edit']['buttonRemove'], array(
	'href'		=> './admin/mail/template/remove/'.$template->mailTemplateId,
	'class'		=> 'btn btn-small btn-danger',
	'onclick'	=> 'if(!confirm(\'Wirklich ?\'))return false;'
) );
$buttonExport	= UI_HTML_Tag::create( 'a', $iconExport.'&nbsp;'.$words['edit']['buttonExport'], array(
	'href'	=> './admin/mail/template/export/'.$template->mailTemplateId,
	'class'	=> 'btn btn-small',
) );

return '
	<div class="row-fluid">
		<div class="span6">
			<form action="./admin/mail/template/edit/'.$template->mailTemplateId.'" method="post" class="not-form-changes-auto">
				<div class="content-panel">
					<h3>Grunddaten</h3>
					<div class="content-panel-inner">
						<label for="input_template_title">'.$words['edit']['labelTitle'].'</label>
						<input type="text" name="template_title" id="input_template_title" class="span12" value="'.htmlentities( $template->title, ENT_QUOTES, 'UTF-8' ).'"/>
						<div class="buttonbar">
							'.$buttonCancel.'
							'.$buttonSave.'
							'.$buttonPreview.'
							'.$buttonExport.'
							'.$buttonRemove.'
						</div>
					</div>
				</div>
			</form>
		</div>
		<div class="span6">
			<form action="./admin/mail/template/test/'.$template->mailTemplateId.'" method="post">
				<div class="content-panel">
					<h3>'.$words['edit-test']['heading'].'</h3>
					<div class="content-panel-inner">
						<div class="row-fluid">
							<div class="span12">
								<label for="input_email">'.$words['edit-test']['labelAddress'].'</label>
								<input type="email" name="email" id="input_email" class="span12"/>
							</div>
						</div>
						<div class="buttonbar">
							<button type="submit" class="btn">'.$words['edit-test']['buttonSend'].'</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>'.$modal;

?>
