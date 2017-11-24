<?php

$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
$iconSave		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );
$iconPreview	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-eye' ) );
$iconRemove		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );
$iconPreview	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-eye' ) );

$modal			= new View_Helper_Bootstrap_Modal( $env );
$modal->setHeading( 'Vorschau' );
$modal->setBody( '<iframe src="./admin/mail/template/preview/'.$template->mailTemplateId.'/html"></iframe>' );
$modal->setId( 'modal-admin-mail-template-preview' );
$modal->setFade( FALSE );
//	$modal->setButtonLabelCancel( $iconCancel.'&nbsp;'.$modalWords->buttonCancel );
//	$modal->setButtonLabelSubmit( $iconSave.'&nbsp;'.$modalWords->buttonSubmit );
$trigger	= new View_Helper_Bootstrap_Modal_Trigger( $env );
$trigger->setModalId( 'modal-admin-mail-template-preview' );
$trigger->setLabel( $iconPreview.'&nbsp;Vorschau' );
$trigger->setAttributes( array( 'class' => 'btn btn-info' ) );
$buttonPreview	= $trigger->render();

$buttonCancel	= UI_HTML_Tag::create( 'a', $iconCancel.'&nbsp;abbrechen', array(
	'href'	=> './admin/mail/template',
	'class'	=> 'btn btn-small',
) );
$buttonSave		= UI_HTML_Tag::create( 'button', $iconSave.'&nbsp;speichern', array(
	'type'	=> 'submit',
	'name'	=> 'save',
	'class'	=> 'btn btn-primary',
) );
$buttonRemove	= UI_HTML_Tag::create( 'a', $iconRemove.'&nbsp;entfernen', array(
	'href'	=> './admin/mail/template/remove/'.$template->mailTemplateId,
	'class'	=> 'btn btn-small btn-inverse',
) );


$rowFacts	= '
	<div class="row-fluid">
		<div class="span6">
			<form action="./admin/mail/template/edit/'.$template->mailTemplateId.'" method="post" class="not-form-changes-auto">
				<div class="content-panel">
					<h3>Grunddaten</h3>
					<div class="content-panel-inner">
						<label for="input_template_title">Titel</label>
						<input type="text" name="template_title" id="input_template_title" class="span12" value="'.htmlentities( $template->title, ENT_QUOTES, 'UTF-8' ).'"/>
						<div class="buttonbar">
							'.$buttonCancel.'
							'.$buttonSave.'
							'.$buttonPreview.'
							'.$buttonRemove.'
						</div>
					</div>
				</div>
			</form>
		</div>
		<div class="span6">
			<form action="./admin/mail/template/test/'.$template->mailTemplateId.'" method="post">
				<div class="content-panel">
					<h3>Test per Versand</h3>
					<div class="content-panel-inner">
						<div class="row-fluid">
							<div class="span12">
								<label for="input_email">E-Mail-Adresse</label>
								<input type="email" name="email" id="input_email" class="span12"/>
							</div>
						</div>
						<div class="buttonbar">
							<button type="submit" class="btn">versenden</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>'.$modal;



$rowText	= '<form action="./admin/mail/template/edit/'.$template->mailTemplateId.'" method="post" class="not-form-changes-auto">
	<div class="row-fluid">
		<div class="span12">
			<label for="input_template_plain">Text-Variante</label>
			<textarea name="template_plain" id="input_template_plain" class="span12" data-ace-option-max-lines="25" rows="25">'.htmlentities( $template->plain, ENT_QUOTES, 'UTF-8' ).'</textarea>
		</div>
	</div>
</form>';

$rowHtml	= '<form action="./admin/mail/template/edit/'.$template->mailTemplateId.'" method="post" class="not-form-changes-auto">
	<div class="row-fluid">
		<div class="span12">
			<label for="input_template_html">HTML-Gerüst</label>
			<textarea name="template_html" id="input_template_html" class="span12" data-ace-option-max-lines="25" data-ace-mode="html" rows="25" style="line-height: 1em">'.htmlentities( $template->html, ENT_QUOTES, 'UTF-8' ).'</textarea>
		</div>
	</div>
</form>';


