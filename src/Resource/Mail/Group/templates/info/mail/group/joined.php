<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var array<array<string,string>> $words */

$wf				= (object) $words['facts'];
$w				= (object) $words['joined'];

$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-left'] );
$iconList		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-list'] );
$iconSave		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );

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

return '<div class="row-fluid">
	<div class="span7 offset2">
		<br/>
		<br/>
		<div class="content-panel">
			<h3>'.$w->heading.'</h3>
			<div class="content-panel-inner">
				<div class="row-fluid">
					<div class="span10 offset1">
						<br/>
						<div class="group-title" style="font-size: 1.3em; line-height: 1.6em">'.$group->title.'</div>
						<p>'.$description.'</p>
						<small class="not-muted">'.$facts.'</small>
						<hr/>
						<div class="alert alert-success">'.sprintf( $w->textSuccess, $group->title ).'</div>
						<h4>'.$w->headingConfirm.'</h4>
						<p>
							'.sprintf( $w->textMailSent, $member->address ).'
						</p>
						<p><strong>'.$w->textClickLink.'</strong></p>
					</div>
				</div>
				<br/>
			</div>
		</div>
	</div>
</div>';
