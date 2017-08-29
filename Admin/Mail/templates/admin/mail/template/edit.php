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

$listImages	= '<em class="muted">Keine.</em>';
if( $template->images ){
	$list	= array();
	foreach( explode( ',', $template->images ) as $nr => $item ){
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

$rowText	= '
<div class="row-fluid">
	<div class="span12">
		<label for="input_template_plain">Text-Variante</label>
		<textarea name="template_plain" id="input_template_plain" class="span12 ace-auto" data-ace-option-max-lines="14" rows="14">'.htmlentities( $template->plain, ENT_QUOTES, 'UTF-8' ).'</textarea>
	</div>
</div>';
$rowHtml	= '
<div class="row-fluid">
	<div class="span12">
		<label for="input_template_html">HTML-Gerüst</label>
		<textarea name="template_html" id="input_template_html" class="span12 ace-auto" data-ace-option-max-lines="14" data-ace-mode="html" rows="14" style="line-height: 1em">'.htmlentities( $template->html, ENT_QUOTES, 'UTF-8' ).'</textarea>
	</div>
</div>';
$rowStyles	= '
<div class="row-fluid">
	<div class="span6">
		<label for="input_template_css">Style-Definitionen</label>
		<textarea name="template_css" id="input_template_css" class="span12 ace-auto"data-ace-option-max-lines="14" data-ace-mode="css" rows="14" style="line-height: 1em">'.htmlentities( $template->css, ENT_QUOTES, 'UTF-8' ).'</textarea>
	</div>
	<div class="span6">
		<h4>Style-Files</h4>
		'.$listStyles.'
		<label for="input_template_style">Pfad</label>
		<input type="text" name="template_style" class="span12"/>
	</div>
</div>';
$rowImages	= '
<div class="row-fluid">
	<h4>Bildverweise</h4>
	'.$listImages.'
	<label for="input_template_image">Pfad</label>
	<input type="text" name="template_image" class="span12"/>
</div>';

$tabs		= new \CeusMedia\Bootstrap\Tabs( 'admin-mail-template-edit' );
$tabs->add( 'admin-mail-template-edit-tab-ext', '#', 'Text-Variante', $rowText );
$tabs->add( 'admin-mail-template-edit-tab-html', '#', 'HTML-Variante', $rowHtml );
$tabs->add( 'admin-mail-template-edit-tab-styles', '#', 'Style-Definitionen', $rowStyles );
$tabs->add( 'admin-mail-template-edit-tab-images', '#', 'Bilder-Verweise', $rowImages );

return '
<div class="row-fluid">
	<div class="span12">
		<div class="content-panel">
			<h3>Edit Template</h3>
			<div class="content-panel-inner">
				<form action="./admin/mail/template/edit/'.$template->mailTemplateId.'" method="post" class="not-form-changes-auto">
					<div class="row-fluid">
						<div class="span12">
							<label for="input_template_title">Titel</label>
							<input type="text" name="template_title" id="input_template_title" class="span12" value="'.htmlentities( $template->title, ENT_QUOTES, 'UTF-8' ).'"/>
						</div>
					</div>
					'.$tabs.'
					<div class="buttonbar">
						'.$buttonCancel.'
						'.$buttonSave.'
						'.$buttonPreview.'
						'.$buttonRemove.'
					</div>
				</form>
			</div>
		</div>
		<div class="content-panel">
			<h3>Testen</h3>
			<div class="content-panel-inner">
				<form action="./admin/mail/template/test/'.$template->mailTemplateId.'" method="post">
				<div class="row-fluid">
					<div class="span6">
						<label for="input_email">E-Mail-Adresse</label>
						<input type="text" name="email" id="input_email" class="span12"/>
					</div>
				</div>
				<div class="buttonbar">
					<button type="submit">testen</button>
				</div>
			</div>
		</div>
	</div>
</div>
<script>
jQuery(document).ready(function(){
	jQuery("#admin-mail-template-edit li").bind("click", function(){
		console.log(jQuery(this).data("nr"));
	});
})
</script>
<style>
</style>
';
?>
