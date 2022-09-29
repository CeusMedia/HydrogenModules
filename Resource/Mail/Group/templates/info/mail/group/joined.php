<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-left'] );
$iconList		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-list'] );
$iconSave		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );

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
			<h3>Beitritt beantragt</h3>
			<div class="content-panel-inner">
				<div class="row-fluid">
					<div class="span10 offset1">
						<br/>
						<div class="group-title" style="font-size: 1.3em; line-height: 1.6em">'.$group->title.'</div>
						<p>'.$description.'</p>
						<small class="not-muted">'.$facts.'</small>
						<hr/>
						<div class="alert alert-success">Der Betritt zur Gruppe "'.$group->title.'" wurde beantragt.</div>
						<h4>Bestätigung notwendig</h4>
						<p>
							Wir haben eine E-Mail an die Adresse <strong>'.$member->address.'</strong> geschickt.<br/>
							Darin enthalten ist ein Link zur Bestätigung der verwendeten E-Mail-Adresse.
						</p>
						<p><strong>Bitte klicken Sie diesen Link an oder öffnen die Link-Adresse im Browser.</strong></p>
					</div>
				</div>
				<br/>
			</div>
		</div>
	</div>
</div>';
