<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

extract( $view->populateTexts( array( 'top', 'info', 'bottom' ), 'html/work/newsletter/template/html' ) );
extract( $view->populateTexts( array( 'placeholders' ), 'html/work/newsletter/template/' ) );

//  --  PANEL: PREVIEW  --  //
$urlPreview			= './work/newsletter/template/preview/html/'.$template->newsletterTemplateId;
$iframeHtml			= HtmlTag::create( 'iframe', '', array(
	'src'			=> $urlPreview,
	'frameborder'	=> '0',
) );
$buttonPreviewHtml	= HtmlTag::create( 'button', '<i class="fa fa-fw fa-eye"></i>&nbsp;Vorschau', array(
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

$value		= htmlentities( $template->html, ENT_QUOTES, 'UTF-8' );
$content	= $textTop.'
<div class="row-fluid">
	<div class="span7">
		<div class="content-panel">
			<h3>'.$words->edit->labelHtml.'
				<div class="pull-right">
					<a href="#modal-newsletter-template-placeholders" role="button" class="btn btn-mini not-btn-info" data-toggle="modal"><i class="fa fa-fw fa-info-circle"></i>&nbsp;Hilfe</a>
				</div>
			</h3>
			<div class="content-panel-inner">
				'.HtmlTag::create( 'textarea', $value, array(
					'name'		=> 'html',
					'id'		=> 'input_html',
					'class'		=> 'CodeMirror-auto',
					'rows'		=> 30,
					'readonly'	=> $isUsed ? "readonly" : NULL,
//					'disabled'	=> $isUsed ? "disabled" : NULL,
				) ).'
				'.$buttons.'
			</div>
		</div>
	</div>
	<div class="span5">
		'.$panelPreview.'
	</div>
<!--	<div class="span3">
		'.$textInfo.'
	</div>-->
</div>
'.$textBottom.'
'.$textPlaceholders;

return $content;
