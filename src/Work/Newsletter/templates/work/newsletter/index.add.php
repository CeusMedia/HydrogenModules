<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

use View_Helper_Bootstrap_Modal as BootstrapModalDialog;

/** @var Environment $env */
/** @var View $view */
/** @var object $words */
/** @var object $newsletter */
/** @var array<object> $addTemplates */
/** @var array<object> $addNewsletters */
/** @var bool $useUserGroupRelations */

$w	= (object) $words->add;

$iconCancel	= HtmlTag::create( 'i', '', ['class' => "fa fa-fw fa-arrow-left"] ).'&nbsp;';
$iconSave	= HtmlTag::create( 'i', '', ['class' => "fa fa-fw fa-check"] ).'&nbsp;';

$optTemplate	= [];
foreach( $addTemplates as $entry )
	$optTemplate[$entry->newsletterTemplateId]	= $entry->title;
$optTemplate	= HtmlElements::Options( $optTemplate, 0 );

$optNewsletter	= ['0' => '- keine Kopie -'];
krsort( $addNewsletters );
foreach( $addNewsletters as $item )
	$optNewsletter[$item->newsletterId]	= $item->title;
$optNewsletter	= HtmlElements::Options( $optNewsletter, 0 );

$groupRelations	= '';
if( $useUserGroupRelations ){
	$list	= [];
	foreach( Logic_Authentication::getInstance( $env )->getCurrentGroups() as $currentGroup ){
		$checkbox	= HtmlTag::create( 'input', NULL, [
			'type'		=> 'checkbox',
			'checked'	=> 'checked',
			'name'		=> 'relationGroupIds[]',
			'value'		=> $currentGroup->groupId,
		] );
		$list[] = HtmlTag::create( 'label', $checkbox.' '.$currentGroup->title, ['class' => 'checkbox'] );
	}

	$groupRelations	= '
		<div class="row-fluid">
			<div class="span12">
				<label for="input_useGroupRelations">geerbte Gruppenzuweisung</label>
				<input type="hidden" name="useGroupRelations" id="input_useGroupRelations" value="1"/>
				<div class="checkbox-list" id="newsletter-group-relations" >
					'.join( $list ).'
				</div>
			</div>
		</div>
';
}


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
<!--		<div class="row-fluid optional newsletterId newsletterId-0">
			<div class="span6">
				<label for="input_senderAddress">'.$w->labelSenderAddress.'</label>
				<input type="text" name="senderAddress" id="input_senderAddress" class="span12" required="required"/>
			</div>
			<div class="span6">
				<label for="input_senderName">'.$w->labelSenderName.'</label>
				<input type="text" name="senderName" id="input_senderName" class="span12"/>
			</div>
		</div>-->
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
		'.$groupRelations.'
	</div>
</div>
<script>
jQuery(document).ready(function(){
	jQuery("#modal-add #input_title").on("change keyup", function(){
		let subject = jQuery("#modal-add #input_subject");
		let title = jQuery("#modal-add #input_title");
		subject.val(title.val());
	});
	jQuery("#modal-add #newsletter-group-relations input").on("change", function(){
		let container = jQuery("#modal-add #newsletter-group-relations");
		if (container.find("input:checked").length === 0) {
			$(this).prop("checked", true);
		}
	});
});
</script>
';

$modalAdd	= new BootstrapModalDialog( $env );
$modalAdd->setId( 'modal-add' );
$modalAdd->setFormAction( './work/newsletter/add' );
$modalAdd->setBody( $formAdd );
$modalAdd->setHeading( $w->heading );
$modalAdd->setButtonLabelCancel( $iconCancel.$w->buttonCancel );
$modalAdd->setButtonLabelSubmit( $iconSave.$w->buttonSave );

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
