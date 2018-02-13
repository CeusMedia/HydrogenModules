<?php

$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
$iconList		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-list' ) );
$iconSave		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );

$nrMembers		= count( $group->members );
$group->description	= @$group->description ? $group->description : '<em class="muted">Keine Beschreibung derzeit.</em>';

$labelMembers	= $nrMembers == 1 ? $nrMembers.' Mitglied' : $nrMembers.' Mitglieder';
$panelForm	= '
<div class="content-panel">
	<h3>Der Gruppe beitreten</h3>
	<div class="content-panel-inner">
		<br/>
		<div class="group-title" style="font-size: 1.3em; line-height: 1.6em">'.$group->title.'</div>
		<p>'.@$group->description.'</p>
		<small class="not-muted">'.$labelMembers.' | Teilnahme: <abbr title="'.$words['types-description'][$group->mailGroupId].'">'.$words['types'][$group->mailGroupId].'</abbr> | Adresse: '.$group->address.'</small>
		<hr/>
		<form action="./info/mail/group/join" method="post">
			<input type="hidden" name="ht" value=""/>
			<div class="row-fluid">
				<div class="span5">
					<label for="input_address_name"><abbr title="wird, falls vorhanden, anstelle der E-Mail-Adresse angezeigt">Ihr Name</abbr>  <small class="muted">(optional)</small></label>
					<input type="text" name="address_name" id="input_address_name" class="span12" value="'.htmlentities( @$data->name, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span7">
					<label for="input_address_email"><abbr title="Sie erhalten zuerst eine E-Mail zur Bestätigung Ihrer Mitgliedschaft.">Ihre E-Mail-Adresse</label>
					<input type="email" name="address_email" id="input_address_email" class="span12" required="required" value="'.htmlentities( @$data->email, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<hr/>
			<p>
				Die Mitglieder der Gruppe werden über Ihren Beitritt informiert.<br/>
				<em>Wollen Sie die Mitglieder begrüßen und sich kurz vorstellen?</em>
			</p>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_message">Kurze Begrüßung von Ihrer Seite <small class="muted">(optional)</small></label>
					<textarea name="message" id="input_message" rows="5" class="span12"></textarea>
				</div>
			</div>
			<input type="text" name="vt" value="" style="display: none"/>
			<div class="buttonbar">
				<a href="./info/mail/group" class="btn not-btn-small">'.$iconList.'&nbsp;zur Liste</a>
				<button type="submit" name="save" class="btn btn-primary not-btn-large">'.$iconSave.'&nbsp;abmelden</button>
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


return 'Info_Mail_Group::unregister';
