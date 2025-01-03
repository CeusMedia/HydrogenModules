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

$iconList		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-list'] ).'&nbsp;';
$iconPrev		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-left'] ).'&nbsp;';
$iconNext		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-right'] ).'&nbsp;';
$iconSave		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] ).'&nbsp;';
$iconPreview	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-eye'] ).'&nbsp;';
$iconAbort		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] ).'&nbsp;';
$iconRemove		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-trash'] ).'&nbsp;';

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
$buttonAbort		= HtmlTag::create( 'a', $iconAbort.$words->edit->buttonAbort, [
	'href'		=> './work/newsletter/setStatus/'.$newsletterId.'/-1',
	'class'		=> 'btn btn-inverse bs4-btn-dark btn-small',
	'disabled'	=> (int) $newsletter->status !== Model_Newsletter::STATUS_NEW ? 'disabled' : NULL,
] );
$buttonRemove		= HtmlTag::create( 'a', $iconRemove.$words->edit->buttonRemove, [
	'href'		=> './work/newsletter/remove/'.$newsletterId,
	'class'		=> 'btn btn-danger btn-small',
	'disabled'	=> (int) $newsletter->status >= Model_Newsletter::STATUS_SENT ? 'disabled' : NULL,
] );
$buttonNext		= HtmlTag::Create( 'a', $iconNext.$words->edit->buttonNext, [
	'href'	=> './work/newsletter/setContentTab/'.$newsletterId.'/1',
	'class'	=> 'btn bs4-btn-secondary not-btn-small',
] );

$panelDetails	= '
<div class="row-fluid">
	<div class="span12">
		<form action="./work/newsletter/edit/'.$newsletterId.'" method="post">
			<div class="content-panel">
				<h3>Grundlegende Daten</h3>
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
						<a href="./work/newsletter" class="btn not-btn-small">'.$iconList.$words->edit->buttonList.'</span></a>
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

$extras		= '';
if( $newsletter->status == Model_Newsletter::STATUS_NEW ){
	$url	= './work/newsletter/setStatus/'.$newsletterId.'/1/?forwardTo='.urlencode( 'setContentTab/'.$newsletterId.'/3' );
	$extras	= '
<div class="content-panel">
	<h3>Bereit zum Versand?</h3>
	<div class="content-panel-inner">
		<div class="row-fluid">
			<div class="span8">
				<p>
					Wenn Sie alle Inhalte der Kampagne eingegeben und mit der Vorschau geprüft haben,
					können Sie die Kampagne auf "bereit" stellen und damit den Versand ermöglichen.<br/>
					<span class="label label-warning">Achtung: Die Kampagne kann dann nicht mehr verändert werden.</span>
				</p>
			</div>
			<div class="span4">
				<a class="btn btn-large btn-primary" href="'.$url.'">bereitmachen</a>
			</div>
		</div>
	</div>
</div>';

}
else if( $newsletter->status == Model_Newsletter::STATUS_READY ){
	$url	= './work/newsletter/edit/'.$newsletterId.'?save&status=0';
	$extras	= '
<div class="content-panel">
	<h3>Doch nicht fertig zum Versand?</h3>
	<div class="content-panel-inner">
		<div class="row-fluid">
			<div class="span7">
				<p>
					Falls Sie diese versandbereite Kampagne doch noch einmal ändern müssen,
					können Sie die Kampagne hier wieder für Änderungen freischalten.<br/>
					Die Kampagne muss dann wieder getestet werden.
				</p>
			</div>
			<div class="span1">
			</div>
			<div class="span4">
				<a class="btn btn-large btn-primary" href="'.$url.'">weiter bearbeiten</a>
			</div>
		</div>
	</div>
</div>';
}
else if( $newsletter->status == Model_Newsletter::STATUS_ABORTED ){
	$url	= './work/newsletter/edit/'.$newsletterId.'?save&status=0';
	$extras	= '
<div class="content-panel">
	<h3>Arbeit am Newsletter wieder aufnehmen</h3>
	<div class="content-panel-inner">
		<div class="row-fluid">
			<div class="span7">
				<p>
					Falls Sie diese verworfene Kampagne doch weiter bearbeiten wollen,
					können Sie die Kampagne hier wieder für Änderungen freischalten.<br/>
				</p>
			</div>
			<div class="span1">
			</div>
			<div class="span4">
				<a class="btn btn-large btn-primary" href="'.$url.'">wieder öffnen</a>
			</div>
		</div>
	</div>
</div>';
}



