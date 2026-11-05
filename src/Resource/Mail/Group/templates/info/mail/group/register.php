<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var array<array<string,string>> $words */

$wf				= (object) $words['facts'];
$w				= (object) $words['join'];

$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-left'] );
$iconList		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-list'] );
$iconSave		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );

$nrMembers		= count( $group->members );
$labelMembers	= sprintf( $nrMembers == 1 ? $wf->membersOne : $wf->membersMany, $nrMembers );
$description	= $group->description ?: '<em class="muted">'.$wf->noDescription.'</em>';
$participation	= HtmlTag::create( 'abbr', $words['types'][$group->type], ['title' => $words['types-description'][$group->type]] );
$address		= HtmlTag::create( 'kbd', $group->address );
$facts			= join( '&nbsp;&nbsp;|&nbsp;&nbsp;', [
	$labelMembers,
	$wf->labelParticipation.': '.$participation,
	$wf->labelAddress.': '.$address,
] );

$termsOfUse		= $view->loadContentFile( 'html/info/mail/group/termsOfUse.html' );
$privacyNotice	= $view->loadContentFile( 'html/info/mail/group/privacyNotice.html' );
$panelLegal		= '
	<div class="row-fluid">
		<div class="span12">
			<div class="framed-content">
				'.$termsOfUse.'
			</div>
			<br/>
		</div>
	</div>
	<div class="row-fluid">
		<div class="span12">
			<div class="framed-content">
				'.$privacyNotice.'
			</div>
			<br/>
		</div>
	</div>
	<div class="row-fluid">
		<div class="span12">
			<label class="checkbox">
				<input type="checkbox" name="accept" id="input_accept"/>
				'.$w->labelAccept.'
			</label>
		</div>
	</div>
';


$panelForm	= '
<div class="content-panel">
	<h3>'.$w->headingRegister.'</h3>
	<div class="content-panel-inner">
		<br/>
		<div class="group-title" style="font-size: 1.3em; line-height: 1.6em">'.$group->title.'</div>
		<p>'.$description.'</p>
		<small class="not-muted">'.$facts.'</small>
		<hr/>
		<form action="./info/mail/group/join" method="post">
			<input type="hidden" name="ht" value=""/>
			<div class="row-fluid">
				<div class="span5">
					<label for="input_address_name"><abbr title="'.htmlentities( $w->labelName_title, ENT_QUOTES, 'UTF-8' ).'">'.$w->labelName.'</abbr>  <small class="muted">'.$w->labelOptional.'</small></label>
					<input type="text" name="address_name" id="input_address_name" class="span12" value="'.htmlentities( @$data->name, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span7">
					<label for="input_address_email"><abbr title="'.htmlentities( $w->labelEmail_title, ENT_QUOTES, 'UTF-8' ).'">'.$w->labelEmail.'</label>
					<input type="email" name="address_email" id="input_address_email" class="span12" required="required" value="'.htmlentities( @$data->email, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<hr/>
			<p>
				'.$w->textMembersInformed.'<br/>
				<em>'.$w->textGreetingQuestion.'</em>
			</p>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_message">'.$w->labelGreeting.' <small class="muted">'.$w->labelOptional.'</small></label>
					<textarea name="message" id="input_message" rows="5" class="span12"></textarea>
				</div>
			</div>
			<input type="text" name="vt" value="" style="display: none"/>
			<hr/>
			'.$panelLegal.'
			<br/>
			<div class="buttonbar">
				<a href="./info/mail/group" class="btn not-btn-small">'.$iconList.'&nbsp;'.$w->buttonList.'</a>
				<button type="submit" name="save" class="btn btn-primary not-btn-large">'.$iconSave.'&nbsp;'.$w->buttonSave.'</button>
			</div>
		</form>
	</div>
</div>';


return '<div class="row-fluid">
	<div class="span7 offset2">
		<br/>
		<br/>
		'.$panelForm.'
	</div>
</div>
<style>
.framed-content {
	height: 250px;
	overflow-y: auto;
	padding: 1em 2em;
	border: 1px solid rgba(127, 127, 127, 0.5);
	zoom: 0.8;
	-moz-transform: scale(0.8);
	background-color: rgba(255, 255, 255, 0.75);
	color: #666;
	}
</style>
<script>
jQuery(document).ready(function(){
	jQuery("#input_accept").on("change", function(){
		var button = jQuery("button");
		var isAccepted = jQuery(this).is(":checked");
		button.prop("disabled", isAccepted ? null : "disabled");
	}).trigger("change");
});
</script>';
