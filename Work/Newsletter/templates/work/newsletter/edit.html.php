<?php

$iconList		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-list' ) ).'&nbsp;';
$iconPrev		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) ).'&nbsp;';
$iconNext		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-right' ) ).'&nbsp;';
$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) ).'&nbsp;';
$iconSave		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) ).'&nbsp;';
$iconPreview	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-eye' ) ).'&nbsp;';
$iconRemove		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) ).'&nbsp;';
$iconExternal	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-external-link' ) ).'&nbsp;';

//  --  PANEL: PREVIEW  --  //
$iframeHtml			= UI_HTML_Tag::create( 'iframe', '', array(
	'src'			=> './work/newsletter/preview/html/'.$newsletter->newsletterId,
	'frameborder'	=> '0',
) );
$buttonPreviewHtml	= UI_HTML_Tag::create( 'button', '<i class="fa fa-fw fa-eye"></i>&nbsp;Vorschau', array(
	'type'			=> 'button',
	'class'			=> 'btn btn-info btn-mini',
	'data-toggle'	=> 'modal',
	'data-target'	=> '#modal-preview',
	'onclick'		=> 'ModuleWorkNewsletter.showPreview("./work/newsletter/preview/html/'.$newsletter->newsletterId.'");',
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

//  --  PANEL: STYLE LIST  --  //
$w				= (object) $words->edit_html_styles;
$panelStyles	= '';
if( $styles ){
	$listStyles		= array();
	foreach( $styles as $nr => $item ){
//		$label			= preg_replace( "@^([a-z]+://[^/]+/)@", '<small class="muted">\\1</small>', $item );
		$link			= UI_HTML_Tag::create( 'a', $iconExternal.$item, array(
			'href'		=> $item,
			'target'	=> '_blank',
			'class'		=> 'autocut',
		) );
		$styles[$nr]	= UI_HTML_Tag::create( 'li', $link );
	}
	$listStyles		= UI_HTML_Tag::create( 'ul', $styles, array( 'class' => "unstyled" ) );
	$panelStyles	= '
<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		'.( $w->labelList ? UI_HTML_Tag::create( 'p', $w->labelList ) : '' ).'
		'.$listStyles.'
	</div>
</div>';
}

$buttonSave		= UI_HTML_Tag::create( 'button', $iconSave.$words->edit->buttonSave, array(
	'type'		=> 'submit',
	'class'		=> 'btn btn-primary',
	'name'		=> 'save',
	'disabled'	=> (int) $newsletter->status !== Model_Newsletter::STATUS_NEW ? 'disabled' : NULL,
) );


$buttonPreview	= UI_HTML_Tag::create( 'a', $iconPreview.$words->edit->buttonPreview, array(
	'href'		=> './work/newsletter/preview/html/'.$newsletterId.'/1',
	'target'	=> 'NewsletterPreview',
	'class'		=> 'btn btn-info btn-small',
) );

$buttonPreview	= UI_HTML_Tag::create( 'a', $iconPreview.$words->edit->buttonPreview, array(
	'type'			=> "button",
	'class'			=> "btn btn-info",
	'data-toggle'	=> "modal",
	'data-target'	=> "#modal-preview",
	'onclick'		=> 'ModuleWorkNewsletter.showPreview(\'./work/newsletter/preview/html/'.$newsletterId.'/1\');'
) );


$buttonPrev		= UI_HTML_Tag::Create( 'a', $iconNext.$words->edit->buttonPrev, array(
	'href'	=> './work/newsletter/setContentTab/'.$newsletterId.'/0',
	'class'	=> 'btn not-btn-small',
) );
$buttonNext		= UI_HTML_Tag::Create( 'a', $iconNext.$words->edit->buttonNext, array(
	'href'	=> './work/newsletter/setContentTab/'.$newsletterId.'/2',
	'class'	=> 'btn not-btn-small',
) );

//  --  PANEL: FORM  --  //
$w				= (object) $words->edit_html;
$value			= htmlentities( $newsletter->html, ENT_QUOTES, 'UTF-8' );
//$value		= strlen( $value ) ? $value : $view->loadContentFile( $pathDefaults.'default.html');
$urlFull		= './work/newsletter/editFull/'.$newsletter->newsletterId;
$buttonFull		= '';//UI_HTML_Tag::create( 'a', $w->buttonFullscreen, array( 'class' => 'btn btn-mini', 'href' => $urlFull ) );
$disabled		= (int) $newsletter->status !== Model_Newsletter::STATUS_NEW ? 'disabled="disabled"' : "";
$panelForm		= '
<div class="content-panel content-panel-form">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<form action="./work/newsletter/edit/'.$newsletterId.'" method="post">
		<!--	<div class="row-fluid">
				<div class="span12">
					<label for="input_html">'.$w->labelContent.'&nbsp;'.$buttonFull.'</label>-->
					<textarea name="html" id="input_html" class="not-TinyMCE" data-tinymce-mode="extended" data-tinymce-relative="false" rows="20">'.$value.'</textarea>
		<!--		</div>
			</div>-->
			<div class="buttonbar">
				<a href="./work/newsletter" class="btn not-btn-small">'.$iconList.$words->edit->buttonList.'</span></a>
				'.$buttonPrev.'
				'.$buttonSave.'
<!--				'.$buttonPreview.'-->
				'.$buttonNext.'
			</div>
		</form>
	</div>
</div>
';

return '
<div class="row-fluid">
	<div class="span7">
		'.$panelForm.'
	</div>
	<div class="span5">
		'.$panelPreview.'
	</div>
</div>
<div class="row-fluid">
	<div class="span6">
		'.$panelStyles.'
	</div>
</div><br/>
<script>
jQuery(document).ready(function(){
	ModuleWorkNewsletter.init();
});
</script>';
