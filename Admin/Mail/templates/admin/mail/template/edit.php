<?php
$w			= (object) $words['edit'];

$iconList	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-list' ) );

$buttonList	= UI_HTML_Tag::create( 'a', $iconList.'&nbsp;'.$words['edit']['buttonList'], array(
	'href'	=> './admin/mail/template',
	'class'	=> 'btn btn-small',
) );

$contentText	= '
<div class="content-panel">
	<h3>'.$w->labelText.'</h3>
	<div class="content-panel-inner">
		<textarea name="template_plain" id="input_template_plain" class="span12" data-ace-option-max-lines="25" rows="25">'.htmlentities( $template->plain, ENT_QUOTES, 'UTF-8' ).'</textarea>
		<br/>
		'.UI_HTML_Tag::create( 'div', $buttonList, array( 'class' => 'buttonbar' ) ).'
	</div>
</div>';

$contentHtml	= '
<div class="content-panel">
	<h3>'.$w->labelHtml.'</h3>
	<div class="content-panel-inner">
		<textarea name="template_html" id="input_template_html" class="span12" data-ace-option-max-lines="25" data-ace-mode="html" rows="25" style="line-height: 1em">'.htmlentities( $template->html, ENT_QUOTES, 'UTF-8' ).'</textarea>
		<br/>
		'.UI_HTML_Tag::create( 'div', $buttonList, array( 'class' => 'buttonbar' ) ).'
	</div>
</div>';

$contentCss	= '
<div class="content-panel">
	<h3>'.$w->labelCss.'</h3>
	<div class="content-panel-inner">
		<textarea name="template_css" id="input_template_css" class="span12" data-ace-option-max-lines="25" data-ace-mode="css" rows="25" style="line-height: 1em">'.htmlentities( $template->css, ENT_QUOTES, 'UTF-8' ).'</textarea>
		<br/>
		'.UI_HTML_Tag::create( 'div', $buttonList, array( 'class' => 'buttonbar' ) ).'
	</div>
</div>';

$contentPreview	= '
	<div class="row-fluid">
		<div class="span12">
			<iframe id="frame-template-preview" src="./admin/mail/template/preview/'.$template->mailTemplateId.'/html"></iframe>
		</div>
	</div>';

$contentFacts	= $view->loadTemplateFile( 'admin/mail/template/edit.facts.php', array( 'buttonList' => $buttonList ) );
$contentStyles	= $view->loadTemplateFile( 'admin/mail/template/edit.styles.php', array( 'buttonList' => $buttonList ) );
$contentImages	= $view->loadTemplateFile( 'admin/mail/template/edit.images.php', array( 'buttonList' => $buttonList ) );

$tabs		= new \CeusMedia\Bootstrap\Tabs( 'admin-mail-template-edit' );
$tabs->add( 'admin-mail-template-edit-tab-facts', '#', $w->tabFacts, $contentFacts );
$tabs->add( 'admin-mail-template-edit-tab-text', '#', $w->tabText, $contentText );
$tabs->add( 'admin-mail-template-edit-tab-html', '#', $w->tabHtml, $contentHtml );
$tabs->add( 'admin-mail-template-edit-tab-css', '#', $w->tabCss, $contentCss );
$tabs->add( 'admin-mail-template-edit-tab-styles', '#', $w->tabStyles, $contentStyles );
$tabs->add( 'admin-mail-template-edit-tab-images', '#', $w->tabImages, $contentImages );
$tabs->add( 'admin-mail-template-edit-tab-preview', '#', $w->tabPreview, $contentPreview );
$tabs->setActive( $tabId ? $tabId : 'admin-mail-template-edit-tab-facts' );

return '
<h3>'.sprintf( $w->heading, htmlentities( $template->title, ENT_QUOTES, 'UTF-8' ) ).'</h3>
<p>
	<a href="./admin/mail/template" class="btn btn-mini">'.$iconList.'&nbsp;zur Liste</a>
</p>
<div class="not-content-panel-inner">
	'.$tabs.'
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
		var targetUrl	= jQuery(this).attr("href").substr(1);
		jQuery.ajax({
			url: "./admin/mail/template/ajaxSetTab/"+targetUrl
		});
	});
})
</script>
';
?>
