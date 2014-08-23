<?php
$optType	= UI_HTML_Elements::Options( $words['types'], $application->type );
$optStatus	= UI_HTML_Elements::Options( $words['states'], $application->status );

$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-arrow-left' ) );
$iconSave		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-ok icon-white' ) );
$iconEnable		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-play not-icon-white' ) );
$iconDisable	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-pause not-icon-white' ) );

$buttonEnable	= UI_HTML_Tag::create( 'a', $iconEnable.' aktivieren', array( 'href' => "#", 'disabled' => 'disabled', 'class' => "btn btn-default btn-small disabled" ) );
$buttonDisable	= UI_HTML_Tag::create( 'a', $iconDisable.' deaktivieren', array( 'href' => "#", 'disabled' => 'disabled', 'class' => "btn not-btn-inverse btn-small disabled" ) );

$isEditor		= TRUE;

if( $isEditor && (int) $application->status === 0 )
	$buttonEnable	= UI_HTML_Tag::create( 'a', $iconEnable.' aktivieren', array(
		'href'		=> "./oauth/application/enable/".$applicationId,
		'class'		=> "btn btn-default btn-small"
	) );

if( $isEditor && (int) $application->status === 1 )
	$buttonDisable	= UI_HTML_Tag::create( 'a', $iconDisable.' deaktivieren', array(
		'href'		=> "./oauth/application/disable/".$applicationId,
		'class'		=> "btn not-btn-inverse btn-small"
	) );

return '
<h2 class="muted">OAuth-Server</h2>
<div class="content-panel">
	<div class="content-panel-inner">
		<h3>Applikation verändern</h3>
		<form action="./oauth/application/edit/'.$application->oauthApplicationId.'" method="post">
			<div class="row-fluid">
				<div class="span6">
					<div class="row-fluid">
						<div class="span12">
							<label for="input_title" class="mandatory required">Titel</label>
							<input type="text" name="title" id="input_title" class="span12" required="required" value="'.htmlentities( $application->title, ENT_QUOTES, 'UTF-8' ).'"/>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<label for="input_url" class="mandatory required">Basis-URL</label>
							<input type="text" name="url" id="input_title" class="span12" required="required" value="'.htmlentities( $application->url, ENT_QUOTES, 'UTF-8' ).'"/>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span6">
							<label for="input_type" class="mandatory required">Vertraulichkeit</label>
							<select name="type" id="input_type" class="span12">'.$optType.'</select>
						</div>
						<div class="span6">
							<label for="input_type" class="mandatory required">Zustand</label>
							<select name="status" id="input_status" class="span12" disabled="disabled" readonly="readonly">'.$optStatus.'</select>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span4">
							<label for="input_clientId" class="mandatory required">Client-ID</label>
							<input type="text" name="clientId" id="input_clientId" class="span12" readonly="readonly" disabled="disabled" value="'.htmlentities( $application->clientId, ENT_QUOTES, 'UTF-8' ).'"/>
						</div>
						<div class="span8">
							<label for="input_clientSecret" class="mandatory required">Client-Secret</label>
							<input type="text" name="clientSecret" id="input_clientSecret" class="span12" required="required" value="'.htmlentities( $application->clientSecret, ENT_QUOTES, 'UTF-8' ).'"/>
						</div>
					</div>
				</div>
				<div class="span6">
					<label for="input_description">Beschreibung</label>
					<textarea type="text" name="description" id="input_description" class="span12" rows="11">'.htmlentities( $application->description, ENT_QUOTES, 'UTF-8' ).'</textarea>
				</div>
			</div>
			<div class="buttonbar">
				<a href="./oauth/application" class="btn not-btn-small"><i class="icon-arrow-left"></i> zurück</a>
				<a href="./oauth/application/view/'.$application->oauthApplicationId.'" class="btn not-btn-small btn-info"><i class="icon-eye-open icon-white"></i> anzeigen</a>
				&nbsp;|&nbsp;
				<button type="submit" name="save" class="btn btn-success not-btn-small"><i class="icon-ok icon-white"></i> speichern</button>
				<a href="./oauth/application/remove/'.$application->oauthApplicationId.'" class="btn btn-danger not-btn-small"><i class="icon-remove icon-white"></i> entfernen</a>
				&nbsp;|&nbsp;
				<div class="btn-group">
					'.$buttonEnable.'
					'.$buttonDisable.'
				</div>
			</div>
		</form>
	</div>
</div>
';