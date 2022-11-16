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
/** @var array $newsletters */
/** @var object $newsletter */

$iconCancel	= HtmlTag::create( 'i', '', ['class' => "fa fa-fw fa-arrow-left"] ).'&nbsp;';
$iconSave	= HtmlTag::create( 'i', '', ['class' => "fa fa-fw fa-check"] ).'&nbsp;';

$optTemplate	= [];
foreach( $templates as $entry )
	$optTemplate[$entry->newsletterTemplateId]	= $entry->title;
$optTemplate	= HtmlElements::Options( $optTemplate, $newsletter->newsletterTemplateId );

$optNewsletter	= ['0' => '- keine Kopie -'];
krsort( $newsletters );
foreach( $newsletters as $item )
	$optNewsletter[$item->newsletterId]	= $item->title;
$optNewsletter	= HtmlElements::Options( $optNewsletter, $newsletter->newsletterId );

$formAdd	= '
<form action="./work/newsletter/add" method="post">
	<div class="row-fluid">
		<div class="span12">
			<div class="row-fluid">
				<div class="span4">
					<label for="input_title">'.$words->add->labelTitle.'</label>
					<input type="text" name="title" id="input_title" class="span12" required="required" value="'.htmlentities( $newsletter->title, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span3">
					<label for="input_newsletterId">'.$words->add->labelNewsletterId.'</label>
					<select name="newsletterId" id="input_newsletterId" class="span12 has-optionals">'.$optNewsletter.'</select>
				</div>
				<div class="span3 optional newsletterId newsletterId-0">
					<label for="input_newsletterTemplateId">'.$words->add->labelTemplateId.'</label>
					<select name="newsletterTemplateId" id="input_newsletterTemplateId" class="span12" required="required">'.$optTemplate.'</select>
				</div>
				<div class="span2">
					<label for="input_trackingCode"><abbr title="'.$words->add->labelTrackingCode_title.'">'.$words->add->labelTrackingCode.'</abbr></label>
					<input type="text" name="trackingCode" id="input_trackingCode" class="span12" value="'.htmlentities( $newsletter->trackingCode, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span4">
					<label for="input_subject">'.$words->add->labelSubject.'</label>
					<input type="text" name="subject" id="input_subject" class="span12" value="'.htmlentities( $newsletter->subject, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span4 optional newsletterId newsletterId-0">
					<label for="input_senderAddress">'.$words->add->labelSenderAddress.'</label>
					<input type="text" name="senderAddress" id="input_senderAddress" class="span12" required="required" value="'.htmlentities( $newsletter->senderAddress, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span4 optional newsletterId newsletterId-0">
					<label for="input_senderName">'.$words->add->labelSenderName.'</label>
					<input type="text" name="senderName" id="input_senderName" class="span12" value="'.htmlentities( $newsletter->senderName, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<div class="buttonbar">
				<a href="./work/newsletter" class="btn btn-small">'.$iconCancel.$words->add->buttonCancel.'</span></a>
				<button type="submit" class="btn btn-primary" name="save">'.$iconSave.$words->add->buttonSave.'</button>
			</div>
		</div>
	</div>
</form>
';

$tabsMain	= $tabbedLinks ? $this->renderMainTabs() : '';

extract( $view->populateTexts( ['above', 'bottom', 'top'], 'html/work/newsletter/add/' ) );

return $textTop.'
<div class="newsletter-content">
	'.$tabsMain.'
	'.$textAbove.'
	<div class="row-fluid">
		<div class="span12">
			<div class="content-panel">
				<h3><span class="muted">'.$words->add->heading.'</span> '.$newsletter->title.'</h3>
				<div class="content-panel-inner">
					'.$formAdd.'
				</div>
			</div>
		</div>
	</div>
</div>'.$textBottom;
