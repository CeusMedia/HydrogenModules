<?php
use CeusMedia\Bootstrap\Button\Link as LinkButton;
use CeusMedia\Bootstrap\Button\Submit as SubmitButton;
use CeusMedia\Bootstrap\Icon;
use CeusMedia\Bootstrap\Modal\Dialog as BootstrapModalDialog;
use CeusMedia\Bootstrap\Modal\Trigger as BootstrapModalTrigger;

$buttonActivate	= LinkButton::create(
	'./admin/mail/template/setStatus/'.$template->mailTemplateId.'/'.Model_Mail_Template::STATUS_ACTIVE,
	$words['edit']['buttonActivate'],
	'btn btn-small',
	NULL
);
if( $template->status != Model_Mail_Template::STATUS_USABLE )
	$buttonActivate	= '';

$buttonUsable	= LinkButton::create(
	'./admin/mail/template/setStatus/'.$template->mailTemplateId.'/'.Model_Mail_Template::STATUS_USABLE,
	$words['edit']['buttonUsable'],
	'btn btn-small',
	NULL
);
if( $template->status >= Model_Mail_Template::STATUS_USABLE )
	$buttonUsable	= '';

$panelMain	= '
	<form action="./admin/mail/template/edit/'.$template->mailTemplateId.'" method="post" class="not-form-changes-auto">
		<div class="content-panel">
			<h3>Grunddaten</h3>
			<div class="content-panel-inner">
				<div class="row-fluid">
					<div class="span9">
						<label for="input_template_title" class="mandatory required">'.$words['edit']['labelTitle'].'</label>
						<input type="text" name="template_title" id="input_template_title" class="span12" required="required" value="'.htmlentities( $template->title, ENT_QUOTES, 'UTF-8' ).'"/>
					</div>
					<div class="span3">
						<label for="input_template_status">'.$words['edit']['labelStatus'].'</label>
						<input type="text" class="span12" value="'.$words['status'][$template->status].'" disabled="disabled"/>
					</div>
				</div>
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
					'./*Trigger::create(
						'modal-admin-mail-template-preview-html',
						$words['edit']['buttonPreview']
					)	->setIcon( 'eye' )
						->setAttributes( array( 'class' => 'btn btn-info' ) ).*/'
					'.$buttonUsable.'
					'.$buttonActivate.'
				</div>
			</div>
		</div>
	</form>';

$panelTest	= '
	<form action="./admin/mail/template/test/'.$template->mailTemplateId.'" method="post">
		<div class="content-panel">
			<h3>'.$words['edit-test']['heading'].'</h3>
			<div class="content-panel-inner">
				<div class="row-fluid">
					<div class="span12">
						<label for="input_email">'.$words['edit-test']['labelAddress'].'</label>
						<input type="email" name="email" id="input_email" class="span12" required="required"/>
					</div>
				</div>
				<div class="buttonbar">
					<button type="submit" class="btn">'.$words['edit-test']['buttonSend'].'</button>
				</div>
			</div>
		</div>
	</form>';

$panelCopy	= '
	<form action="./admin/mail/template/copy/'.$template->mailTemplateId.'" method="post">
		<div class="content-panel">
			<h3>'.$words['edit-copy']['heading'].'</h3>
			<div class="content-panel-inner">
				<div class="row-fluid">
					<div class="span12">
						<label for="input_title">'.$words['edit-copy']['labelTitle'].'</label>
						<input type="text" name="title" id="input_title" class="span12" required="required"/>
					</div>
				</div>
				<div class="buttonbar">
					<button type="submit" class="btn">'.$words['edit-copy']['buttonCopy'].'</button>
				</div>
			</div>
		</div>
	</form>';

$panelExport	= '
	<div class="content-panel">
		<h3>'.$words['edit-export']['heading'].'</h3>
		<div class="content-panel-inner">
			<div class="row-fluid">
				<div class="span12">
					<div class="alert alert-info">
						Diese Vorlage kann in eine JSON-Datei exportiert werden.
					</div>
				</div>
			</div>
			<div class="buttonbar">
				'.LinkButton::create(
					'./admin/mail/template/export/'.$template->mailTemplateId,
					$words['edit']['buttonExport'],
					'btn btn-small',
					'download'
				).'
			</div>
		</div>
	</div>';