/*  --  PANEL: PREVIEW: HTML  --  */
$urlPreview		= './work/newsletter/preview/html/'.$newsletterId;
$iframeHtml		= HtmlTag::create( 'iframe', '', [
	'src'			=> $urlPreview,
	'frameborder'	=> '0',
] );
$buttonPreviewHtml	= HtmlTag::create( 'button', $iconPreview.'Vorschau', [
	'type'			=> 'button',
	'class'			=> 'btn btn-info btn-mini',
	'data-toggle'	=> 'modal',
	'data-target'	=> '#modal-preview',
	'onclick'		=> 'ModuleWorkNewsletter.showPreview("'.$urlPreview.'");',
] );
$panelPreviewHtml	= '
<div id="newsletter-preview" class="half-size">
	<div id="newsletter-preview-container">
 		<div id="newsletter-preview-iframe-container">
			'.$iframeHtml.'
		</div>
	</div>
</div>';

/*  --  PANEL: PREVIEW: TEXT  --  */
$urlPreview		= './work/newsletter/preview/text/'.$newsletterId;
$iframeText		= HtmlTag::create( 'iframe', '', [
	'src'			=> $urlPreview,
	'frameborder'	=> '0',
] );
$buttonPreviewText	= HtmlTag::create( 'button', $iconPreview.'Vorschau', [
	'type'			=> 'button',
	'class'			=> 'btn btn-info btn-mini',
	'data-toggle'	=> 'modal',
	'data-target'	=> '#modal-preview',
	'onclick'		=> 'ModuleWorkNewsletter.showPreview("'.$urlPreview.'");',
] );
$panelPreviewText	= '
<div id="newsletter-preview" class="half-size">
	<div id="newsletter-preview-container">
 		<div id="newsletter-preview-iframe-container">
			'.$iframeText.'
		</div>
	</div>
</div>';

$panelPreview		= '
<div class="content-panel">
	<h4>
		<span>HTML-Vorschau</span>
		<div style="float: right">
			'.$buttonPreviewHtml.'
		</div>
	</h4>
	<div class="content-panel-inner">
		'.$panelPreviewHtml.'
	</div>
</div>
<div class="content-panel">
	<h4>
		<span>Text-Vorschau</span>
		<div style="float: right">
			'.$buttonPreviewText.'
		</div>
	</h4>
	<div class="content-panel-inner">
		'.$panelPreviewText.'
	</div>
</div>';

$panelRemove	= '
<div class="content-panel">
	<h3>Kampagne entfernen</h3>
	<div class="content-panel-inner">
		<div class="alert alert-info">
			Diese Kampagne wurde bereits versendet und kann daher nicht mehr entfernt werden.
		</div>
		<p>
			Da die Empfänger der Kampagne einen Link zum Anzeigen des Newsletters im Browser erhalten haben,
			kann die Kampagne momemtan nicht entfernt werden,
		</p>
		<div class="alert alert-info">
			Sobald eine Kampagne mit dieser Vorlage versendet wird, kann die Vorlage nicht mehr entfernt werden.
		</div>
		<div class="buttonbar">
			'.$buttonRemove.'
		</div>
	</div>
</div>';

//print_m( $newsletters );die;

if( $newsletter->status < Model_Newsletter::STATUS_SENT )
	$panelRemove	= '
<div class="content-panel">
	<h3>Vorlage entfernen</h3>
	<div class="content-panel-inner">
		<p>
			Diese Vorlage wurde noch nicht verwendet und kann daher entfernt werden, wenn sie nicht mehr benötigt wird.
		</p>
		<div class="alert alert-info">
			Sobald eine Kampagne mit dieser Vorlage versendet wird, kann die Vorlage nicht mehr entfernt werden.
		</div>
		<div class="buttonbar">
			'.$buttonRemove.'
		</div>
	</div>
</div>';

return '
<div class="row-fluid">
	<div class="span7">
		'.$panelDetails.'
		'.$extras.'
		'.$panelRemove.'
	</div>
	<div class="span5">
		'.$panelPreview.'
	</div>
</div>';
