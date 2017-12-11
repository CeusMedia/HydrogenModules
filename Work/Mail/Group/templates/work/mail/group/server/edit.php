<?php

$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
$iconSave		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );

$panelEdit	= '
<div class="content-panel">
	<h3>E-Mail-Server bearbeiten</h3>
	<div class="content-panel-inner">
		<form action="./work/mail/group/server/edit/'.$server->mailGroupServerId.'" method="post">
			<div class="row-fluid">
				<div class="span3">
					<label for="input_title" class="mandatory">Titel</label>
					<input type="text" name="title" id="input_title" class="span12" required="required" value="'.htmlentities( $server->title, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span3">
					<label for="input_host" class="mandatory">Host-Adresse</label>
					<input type="text" name="host" id="input_host" class="span12" required="required" value="'.htmlentities( $server->host, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span2">
					<label for="input_port">Port</label>
					<input type="text" name="port" id="input_port" class="span12" required="required" value="'.htmlentities( $server->port, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<div class="buttonbar">
				<a href="./work/mail/group/server" class="btn">'.$iconCancel.'&nbsp;zurück</a>
				<button type="submit" name="save" class="btn btn-primary">'.$iconSave.'&nbsp;speichern</button>
			</div>
		</form>
	</div>
</div>';

$tabs	= $view->renderTabs( $env, 2 );

return $tabs.$panelEdit;
