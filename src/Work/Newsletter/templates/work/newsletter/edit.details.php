<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

/** @var Environment $env */
/** @var View $view */
/** @var object $words */
/** @var bool $tabbedLinks */
/** @var array $templates */
/** @var object $newsletter */
/** @var string $newsletterId */
/** @var bool $isUsed */
/** @var bool $useUserGroupRelations */
/** @var bool $canManageGroupRelations */
/** @var bool $canRemove */

$iconList		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-list'] ).'&nbsp;';
$iconPrev		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-left'] ).'&nbsp;';
$iconNext		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-right'] ).'&nbsp;';
$iconSave		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] ).'&nbsp;';
$iconPreview	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-eye'] ).'&nbsp;';
$iconAbort		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] ).'&nbsp;';
$iconRemove		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-trash'] ).'&nbsp;';

$wd			= (object) $words->edit_details;

$disabled		= (int) $newsletter->status !== 0 ? 'disabled="disabled"' : "";

$optStatus		= HtmlElements::Options( $words->states, $newsletter->status );

$optTemplate	= [];
foreach( $templates as $entry )
	$optTemplate[$entry->newsletterTemplateId]	= $entry->title;
$optTemplate	= HtmlElements::Options( $optTemplate, $newsletter->newsletterTemplateId );

$buttonSave		= HtmlTag::create( 'button', $iconSave.$words->edit->buttonSave, [
	'type'		=> 'submit',
	'class'		=> 'btn btn-primary',
	'name'		=> 'save',
	'disabled'	=> 	(int) $newsletter->status !== Model_Newsletter::STATUS_NEW ? 'disabled' : NULL,
] );
$buttonPreview	= HtmlTag::create( 'button', $iconPreview.$words->edit->buttonPreview, [
	'type'			=> "button",
	'class'			=> "btn btn-info",
	'data-toggle'	=> "modal",
	'data-target'	=> "#modal-preview",
	'onclick'		=> 'ModuleWorkNewsletter.showPreview(\'./work/newsletter/preview/html/'.$newsletterId.'/1\');'
] );
$buttonAbort	= '';
if( $env->getAcl()->has( 'work/newsletter', 'setStatus' ) )
	$buttonAbort	= HtmlTag::create( 'a', $iconAbort.$words->edit->buttonAbort, [
	'href'		=> './work/newsletter/setStatus/'.$newsletterId.'/-1',
	'class'		=> 'btn btn-inverse bs4-btn-dark btn-small',
	'disabled'	=> (int) $newsletter->status !== Model_Newsletter::STATUS_NEW ? 'disabled' : NULL,
] );
$buttonNext		= HtmlTag::Create( 'a', $iconNext.$words->edit->buttonNext, [
	'href'	=> './work/newsletter/setContentTab/'.$newsletterId.'/1',
	'class'	=> 'btn bs4-btn-secondary not-btn-small',
] );

$buttonList		= HtmlTag::create( 'a', $iconList.$words->edit->buttonList, ['href' => "./work/newsletter", 'class' => "btn"] );

