<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Bootstrap\Nav\Tabs as BootstrapTabs;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;
use CeusMedia\HydrogenFramework\View;

/** @var Web $env */
/** @var View $view */
/** @var array<array<string,string>> $words */
/** @var object $template */
/** @var ?string $tabId */

$w			= (object) $words['edit'];

$iconList	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-list'] );

$buttonList	= HtmlTag::create( 'a', $iconList.'&nbsp;'.$words['edit']['buttonList'], array(
	'href'	=> './admin/mail/template',
	'class'	=> 'btn btn-small',
) );

$contentText	= '
<div class="content-panel">
	<h3>'.$w->labelText.'</h3>
	<div class="content-panel-inner">
		<textarea name="template_plain" id="input_template_plain" class="span12" data-ace-option-max-lines="25" rows="25">'.htmlentities( $template->plain, ENT_QUOTES, 'UTF-8' ).'</textarea>
		<br/>
		'.HtmlTag::create( 'div', $buttonList, ['class' => 'buttonbar'] ).'
	</div>
</div>';

$contentHtml	= '
<div class="content-panel">
	<h3>'.$w->labelHtml.'</h3>
	<div class="content-panel-inner">
		<textarea name="template_html" id="input_template_html" class="span12" data-ace-option-max-lines="25" data-ace-mode="html" rows="25" style="line-height: 1em">'.htmlentities( $template->html, ENT_QUOTES, 'UTF-8' ).'</textarea>
		<br/>
		'.HtmlTag::create( 'div', $buttonList, ['class' => 'buttonbar'] ).'
	</div>
</div>';

$contentCss	= '
<div class="content-panel">
	<h3>'.$w->labelCss.'</h3>
	<div class="content-panel-inner">
		<textarea name="template_css" id="input_template_css" class="span12" data-ace-option-max-lines="25" data-ace-mode="css" rows="25" style="line-height: 1em">'.htmlentities( $template->css, ENT_QUOTES, 'UTF-8' ).'</textarea>
		<br/>
		'.HtmlTag::create( 'div', $buttonList, ['class' => 'buttonbar'] ).'
	</div>
</div>';

$contentPreview	= '
	<div class="row-fluid">
		<div class="span12">
			<iframe id="frame-template-preview" src="./admin/mail/template/preview/'.$template->mailTemplateId.'"></iframe>
		</div>
	</div>';

$contentFacts	= $view->loadTemplateFile( 'admin/mail/template/edit.facts.php', ['buttonList' => $buttonList] );
$contentStyles	= $view->loadTemplateFile( 'admin/mail/template/edit.styles.php', ['buttonList' => $buttonList] );
$contentImages	= $view->loadTemplateFile( 'admin/mail/template/edit.images.php', ['buttonList' => $buttonList] );

$tabs		= new BootstrapTabs( 'admin-mail-template-edit' );
$tabs->add( 'admin-mail-template-edit-tab-facts', '#', $w->tabFacts, $contentFacts );
$tabs->add( 'admin-mail-template-edit-tab-text', '#', $w->tabText, $contentText );
$tabs->add( 'admin-mail-template-edit-tab-html', '#', $w->tabHtml, $contentHtml );
$tabs->add( 'admin-mail-template-edit-tab-css', '#', $w->tabCss, $contentCss );
$tabs->add( 'admin-mail-template-edit-tab-styles', '#', $w->tabStyles, $contentStyles );
$tabs->add( 'admin-mail-template-edit-tab-images', '#', $w->tabImages, $contentImages );
$tabs->add( 'admin-mail-template-edit-tab-preview', '#', $w->tabPreview, $contentPreview );
$tabs->setActive( $tabId ?: 'admin-mail-template-edit-tab-facts' );

$helperNav	= View_Helper_Pagination_PrevNext::create( $env )
	->setCurrentId( $template->mailTemplateId )
	->setUrlTemplate( './admin/mail/template/edit/%s' )
	->useIndex( TRUE )
	->setIndexUrl( './admin/mail/template' )
	->setModelClass( 'Model_Mail_Template' )
	->setOrderColumn( 'mailTemplateId' );
$navPrevNext	= HtmlTag::create( 'div', $helperNav->render(), ['class' => 'pull-right'] );

$heading3	= HtmlTag::create( 'h3', vsprintf( $w->heading, [
	'./admin/mail/template',
	htmlentities( $template->title, ENT_QUOTES, 'UTF-8' )
] ).$navPrevNext );

[$textTop, $textBottom]	= array_values( $view->populateTexts( ['top', 'bottom'], 'html/admin/mail/template/edit/' ) );

return $textTop.$heading3.$tabs.$textBottom;