$panelRemove	= '
	<div class="content-panel">
		<h3>'.$words['edit-remove']['heading'].'</h3>
		<div class="content-panel-inner">
			<div class="row-fluid">
				<div class="span12">
					<div class="alert alert-notice">
						Die Vorlage kann entfernt werden, wenn sie nicht bereits verwendet wurde.
					</div>
				</div>
			</div>
			<div class="buttonbar">
				'.LinkButton::create(
					'./admin/mail/template/remove/'.$template->mailTemplateId,
					$words['edit']['buttonRemove'],
					'btn btn-small btn-danger',
					'fa fa-fw fa-remove'
				)->setConfirm( 'Wirklich?' ).'
			</div>
		</div>
	</div>';
if( $template->used )
	$panelRemove	= '';

/*  --  PANEL: PREVIEW: HTML  --  */
$modalPreviewHtml	= new BootstrapModalDialog( 'modal-admin-mail-template-preview-html' );
$modalPreviewHtml->setHeading( $words['modal-preview']['heading-html'] );
$modalPreviewHtml->setBody( '<iframe src="./admin/mail/template/preview/'.$template->mailTemplateId.'/html"></iframe>' );
$modalPreviewHtml->setFade( FALSE );
//$modalPreviewHtml->useHeader( FALSE );
$modalPreviewHtml->useFooter( FALSE );
$modalPreviewHtml->setCloseButtonLabel( $words['modal-preview']['buttonClose'] );
$buttonPreviewHtml	= BootstrapModalTrigger::create(
	'modal-admin-mail-template-preview-html',
	$words['edit']['buttonPreview']
)	->setIcon( 'eye' )
	->setAttributes( array( 'class' => 'btn btn-info btn-mini' ) );
$iframeHtml		= UI_HTML_Tag::create( 'iframe', '', array(
	'src'			=> './admin/mail/template/preview/'.$template->mailTemplateId.'/html',
	'frameborder'	=> '0',
) );

/*  --  PANEL: PREVIEW: TEXT  --  */
$modalPreviewText	= new BootstrapModalDialog( 'modal-admin-mail-template-preview-text' );
$modalPreviewText->setHeading( $words['modal-preview']['heading-text'] );
$modalPreviewText->setBody( '<iframe src="./admin/mail/template/preview/'.$template->mailTemplateId.'/text"></iframe>' );
$modalPreviewText->setFade( FALSE );
//$modalPreviewText->useHeader( FALSE );
$modalPreviewText->useFooter( FALSE );
$buttonPreviewText	= BootstrapModalTrigger::create(
	'modal-admin-mail-template-preview-text',
	$words['edit']['buttonPreview']
)	->setIcon( 'eye' )
	->setAttributes( array( 'class' => 'btn btn-info btn-mini' ) );
$iframeText		= UI_HTML_Tag::create( 'iframe', '', array(
	'src'			=> './admin/mail/template/preview/'.$template->mailTemplateId.'/text',
	'frameborder'	=> '0',
) );

$panelPreview		= '
<div class="content-panel">
	<h3>'.$words['modal-preview']['heading'].'</h3>
	<div class="content-panel-inner">
		<label>
			<span>'.$words['modal-preview']['heading-html'].'</span>
			<div style="float: right">
				'.$buttonPreviewHtml.'
			</div>
		</label>
		<div class="template-preview template-preview-html half-size">
			<div class="template-preview-container">
				<div class="template-preview-iframe-container">
					'.$iframeHtml.'
				</div>
			</div>
		</div>
		<label>
			<span>'.$words['modal-preview']['heading-text'].'</span>
			<div style="float: right">
				'.$buttonPreviewText.'
			</div>
		</label>
		<div class="template-preview template-preview-text half-size">
			<div class="template-preview-container">
				<div class="template-preview-iframe-container">
					'.$iframeText.'
				</div>
			</div>
		</div>
	</div>
</div>';

return '<div class="row-fluid">
	<div class="span6">
		'.$panelMain.'
		<div class="row-fluid">
			<div class="span6">
				'.$panelTest.'
			</div>
			<div class="span6">
				'.$panelCopy.'
			</div>
		</div>
		<div class="row-fluid">
			<div class="span6">
				'.$panelExport.'
			</div>
			<div class="span6">
				'.$panelRemove.'
			</div>
		</div>
	</div>
	<div class="span6">
		'.$panelPreview.'
	</div>
</div>'.$modalPreviewHtml.$modalPreviewText;