$panelDetails	= '
<div class="row-fluid">
	<div class="span12">
		<form action="./work/newsletter/edit/'.$newsletterId.'" method="post">
			<div class="content-panel">
				<h3>'.$wd->heading.'</h3>
				<div class="content-panel-inner">
					<div class="row-fluid">
						<div class="span4">
							<label for="input_title">'.$words->edit->labelTitle.'</label>
							<input '.$disabled.' type="text" name="title" id="input_title" class="span12" required="required" value="'.htmlentities( $newsletter->title, ENT_QUOTES, 'UTF-8' ).'"/>
						</div>
						<div class="span4">
							<label for="input_newsletterTemplateId">'.$words->edit->labelTemplateId.'</label>
							<select '.$disabled.' name="newsletterTemplateId" id="input_newsletterTemplateId" class="span12" required="required">'.$optTemplate.'</select>
						</div>
						<div class="span2">
							<label for="input_trackingCode"><abbr title="'.$words->edit->labelTrackingCode_title.'">'.$words->edit->labelTrackingCode.'</abbr></label>
							<input '.$disabled.' type="text" name="trackingCode" id="input_trackingCode" class="span12" value="'.htmlentities( $newsletter->trackingCode ?? '', ENT_QUOTES, 'UTF-8' ).'"/>
						</div>
						<div class="span2">
							<label for="input_status">'.$words->edit->labelStatus.'</label>
							<input disabled="disabled" readonly="readonly" type="text" name="status" id="input_status" class="span12" value="'.$words->states[$newsletter->status].'"/>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span4">
							<label for="input_subject">'.$words->edit->labelSubject.'</label>
							<input '.$disabled.' type="text" name="subject" id="input_subject" class="span12" value="'.htmlentities( $newsletter->subject, ENT_QUOTES, 'UTF-8' ).'"/>
						</div>
						<div class="span4">
							<label for="input_senderAddress">'.$words->edit->labelSenderAddress.'</label>
							<input '.$disabled.' type="text" name="senderAddress" id="input_senderAddress" class="span12" required="required" value="'.htmlentities( $newsletter->senderAddress ?? '', ENT_QUOTES, 'UTF-8' ).'"/>
						</div>
						<div class="span4">
							<label for="input_senderName">'.$words->edit->labelSenderName.'</label>
							<input '.$disabled.' type="text" name="senderName" id="input_senderName" class="span12" value="'.htmlentities( $newsletter->senderName ?? '', ENT_QUOTES, 'UTF-8' ).'"/>
						</div>
					</div>
					<div class="row-fluid">
				<!--		<div class="span7">
							<label for="input_heading">'.$words->edit->labelHeading.'</label>
							<input '.$disabled.' type="text" name="heading" id="input_heading" class="span12" value="'.htmlentities( $newsletter->heading ?? '', ENT_QUOTES, 'UTF-8' ).'"/>
						</div>-->
				<!--		<div class="span6">
								<label for="input_status" class="checkbox">
								'.HtmlTag::create( 'input', NULL, [
									'type'		=> 'checkbox',
									'name'		=> 'status',
									'value'		=> Model_Newsletter::STATUS_READY,
									'id'		=> 'input_status',
									'readonly'	=> $isUsed ? 'readonly' : NULL,
									'disabled'	=> $isUsed ? 'disabled' : NULL,
									'checked'	=> $newsletter->status >= Model_Newsletter::STATUS_READY ? 'checked' : NULL,
								] ).'
									'.$words->edit->labelReady.'
								</label>
						</div>-->
					</div>
					<div class="buttonbar">
						'.$buttonList.'
						'.$buttonSave.'
						'./*$buttonPreview.*/'
						'.$buttonNext.'
						'.$buttonAbort.'
						'./*$buttonRemove.*/'
					</div>
				</div>
			</div>
		</form>
	</div>
</div>';

$panelFlow			= '';
$allowedToSetStatus	= $env->getAcl()->has( 'work/newsletter', 'setStatus' );

