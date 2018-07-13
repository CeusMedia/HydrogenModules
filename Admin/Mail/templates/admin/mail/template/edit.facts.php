<?php
use \CeusMedia\Bootstrap\Icon;
use \CeusMedia\Bootstrap\Modal;
use \CeusMedia\Bootstrap\Modal\Trigger;
use \CeusMedia\Bootstrap\LinkButton;
use \CeusMedia\Bootstrap\SubmitButton;

$modalPreview		= new Modal( 'modal-admin-mail-template-preview' );
$modalPreview->setHeading( $words['modal-preview']['heading'] );
$modalPreview->setBody( '<iframe src="./admin/mail/template/preview/'.$template->mailTemplateId.'/html"></iframe>' );
$modalPreview->setFade( FALSE );

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
							'.LinkButton::create(
								'./admin/mail/template',
								$words['edit']['buttonCancel'],
								'btn btn-small',
								'list'
							).'
							'.SubmitButton::create(
								'save',
								$words['edit']['buttonSave'],
								'btn btn-primary',
								'check'
							).'
							'.Trigger::create(
								'modal-admin-mail-template-preview',
								$words['edit']['buttonPreview']
							)	->setIcon( 'eye' )
								->setAttributes( array( 'class' => 'btn btn-info' ) ).'
							'.LinkButton::create(
								'./admin/mail/template/export/'.$template->mailTemplateId,
								$words['edit']['buttonExport'],
								'btn btn-small',
								'download'
							).'
							'.LinkButton::create(
								'./admin/mail/template/remove/'.$template->mailTemplateId,
								$words['edit']['buttonRemove'],
								'btn btn-small btn-danger',
								'remove'
							)->setConfirm( 'Wirklich?' ).'
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
	</div>'.$modalPreview;

?>
