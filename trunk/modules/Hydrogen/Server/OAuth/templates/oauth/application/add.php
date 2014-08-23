<?php
$optType	= UI_HTML_Elements::Options( $words['types'], $application->get( 'type' ) );

return '
<h2 class="muted">OAuth-Server</h2>
<div class="content-panel">
	<div class="content-panel-inner">
		<h3>Neue Applikation</h3>
		<form action="./oauth/application/add" method="post">
			<div class="row-fluid">
				<div class="span6">
					<div class="row-fluid">
						<div class="span12">
							<label for="input_title" class="mandatory required">Titel</label>
							<input type="text" name="title" id="input_title" class="span12" required="required" value="'.htmlentities( $application->get( 'title' ), ENT_QUOTES, 'UTF-8' ).'"/>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<label for="input_url" class="mandatory required">Basis-URL</label>
							<input type="text" name="url" id="input_title" class="span12" required="required" value="'.htmlentities( $application->get( 'url' ), ENT_QUOTES, 'UTF-8' ).'"/>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span6">
							<label for="input_type" class="mandatory required">Vertraulichkeit</label>
							<select name="type" id="input_type" class="span12">'.$optType.'</select>
						</div>
					</div>
				</div>
				<div class="span6">
					<label for="input_description">Beschreibung</label>
					<textarea type="text" name="description" id="input_description" class="span12" rows="7">'.htmlentities( $application->get( 'description' ), ENT_QUOTES, 'UTF-8' ).'</textarea>
				</div>
			</div>
			<div class="buttonbar">
				<a href="./oauth/application" class="btn not-btn-small"><i class="icon-arrow-left"></i> zurück</a>
				<button type="submit" name="save" class="btn btn-success not-btn-small"><i class="icon-ok icon-white"></i> hinzufügen</button>
			</div>
		</form>
	</div>
</div>
';