if( $newsletter->status == Model_Newsletter::STATUS_NEW && $allowedToSetStatus ){
	$url		= './work/newsletter/setStatus/'.$newsletterId.'/'.Model_Newsletter::STATUS_READY.'/?forwardTo='.urlencode( 'setContentTab/'.$newsletterId.'/3' );
	$panelFlow	= '
<div class="content-panel">
	<h3>'.$wd->headingReady.'</h3>
	<div class="content-panel-inner">
		<div class="row-fluid">
			<div class="span8">
				<p>
					'.$wd->textReady.'<br/>
					<span class="label label-warning">'.$wd->labelReadyWarning.'</span>
				</p>
			</div>
			<div class="span4">
				<a class="btn btn-large btn-primary" href="'.$url.'">'.$wd->buttonReady.'</a>
			</div>
		</div>
	</div>
</div>';

}
else if( $newsletter->status == Model_Newsletter::STATUS_READY && $allowedToSetStatus ){
	$url		= './work/newsletter/setStatus/'.$newsletterId.'/'.Model_Newsletter::STATUS_NEW;
	$panelFlow	= '
<div class="content-panel">
	<h3>'.$wd->headingUnready.'</h3>
	<div class="content-panel-inner">
		<div class="row-fluid">
			<div class="span7">
				<p>
					'.$wd->textUnready.'
				</p>
			</div>
			<div class="span1">
			</div>
			<div class="span4">
				<a class="btn btn-large btn-primary" href="'.$url.'">'.$wd->buttonUnready.'</a>
			</div>
		</div>
	</div>
</div>';
}
else if( $newsletter->status == Model_Newsletter::STATUS_ABORTED && $allowedToSetStatus ){
	$url	= './work/newsletter/setStatus/'.$newsletterId.'/'.Model_Newsletter::STATUS_NEW;
	$panelFlow	= '
<div class="content-panel">
	<h3>'.$wd->headingReopen.'</h3>
	<div class="content-panel-inner">
		<div class="row-fluid">
			<div class="span7">
				<p>
					'.$wd->textReopen.'<br/>
				</p>
			</div>
			<div class="span1">
			</div>
			<div class="span4">
				<a class="btn btn-large btn-primary" href="'.$url.'">'.$wd->buttonReopen.'</a>
			</div>
		</div>
	</div>
</div>';
}

/*  --  PANEL: PREVIEW: HTML  --  */
$helper	= new View_Helper_Work_Newsletter_Preview( $env );
$helper->setFormat( View_Helper_Work_Newsletter_Preview::FORMAT_HTML );
$helper->setMode( View_Helper_Work_Newsletter_Preview::MODE_NEWSLETTER );
$helper->setResource( $newsletter );
$helper->setClass( 'scale-2x half-size' );
$panelPreviewHtml	= $helper->render();

/*  --  PANEL: PREVIEW: TEXT  --  */
$helper	= new View_Helper_Work_Newsletter_Preview( $env );
$helper->setFormat( View_Helper_Work_Newsletter_Preview::FORMAT_PLAIN );
$helper->setMode( View_Helper_Work_Newsletter_Preview::MODE_NEWSLETTER );
$helper->setResource( $newsletter );
$helper->setClass( 'scale-2x half-size' );
$panelPreviewText	= $helper->render();

$panelRemove		= '';
if( $canRemove && $newsletter->status < Model_Newsletter::STATUS_SENT ){
	$buttonRemove		= HtmlTag::create( 'a', $iconRemove.$words->edit->buttonRemove, [
		'href'		=> './work/newsletter/remove/'.$newsletterId,
		'class'		=> 'btn btn-danger btn-small',
		'disabled'	=> (int) $newsletter->status >= Model_Newsletter::STATUS_SENT ? 'disabled' : NULL,
	] );
	$panelRemove	= '
<div class="content-panel">
	<h3>'.$wd->headingRemove.'</h3>
	<div class="content-panel-inner">
		<p>
			'.$wd->textRemove.'
		</p>
		<div class="alert alert-info">
			'.$wd->alertRemove.'
		</div>
		<div class="buttonbar">
			'.$buttonRemove.'
		</div>
	</div>
</div>';
}

//  --  PANEL: USER GROUPS  --  //
$helperPanelGroups	= new View_Helper_Manage_Group_EntityRelationEditor( $this->env );
$panelGroups		= $helperPanelGroups
	->setModule( 'Resource_Newsletter' )
	->visible( $useUserGroupRelations )
	->enable( $canManageGroupRelations && $newsletter->status < Model_Newsletter::STATUS_SENT )
	->setFrom( 'work/newsletter/edit/'.$newsletterId )
	->setEntityId( $newsletterId )
	->setSize( 4 )
	->render();

return '
<div class="row-fluid">
	<div class="span8">
		'.$panelDetails.'
		'.$panelGroups.'
		'.$panelFlow.'
		'.$panelRemove.'
	</div>
	<div class="span4">
		'.$panelPreviewHtml.'
		'.$panelPreviewText.'
	</div>
</div>';
