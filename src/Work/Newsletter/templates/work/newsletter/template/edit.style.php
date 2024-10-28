<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

/** @var Environment $env */
/** @var View $view */
/** @var object $words */
/** @var object $template */
/** @var bool $isUsed */

$w				= (object) $words->styles;

//print_m( $template->newsletterTemplateId );die;

//  --  PANEL: FORM  --  //
$value			= htmlentities( $template->style, ENT_QUOTES, 'UTF-8' );
$panelForm		= '
<div class="content-panel">
	<h3>Style-Angaben für HTML-Format <small class="muted"></small></h3>
	<div class="content-panel-inner">
<!--		<label for="input_style">'.$words->edit->labelStyle.'</label>-->
		'.HtmlTag::create( 'textarea', $value, [
			'name'		=> 'style',
			'id'		=> 'input_style',
			'class'		=> 'span12 CodeMirror-auto',
			'rows'		=> 30,
			'readonly'	=> $isUsed ? "readonly" : NULL,
//			'disabled'	=> $isUsed ? "disabled" : NULL,
		] ).'
		'.$buttons.'
	</div>
</div>';

$urlPreview			= './work/newsletter/template/preview/html/'.$template->newsletterTemplateId;
$iframeHtml			= HtmlTag::create( 'iframe', '', [
	'src'			=> $urlPreview,
	'frameborder'	=> '0',
] );
$buttonPreviewHtml	= HtmlTag::create( 'button', '<i class="fa fa-fw fa-eye"></i>&nbsp;Vorschau', [
	'type'			=> 'button',
	'class'			=> 'btn btn-info',
	'data-toggle'	=> 'modal',
	'data-target'	=> '#modal-preview',
	'onclick'		=> 'ModuleWorkNewsletter.showPreview("'.$urlPreview.'");',
] );
$panelPreview	= '
<div class="content-panel">
	<h4>
		<span>HTML-Vorschau</span>
		<div style="float: right">
			'.$buttonPreviewHtml.'
		</div>
	</h4>
	<div class="content-panel-inner">
		<div id="newsletter-preview">
			<div id="newsletter-preview-container">
		 		<div id="newsletter-preview-iframe-container">
					'.$iframeHtml.'
				</div>
			</div>
		</div>
	</div>
</div>';

extract( $view->populateTexts( ['top', 'info', 'bottom'], 'html/work/newsletter/template/style/' ) );

return $textTop.'
<div class="row-fluid">
	<div class="span6">
		'.$panelForm.'
	</div>
	<div class="span6">
		'.$textInfo.'
		'.$panelPreview.'
	</div>
</div>'.$textBottom;
