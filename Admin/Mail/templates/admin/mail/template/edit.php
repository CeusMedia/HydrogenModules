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

$heading3	= UI_HTML_Tag::create( 'h3', vsprintf( $w->heading, array(
	'./admin/mail/template',
	htmlentities( $template->title, ENT_QUOTES, 'UTF-8' )
) ) );

extract( $view->populateTexts( array( 'top', 'bottom' ), 'html/admin/mail/template/edit/' ) );

return $textTop.$heading3.$tabs.$textBottom;
?>
