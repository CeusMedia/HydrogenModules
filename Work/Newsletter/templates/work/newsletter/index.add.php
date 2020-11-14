<?php

$w	= (object) $words->add;

$iconCancel	= UI_HTML_Tag::create( 'i', '', array( 'class' => "fa fa-fw fa-arrow-left" ) ).'&nbsp;';
$iconSave	= UI_HTML_Tag::create( 'i', '', array( 'class' => "fa fa-fw fa-check" ) ).'&nbsp;';

$optTemplate	= array();
foreach( $addTemplates as $entry )
	$optTemplate[$entry->newsletterTemplateId]	= $entry->title;
$optTemplate	= UI_HTML_Elements::Options( $optTemplate, 0 );

$optNewsletter	= array( '0' => '- keine Kopie -' );
krsort( $addNewsletters );
foreach( $addNewsletters as $item )
	$optNewsletter[$item->newsletterId]	= $item->title;
$optNewsletter	= UI_HTML_Elements::Options( $optNewsletter, 0 );

$formAdd	= '
<div class="row-fluid">
	<div class="span12">
		<div class="row-fluid">
			<div class="span12">
				<label for="input_title">'.$w->labelTitle.'</label>
				<input type="text" name="title" id="input_title" class="span12" required="required"/>
			</div>
		</div>
		<div class="row-fluid">
			<div class="span7">
				<label for="input_newsletterId">'.$w->labelNewsletterId.'</label>
				<select name="newsletterId" id="input_newsletterId" class="span12 has-optionals">'.$optNewsletter.'</select>
			</div>
			<div class="span5 optional newsletterId newsletterId-0">
				<label for="input_newsletterTemplateId">'.$w->labelTemplateId.'</label>
				<select name="newsletterTemplateId" id="input_newsletterTemplateId" class="span12" required="required">'.$optTemplate.'</select>
			</div>
		</div>
		<div class="row-fluid optional newsletterId newsletterId-0">
			<div class="span6">
				<label for="input_senderAddress">'.$w->labelSenderAddress.'</label>
				<input type="text" name="senderAddress" id="input_senderAddress" class="span12" required="required"/>
			</div>
			<div class="span6">
				<label for="input_senderName">'.$w->labelSenderName.'</label>
				<input type="text" name="senderName" id="input_senderName" class="span12"/>
			</div>
		</div>
<!--		<div class="row-fluid" style="display: none">
			<div class="span9">
				<label for="input_subject">'.$w->labelSubject.'</label>
				<input type="text" name="subject" id="input_subject" class="span12"/>
			</div>
			<div class="span3">
				<label for="input_trackingCode"><abbr title="'.$w->labelTrackingCode_title.'">'.$w->labelTrackingCode.'</abbr></label>
				<input type="text" name="trackingCode" id="input_trackingCode" class="span12"/>
			</div>
		</div>-->
	</div>
</div>
<script>
jQuery(document).ready(function(){
	jQuery("#modal-add #input_title").on("change keyup", function(){
		var subject = jQuery("#modal-add #input_subject");
		var title = jQuery("#modal-add #input_title");
		subject.val(title.val());
	});
});
</script>
';

$modalAdd	= new \CeusMedia\Bootstrap\Modal( 'modal-add' );
$modalAdd->setFormAction( './work/newsletter/add' );
$modalAdd->setBody( $formAdd );
$modalAdd->setHeading( $w->heading );
$modalAdd->setCloseButtonLabel( $iconCancel.$w->buttonCancel );
$modalAdd->setCloseButtonClass( 'btn btn-small' );
$modalAdd->setSubmitButtonLabel( $iconSave.$w->buttonSave );
$modalAdd->setSubmitButtonClass( 'btn btn-primary' );

return $modalAdd->render();


return $textTop.'
<div class="newsletter-content">
	'.$tabsMain.'
	'.$textAbove.'
	<div class="row-fluid">
		<div class="span12">
			<div class="content-panel">
				<h3><span class="muted">'.$w->heading.'</span> '.$newsletter->title.'</h3>
				<div class="content-panel-inner">
					'.$formAdd.'
				</div>
			</div>
		</div>
	</div>
</div>
'.$textBottom;
?>
