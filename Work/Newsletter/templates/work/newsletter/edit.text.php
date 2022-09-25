<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconList		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-list' ) ).'&nbsp;';
$iconPrev		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) ).'&nbsp;';
$iconNext		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-right' ) ).'&nbsp;';
$iconCancel		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) ).'&nbsp;';
$iconSave		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) ).'&nbsp;';
$iconPreview	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-eye' ) ).'&nbsp;';
$iconRemove		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) ).'&nbsp;';


//  --  PANEL: PREVIEW  --  //
$urlPreview			= './work/newsletter/preview/text/'.$newsletter->newsletterId;
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

//  --  PANEL: FORM  --  //
$w				= (object) $words->edit_text;

$fieldContent	= '
<div class="row-fluid">
	<label for="input_plain">'.$w->labelContent.'</label>
	<textarea name="plain" id="input_plain" class="span12 ace-auto" rows="20" data-ace-option-max-lines="20">'.htmlentities( $newsletter->plain, ENT_QUOTES, 'UTF-8' ).'</textarea>
</div>';

if( $newsletter->generatePlain ){
	$fieldContent	= '
	<div class="row-fluid">
		<label for="input_plain">'.$w->labelContent.'</label>
		<textarea name="plain" id="input_plain" class="span12 ace-auto" rows="20" data-ace-option-max-lines="20" disabled="disabled">'.htmlentities( $newsletter->plain, ENT_QUOTES, 'UTF-8' ).'</textarea>
	</div>';
}

$buttonSave		= HtmlTag::create( 'button', $iconSave.$words->edit->buttonSave, array(
	'type'		=> 'submit',
	'name'		=> 'save',
	'class'		=> 'btn btn-primary',
	'disabled'	=> (int) $newsletter->status !== Model_Newsletter::STATUS_NEW ? 'disabled' : NULL,
) );

$buttonPreview	= HtmlTag::create( 'a', $iconPreview.$words->edit->buttonPreview, array(
	'href'		=> './work/newsletter/preview/text/'.$newsletterId.'/1',
	'target'	=> 'NewsletterPreview',
	'class'		=> 'btn btn-info btn-small',
) );

$buttonPreview	= HtmlTag::create( 'a', $iconPreview.$words->edit->buttonPreview, array(
	'type'			=> "button",
	'class'			=> "btn btn-info",
	'data-toggle'	=> "modal",
	'data-target'	=> "#modal-preview",
	'onclick'		=> 'ModuleWorkNewsletter.showPreview(\'./work/newsletter/preview/text/'.$newsletterId.'/1\');'
) );

$buttonPrev		= HtmlTag::Create( 'a', $iconNext.$words->edit->buttonPrev, array(
	'href'	=> './work/newsletter/setContentTab/'.$newsletterId.'/1',
	'class'	=> 'btn not-btn-small',
) );
$buttonNext		= HtmlTag::Create( 'a', $iconNext.$words->edit->buttonNext, array(
	'href'	=> './work/newsletter/setContentTab/'.$newsletterId.'/3',
	'class'	=> 'btn not-btn-small',
) );

$panelForm	= '
<div class="content-panel content-panel-form">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<form action="./work/newsletter/edit/'.$newsletterId.'" method="post">
			<div class="row-fluid">
				<label for="input_generatePlain" class="checkbox">
					'.HtmlTag::create( 'input', NULL, array(
						'type'		=> "checkbox",
						'name'		=> "generatePlain",
						'id'		=> 'input_generatePlain',
						'value'		=> 1,
						'checked'	=> $newsletter->generatePlain ? 'checked' : NULL,
					) ).' '.$w->labelGeneratePlain.'</label>
			</div>
			'.$fieldContent.'
			<div class="buttonbar">
				<a href="./work/newsletter" class="btn not-btn-small">'.$iconList.$words->edit->buttonList.'</span></a>
				'.$buttonPrev.'
				'.$buttonSave.'
<!--				'.$buttonPreview.'-->
				'.$buttonNext.'
			</div>
		</form>
	</div>
</div>';

return '
<div class="row-fluid">
	<div class="span7">
		'.$panelForm.'
	</div>
	<div class="span5">
		'.$panelPreview.'
	</div>
</div>';
