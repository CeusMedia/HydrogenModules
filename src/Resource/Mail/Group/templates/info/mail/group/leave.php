<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var array<array<string,string>> $words */

$wf				= (object) $words['facts'];
$w				= (object) $words['leave'];

$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-left'] );
$iconList		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-list'] );
$iconSave		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );


if( !empty( $group ) ){
	$labelMessages	= sprintf( $group->messages == 1 ? $wf->messagesOne : $wf->messagesMany, $group->messages );
	$labelMembers	= sprintf( $group->members == 1 ? $wf->membersOne : $wf->membersMany, $group->members );
	$description	= $group->description ?: '<em class="muted">'.$wf->noDescription.'</em>';
	$participation	= HtmlTag::create( 'abbr', $words['types'][$group->type], ['title' => $words['types-description'][$group->type]] );
	$address		= HtmlTag::create( 'kbd', $group->address );
	$facts			= join( '&nbsp;&nbsp;|&nbsp;&nbsp;', [
		$labelMessages,
		$labelMembers,
		$wf->labelParticipation.': '.$participation,
		$wf->labelAddress.': '.$address,
	] );
	$inputGroup	= '
		<div class="row-fluid">
			<div class="span10 offset1">
				<br/>
				<div class="group-title" style="font-size: 1.3em; line-height: 1.6em">'.$group->title.'</div>
				<p>'.$description.'</p>
				<small class="not-muted">'.$facts.'</small>
			</div>
		</div>
		<hr/>';
}
else{
	$inputGroup	= '
			<div class="row-fluid">
				<div class="span12">
					<label for="input_address">'.$w->labelGroupAddress.'</label>
					<input type="text" name="address" id="input_address" class="span12" value="'.htmlentities( @$data->address, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
	';
}
$panelForm	= '
<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<form action="./info/mail/group/leave'.( $groupId ? '/'.$groupId : '' ).'" method="post">
			'.$inputGroup.'
			<div class="row-fluid">
				<div class="span10 offset1">
					<div class="row-fluid">
						<div class="span12">
							<label for="input_email">'.$w->labelEmail.'</label>
							<input type="text" name="email" id="input_email" class="span12" value="'.htmlentities( @$data->email, ENT_QUOTES, 'UTF-8' ).'"/>
						</div>
					</div>
					<hr/>
					<p>
						'.$w->textMembersInformed.'<br/>
						<em>'.$w->textFarewellQuestion.'</em>
					</p>
					<div class="row-fluid">
						<div class="span12">
							<label for="input_message">'.$w->labelMessage.' <small class="muted">'.$w->labelOptional.'</small></label>
							<textarea name="message" id="input_message" rows="5" class="span12">'.@$data->message.'</textarea>
						</div>
					</div>
					<input type="text" name="vt" value="" style="display: none"/>
				</div>
			</div>
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
</div>';
