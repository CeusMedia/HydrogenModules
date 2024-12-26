<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

/** @var Environment $env */
/** @var View $view */
/** @var View_Work_Newsletter_Template $this */
/** @var object $words */
/** @var bool $tabbedLinks */
/** @var object $template */
/** @var string $templateId */
/** @var string $format */

$tabsMain		= $tabbedLinks ? $view->renderMainTabs() : '';

$isUsed			= FALSE;
$currentTab		= (int) $this->env->getSession()->get( 'work.newsletter.template.content.tab' );
$tabs			= $words->tabs;
$tabsContent	= $view->renderTabs( $tabs, 'template/setContentTab/'.$templateId.'/', $currentTab );

$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-left'] ).'&nbsp;';
$iconSave		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] ).'&nbsp;';
$iconPreview	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-eye'] ).'&nbsp;';
$iconRemove		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] ).'&nbsp;';
$iconCopy		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-clone'] ).'&nbsp;';
$iconRefresh	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-refresh'] ).'&nbsp;';
$iconExport		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-download'] ).'&nbsp;';

$buttonCancel	= HtmlTag::create( 'a', $iconCancel.$words->edit->buttonCancel, [
	'class'		=> "btn btn-small",
	'href'		=> "./work/newsletter/template/index",
] );
$buttonSave		= HtmlTag::create( 'button', $iconSave.$words->edit->buttonSave, [
	'type'			=> "submit",
	'class'			=> "btn btn-primary".( $isUsed ? ' disabled' : '' ),
	'name'			=> "save",
	'readonly'		=> $isUsed ? 'readonly' : NULL,
	'onmousedown'	=> $isUsed ? "alert('".$words->edit->buttonSaveDisabled."');" : NULL,
] );
$buttonPreview	= HtmlTag::create( 'button', $iconPreview.$words->edit->buttonPreview, [
	'type'			=> "button",
	'class'			=> "btn btn-info",
	'data-toggle'	=> "modal",
	'data-target'	=> "#modal-preview",
	'onclick'		=> 'ModuleWorkNewsletter.showPreview("./work/newsletter/template/preview/'.$format.'/'.$templateId.'");'
] );
/*
$buttonPreview	= HtmlTag::create( 'a', $iconPreview.$words->edit->buttonPreview, [
	'class'		=> "btn btn-info",
	'href'		=> './work/newsletter/template/preview/'.$format.'/'.$templateId.'/1',
	'target'	=> "NewsletterTemplatePreview",
] );*/
$buttonRemove	= HtmlTag::create( 'a', $iconRemove.$words->edit->buttonRemove, [
	'class'		=> "btn btn-danger",
	'href'		=> $isUsed ? '#' : "./work/newsletter/template/remove/".$templateId,
	'disabled'	=> $isUsed ? 'disabled' : NULL,
	'onclick'	=> $isUsed ? "alert('".$words->edit->buttonRemoveDisabled."'); return false;" : NULL,
] );
$buttonCopy		= HtmlTag::create( 'a', $iconCopy.$words->edit->buttonCopy, [
	'class'		=> "btn btn-success btn-small",
	'href'		=> "./work/newsletter/template/add?templateId=".$templateId
] );
$buttonExport	= HtmlTag::create( 'a', $iconExport.$words->edit->buttonExport, [
	'class'		=> "btn",
	'href'		=> "./work/newsletter/template/export/".$templateId
] );

$buttons		= HtmlTag::create( 'div', join( ' ', [
	$buttonCancel,
	$buttonSave,
	$buttonPreview,
	$buttonExport,
//	$buttonRemove,
//	$buttonCopy,
] ), ['class' => 'buttonbar'] );

$pathTemplates	= 'work/newsletter/template/';

$content = match( $currentTab ){
	0		=> $view->loadTemplateFile( $pathTemplates.'edit.details.php', ['buttons' => $buttons] ),
	1		=> $view->loadTemplateFile( $pathTemplates.'edit.html.php', ['buttons' => $buttons] ),
	2		=> $view->loadTemplateFile( $pathTemplates.'edit.text.php', ['buttons' => $buttons] ),
	3		=> $view->loadTemplateFile( $pathTemplates.'edit.style.php', ['buttons' => $buttons] ),
	4		=> $view->loadTemplateFile( $pathTemplates.'edit.styles.php', ['buttons' => $buttons] ),
	default	=> throw new InvalidArgumentException('Invalid tab: ' . $currentTab),
};
$tabsContent	.= HtmlTag::create( 'div', $content, ['tab-content'] );

$modalPreview	= '
<div id="modal-preview" class="modal hide -fade preview">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3>'.sprintf( $words->preview->heading, $template->title ).'</h3>
	</div>
	<div class="modal-body">
		<div>
			<iframe></iframe>
		</div>
	</div>
	<div class="modal-footer">
<!--		<button type="button" class="btn btn-info" id="preview-refresh">'.$iconRefresh.$words->preview->buttonRefresh.'</button>-->
<!--		<button type="button" class="btn btn-small btn-warning" onclick="ModuleWorkNewsletter.showPreview(\'./work/newsletter/template/preview/'.$format.'/'.$templateId.'/1/1\');">'.$words->preview->buttonOffline.'</button>-->
		<button type="button" class="btn" data-dismiss="modal" aria-hidden="true">'.$iconRemove.$words->preview->buttonClose.'</button>
	</div>
</div>';

$modalStyleAdd	= '
<div id="modal-style-add" class="modal hide fade">
	<form action="./work/newsletter/template/addStyle/'.$templateId.'" method="post">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3><small class="muted"></small>'.$words->addStyle->heading.'</h3>
	</div>
	<div class="modal-body">
		<div class="row-fluid">
			<label for="input_style_url">'.$words->addStyle->labelUrl.'</label>
			<input type="text" name="style_url" id="input_style_url" class="span12"/>
		</div>
	</div>
	<div class="modal-footer">
		<button type="button" class="btn" data-dismiss="modal" aria-hidden="true">'.$words->addStyle->buttonCancel.'</button>
		<button type="submit" class="btn btn-success">'.$words->addStyle->buttonAdd.'</button>
	</div>
	</form>
</div>';

extract( $view->populateTexts( ['above', 'bottom', 'top'], 'html/work/newsletter/template/edit/', [
	'words'		=> $words,
	'template'	=> $template
] ) );

return $textTop.'
<div class="newsletter-content">
	'.$tabsMain.'
	<!--<a href="./work/newsletter/template" class="btn btn-mini">'.$iconCancel.$words->edit->buttonList.'</a>-->
	'.$textAbove.'
	<form action="./work/newsletter/template/edit/'.$templateId.'" method="post">
		'.$tabsContent.'
	</form>
</div>'.$textBottom.$modalPreview.$modalStyleAdd;
