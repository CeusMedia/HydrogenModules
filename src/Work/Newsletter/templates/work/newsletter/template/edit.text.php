<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

/** @var Environment $env */
/** @var View $view */
/** @var object $words */
/** @var object $template */

extract( $view->populateTexts( ['top', 'info', 'bottom'], 'html/work/newsletter/template/text' ) );
extract( $view->populateTexts( ['placeholders'], 'html/work/newsletter/template/' ) );

//  --  PANEL: PREVIEW  --  //
$urlPreview			= './work/newsletter/template/preview/text/'.$template->newsletterTemplateId;
$iframeText			= HtmlTag::create( 'iframe', '', array(
	'src'			=> $urlPreview,
	'frameborder'	=> '0',
) );
$buttonPreviewText	= HtmlTag::create( 'button', '<i class="fa fa-fw fa-eye"></i>&nbsp;Vorschau', array(
	'type'			=> 'button',
	'class'			=> 'btn btn-info btn-mini',
	'data-toggle'	=> 'modal',
	'data-target'	=> '#modal-preview',
	'onclick'		=> 'ModuleWorkNewsletter.showPreview("'.$urlPreview.'");',
) );
$panelPreview	= '
<div class="content-panel">
	<h4>
		<span>HTML-Vorschau</span>
		<div style="float: right">
			'.$buttonPreviewText.'
		</div>
	</h4>
	<div class="content-panel-inner">
		<div id="newsletter-preview">
			<div id="newsletter-preview-container">
		 		<div id="newsletter-preview-iframe-container">
					'.$iframeText.'
				</div>
			</div>
		</div>
	</div>
</div>';

$textarea		= HtmlTag::create( 'textarea', $template->plain, array(
	'name'		=> 'plain',
	'id'		=> 'input_plain',
	'class'		=> 'span12 CodeMirror-auto',
	'rows'		=> 30,
	'readonly'	=> $isUsed ? "readonly" : NULL,
//	'disabled'	=> $isUsed ? "disabled" : NULL,
) );

$content	= $textTop.'
<div class="row-fluid">
	<div class="span7">
		<div class="content-panel">
			<h3>
				'.$words->edit->labelPlain.'
				<div class="pull-right">
					<a href="#modal-newsletter-template-placeholders" role="button" class="btn btn-mini not-btn-info" data-toggle="modal"><i class="fa fa-fw fa-info-circle"></i>&nbsp;Hilfe</a>
				</div>
			</h3>
			<div class="content-panel-inner">
				'.$textarea.'
				'.$buttons.'
			</div>
		</div>
	</div>
	<div class="span5">
		'.$textInfo.'
		'.$panelPreview.'
	</div>
</div>'.$textBottom.$textPlaceholders;

return $content;
