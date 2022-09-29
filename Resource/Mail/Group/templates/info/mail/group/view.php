<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-left'] );
$iconList		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-list'] );
$iconSave		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );
$iconJoin		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-sign-in'] );
$iconRegister	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-bell-o'] );
$iconUnregister	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-sign-out'] );

$labelMessages	= $group->messages == 1 ? $group->messages.' Nachricht' : $group->messages.' Nachrichten';
$labelMembers	= $group->members == 1 ? $group->members.' Mitglied' : $group->members.' Mitglieder';
$description	= $group->description ? $group->description : '<em class="muted">Keine Beschreibung derzeit.</em>';
$participation	= HtmlTag::create( 'abbr', $words['types'][$group->type], ['title' => $words['types-description'][$group->type]] );
$address		= HtmlTag::create( 'kbd', $group->address );
$facts			= join( '&nbsp;&nbsp;|&nbsp;&nbsp;', array(
	$labelMessages,
	$labelMembers,
	'Teilnahme: '.$participation,
	'Adresse: '.$address,
) );

return '<div class="row-fluid">
	<div class="span7 offset2">
		<br/>
		<br/>
		<div class="content-panel">
			<h3><span class="muted">Gruppe:</span> '.$group->title.'</h3>
			<div class="content-panel-inner">
				<div class="row-fluid">
					<div class="span10 offset1">
						<br/>
						<div class="group-title" style="font-size: 1.3em; line-height: 1.6em">'.$group->title.'</div>
						<p>'.$description.'</p>
						<small class="not-muted">'.$facts.'</small>
					</div>
				</div>
				<br/>
				<div class="buttonbar">
					<a href="./info/mail/group" class="btn not-btn-small">'.$iconList.'&nbsp;zur Liste</a>
					<a href="./info/mail/group/join/'.$group->mailGroupId.'" class="btn btn-primary not-btn-large">'.$iconJoin.'&nbsp;beitreten</a>
					<a href="./info/mail/group/leave/'.$group->mailGroupId.'" class="btn btn-inverse not-btn-large">'.$iconUnregister.'&nbsp;verlassen</a>
				</div>
			</div>
		</div>
	</div>
</div>';
