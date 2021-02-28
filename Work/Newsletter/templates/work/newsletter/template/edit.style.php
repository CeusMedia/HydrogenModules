<?php
$w				= (object) $words->styles;

//print_m( $template->newsletterTemplateId );die;

//  --  PANEL: FORM  --  //
$value			= htmlentities( $template->style, ENT_QUOTES, 'UTF-8' );
$panelForm		= '
<div class="content-panel">
	<h3>Style-Angaben für HTML-Format <small class="muted"></small></h3>
	<div class="content-panel-inner">
<!--		<label for="input_style">'.$words->edit->labelStyle.'</label>-->
		'.UI_HTML_Tag::create( 'textarea', $value, array(
			'name'		=> 'style',
			'id'		=> 'input_style',
			'class'		=> 'span12 CodeMirror-auto',
			'rows'		=> 30,
			'readonly'	=> $isUsed ? "readonly" : NULL,
//			'disabled'	=> $isUsed ? "disabled" : NULL,
		) ).'
		'.$buttons.'
	</div>
</div>';

$urlPreview			= './work/newsletter/template/preview/html/'.$template->newsletterTemplateId;
$iframeHtml			= UI_HTML_Tag::create( 'iframe', '', array(
	'src'			=> $urlPreview,
	'frameborder'	=> '0',
) );
$buttonPreviewHtml	= UI_HTML_Tag::create( 'button', '<i class="fa fa-fw fa-eye"></i>&nbsp;Vorschau', array(
	'type'			=> 'button',
	'class'			=> 'btn btn-info',
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

extract( $view->populateTexts( array( 'top', 'info', 'bottom' ), 'html/work/newsletter/template/style/' ) );

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
