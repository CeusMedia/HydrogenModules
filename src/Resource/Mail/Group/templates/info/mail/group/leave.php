<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-left'] );
$iconList		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-list'] );
$iconSave		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );


if( !empty( $group ) ){
	$labelMessages	= $group->messages == 1 ? $group->messages.' Nachricht' : $group->messages.' Nachrichten';
	$labelMembers	= $group->members == 1 ? $group->members.' Mitglied' : $group->members.' Mitglieder';
	$description	= $group->description ? $group->description : '<em class="muted">Keine Beschreibung derzeit.</em>';
	$participation	= HtmlTag::create( 'abbr', $words['types'][$group->type], ['title' => $words['types-description'][$group->type]] );
	$address		= HtmlTag::create( 'kbd', $group->address );
	$facts			= join( '&nbsp;&nbsp;|&nbsp;&nbsp;', [
		$labelMessages,
		$labelMembers,
		'Teilnahme: '.$participation,
		'Adresse: '.$address,
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
					<label for="input_address">E-Mail-Adresse der Gruppe</label>
					<input type="text" name="address" id="input_address" class="span12" value="'.htmlentities( @$data->address, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
	';
}
$panelForm	= '
<div class="content-panel">
	<h3>Abmelden</h3>
	<div class="content-panel-inner">
		<form action="./info/mail/group/leave'.( $groupId ? '/'.$groupId : '' ).'" method="post">
			'.$inputGroup.'
			<div class="row-fluid">
				<div class="span10 offset1">
					<div class="row-fluid">
						<div class="span12">
							<label for="input_email">Ihre E-Mail-Adresse</label>
							<input type="text" name="email" id="input_email" class="span12" value="'.htmlentities( @$data->email, ENT_QUOTES, 'UTF-8' ).'"/>
						</div>
					</div>
					<hr/>
					<p>
						Die Mitglieder der Gruppe werden über Ihren Austritt informiert.<br/>
						<em>Wollen Sie sich noch von den Mitgliedern verabschieden?</em>
					</p>
					<div class="row-fluid">
						<div class="span12">
							<label for="input_message">Letzte Nachricht von Ihrer Seite <small class="muted">(optional)</small></label>
							<textarea name="message" id="input_message" rows="5" class="span12">'.@$data->message.'</textarea>
						</div>
					</div>
					<input type="text" name="vt" value="" style="display: none"/>
				</div>
			</div>
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