$listStyles	= '<em class="muted">Keine.</em>';
if( $template->styles ){
	$list	= array();
	foreach( json_decode( $template->styles, TRUE ) as $style ){
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

$rowStyles	= '<form action="./admin/mail/template/edit/'.$template->mailTemplateId.'" method="post" class="not-form-changes-auto">
	<div class="row-fluid">
		<div class="span6">
			<label for="input_template_css">Style-Definitionen</label>
			<textarea name="template_css" id="input_template_css" class="span12" data-ace-option-max-lines="25" data-ace-mode="css" rows="25" style="line-height: 1em">'.htmlentities( $template->css, ENT_QUOTES, 'UTF-8' ).'</textarea>
		</div>
		<div class="span6">
			<h4>Style-Files</h4>
			'.$listStyles.'
			<label for="input_template_style">Pfad</label>
			<input type="text" name="template_style" class="span12"/>
		</div>
	</div>
</form>';


$modal		= new View_Helper_Input_Resource( $env );
$modal->setModalId( 'modal-admin-mail-template-select' );
$modal->setInputId( 'input_template_image' );
$trigger	= new View_Helper_Input_ResourceTrigger( $env );
$trigger->setLabel( UI_HTML_Tag::create( 'i', '', array( 'class' => "fa fa-fw fa-folder-open" ) ) );
$trigger->setModalId( 'modal-admin-mail-template-select' );
$trigger->setInputId( 'input_template_image' );

$listImages	= '<em class="muted">Keine.</em>';
if( $template->images ){
	$list	= array();
	foreach( json_decode( $template->images, TRUE ) as $nr => $item ){
		$button	= UI_HTML_Tag::create( 'a', '<i class="fa fa-fw fa-remove"></i>', array(
			'class'	=> 'btn btn-mini btn-inverse pull-right',
			'href'	=> './admin/mail/template/removeImage/'.$template->mailTemplateId.'/'.base64_encode( $item ),
		) );
		$image	= UI_HTML_Tag::create(' img', NULL, array(
			'src' 	=> $appUri.$item,
			'style'	=> 'max-height: 40px',
		) );
		$list[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $image ),
			UI_HTML_Tag::create( 'td', '<strong><kbd>image'.( $nr + 1).'</kbd></strong>' ),
			UI_HTML_Tag::create( 'td', $item ),
			UI_HTML_Tag::create( 'td', $button )
		) );
	}
	$listImages	= UI_HTML_Tag::create( 'table', array(
		UI_HTML_Elements::ColumnGroup( '120px', '100px', '', '60px' ),
		UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array(
			'Bild',
			'CID',
			'Pfad',
			'',
		) ) ),
		UI_HTML_Tag::create( 'tbody', $list ),
	), array(
		'class'	=> 'table table-condensed table-fixed',
	) );
}

$rowImages	= '<form action="./admin/mail/template/edit/'.$template->mailTemplateId.'" method="post" class="not-form-changes-auto">
	<div class="row-fluid">
		<div class="span12">
			<h4>Bildverweise</h4>
			'.$listImages.'
			<hr/>
			<h4>Hinzufügen</h4>
			<div class="row-fluid">
				<div class="span9">
					<label for="input_template_image">Pfad</label>
					<input type="text" name="template_image" id="input_template_image" class="span12" required="required"/>
				</div>
				<div class="span3">
					<label>&nbsp;</label>
					<div class="btn-group">
						'.$trigger.'
						<button type="submit" name="save" class="btn btn-primary"><i class="fa fa-fw fa-check"></i> hinzufügen</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</form>'.$modal;

$rowPreview	= '
	<div class="row-fluid">
		<div class="span12">
			<iframe id="frame-template-preview" src="./admin/mail/template/preview/'.$template->mailTemplateId.'/html"></iframe>
		</div>
	</div>';

$tabs		= new \CeusMedia\Bootstrap\Tabs( 'admin-mail-template-edit' );
$tabs->add( 'admin-mail-template-edit-tab-facts', '#', 'Details', $rowFacts );
$tabs->add( 'admin-mail-template-edit-tab-ext', '#', 'Text-Variante', $rowText );
$tabs->add( 'admin-mail-template-edit-tab-html', '#', 'HTML-Variante', $rowHtml );
$tabs->add( 'admin-mail-template-edit-tab-styles', '#', 'Style-Definitionen', $rowStyles );
$tabs->add( 'admin-mail-template-edit-tab-images', '#', 'Bilder-Verweise', $rowImages );
$tabs->add( 'admin-mail-template-edit-tab-preview', '#', 'Vorschau', $rowPreview );
$tabs->setActive( $tabId ? $tabId : 'admin-mail-template-edit-tab-facts' );

return '
<div class="row-fluid">
	<div class="span12">
		<div class="content-panel">
			<h3>Edit Template</h3>
			<div class="content-panel-inner">
				'.$tabs.'
			</div>
		</div>
	</div>
</div>
<script>
var templateId = '.$template->mailTemplateId.';

$(document).ready(function(){
	ModuleAce.verbose = false;
	var onUpdate	= function(chance){
		jQuery("#modal-admin-mail-template-preview .modal-body iframe").get(0).contentWindow.location.reload();
		jQuery("#frame-template-preview").get(0).contentWindow.location.reload();
	};
	ModuleAceAutoSave.applyToEditor(
		ModuleAce.applyTo("#input_template_html"),
		"admin/mail/template/ajaxSaveHtml/"+templateId,
		{callbacks: {update: onUpdate}}
	);
	ModuleAceAutoSave.applyToEditor(
		ModuleAce.applyTo("#input_template_plain"),
		"admin/mail/template/ajaxSavePlain/"+templateId,
		{callbacks: {update: onUpdate}}
	);
	ModuleAceAutoSave.applyToEditor(
		ModuleAce.applyTo("#input_template_css"),
		"admin/mail/template/ajaxSaveCss/"+templateId,
		{callbacks: {update: onUpdate}}
	);
/*	onUpdate();*/
});

jQuery(document).ready(function(){
	jQuery("#admin-mail-template-edit li a").bind("click", function(){
		jQuery.ajax({
			url: "./admin/mail/template/ajaxSetTab/"+jQuery(this).data("id")
		});
	});
})
</script>
<style>
#modal-admin-mail-template-preview {
	width: 80vw;
	height: 90vh;
	top: 5vh;
	margin-left: -40% !important;
	}
#modal-admin-mail-template-preview .modal-body {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    max-height: none;
    padding-top: 50px;
    padding-bottom: 60px;
    box-sizing: border-box;
    z-index: 1008;
	}
#modal-admin-mail-template-preview .modal-body iframe {
    width: 100%;
    height: 99%;
    border: 0px;
	}
#frame-template-preview {
	width: 100%;
	height: 485px;
	border: 1px solid gray;
	box-sizing: border-box;
	}
</style>
';
?>
