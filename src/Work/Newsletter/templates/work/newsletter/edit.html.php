<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use CeusMedia\HydrogenFramework\View;

/** @var WebEnvironment $env */
/** @var View $view */
/** @var object $words */
/** @var object $newsletter */
/** @var string $newsletterId */
/** @var array $styles */

$env->getPage()->js->addScriptOnReady( 'ModuleWorkNewsletter.init("'.$env->url.'", '.$newsletter->newsletterTemplateId.', 0);' );

$iconList		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-list'] ).'&nbsp;';
$iconPrev		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-left'] ).'&nbsp;';
$iconNext		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-right'] ).'&nbsp;';
$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-left'] ).'&nbsp;';
$iconSave		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] ).'&nbsp;';
$iconPreview	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-eye'] ).'&nbsp;';
$iconRemove		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] ).'&nbsp;';
$iconExternal	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-external-link'] ).'&nbsp;';

//  --  PANEL: PREVIEW  --  //
$helper	= new View_Helper_Work_Newsletter_Preview( $env );
$helper->setFormat( View_Helper_Work_Newsletter_Preview::FORMAT_HTML );
$helper->setMode( View_Helper_Work_Newsletter_Preview::MODE_NEWSLETTER );
$helper->setResource( $newsletter );
$helper->setClass( 'scale-3x' );
$panelPreview	= $helper->render();

//  --  PANEL: STYLE LIST  --  //
$w				= (object) $words->edit_html_styles;
$panelStyles	= '';
if( $styles ){
	$listStyles		= [];
	foreach( $styles as $nr => $item ){
//		$label			= preg_replace( "@^([a-z]+://[^/]+/)@", '<small class="muted">\\1</small>', $item );
		$link			= HtmlTag::create( 'a', $iconExternal.$item, [
			'href'		=> $item,
			'target'	=> '_blank',
			'class'		=> 'autocut',
		] );
		$styles[$nr]	= HtmlTag::create( 'li', $link );
	}
	$listStyles		= HtmlTag::create( 'ul', $styles, ['class' => "unstyled"] );
	$panelStyles	= '
<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		'.( $w->labelList ? HtmlTag::create( 'p', $w->labelList ) : '' ).'
		'.$listStyles.'
	</div>
</div>';
}

$buttonSave		= HtmlTag::create( 'button', $iconSave.$words->edit->buttonSave, [
	'type'		=> 'submit',
	'class'		=> 'btn btn-primary',
	'name'		=> 'save',
	'disabled'	=> (int) $newsletter->status !== Model_Newsletter::STATUS_NEW ? 'disabled' : NULL,
] );

$buttonPreview	= HtmlTag::create( 'a', $iconPreview.$words->edit->buttonPreview, [
	'href'		=> './work/newsletter/preview/html/'.$newsletterId.'/1',
	'target'	=> 'NewsletterPreview',
	'class'		=> 'btn btn-info btn-small',
] );

$buttonPreview	= HtmlTag::create( 'a', $iconPreview.$words->edit->buttonPreview, [
	'type'			=> "button",
	'class'			=> "btn btn-info",
	'data-toggle'	=> "modal",
	'data-target'	=> "#modal-preview",
	'onclick'		=> 'ModuleWorkNewsletter.showPreview(\'./work/newsletter/preview/html/'.$newsletterId.'/1\');'
] );

//  -- BUTTONS: PREV / NEXT  --  //
$buttonPrev		= HtmlTag::Create( 'a', $iconPrev.$words->edit->buttonPrev, [
	'href'	=> './work/newsletter/setContentTab/'.$newsletterId.'/0',
	'class'	=> 'btn not-btn-small',
] );
$buttonNext		= HtmlTag::Create( 'a', $iconNext.$words->edit->buttonNext, [
	'href'	=> './work/newsletter/setContentTab/'.$newsletterId.'/2',
	'class'	=> 'btn not-btn-small',
] );

//  --  PANEL: FORM  --  //
$w				= (object) $words->edit_html;
$value			= htmlentities( $newsletter->html, ENT_QUOTES, 'UTF-8' );
//$value		= strlen( $value ) ? $value : $view->loadContentFile( $pathDefaults.'default.html');
$urlFull		= './work/newsletter/editFull/'.$newsletter->newsletterId;
$buttonFull		= '';//HtmlTag::create( 'a', $w->buttonFullscreen, ['class' => 'btn btn-mini', 'href' => $urlFull] );
$disabled		= (int) $newsletter->status !== Model_Newsletter::STATUS_NEW ? 'disabled="disabled"' : "";

$buttonList		= HtmlTag::create( 'a', $iconList.$words->edit->buttonList, ['href' => "./work/newsletter", 'class' => "btn"] );
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
				'.$buttonList.'
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
	<div class="span8">
		'.$panelForm.'
	</div>
	<div class="span4">
		'.$panelPreview.'
	</div>
</div>
<div class="row-fluid">
	<div class="span6">
		'.$panelStyles.'
	</div>
</div><br/>';

