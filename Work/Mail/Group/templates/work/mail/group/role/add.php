<?php

$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
$iconSave		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );

$tabs	= $view->renderTabs( $env, 2 );

$panelAdd	= '
<div class="content-panel">
	<h3>Neue Rolle</h3>
	<div class="content-panel-inner">
		<form action="./work/mail/group/role/add" method="post">
			<div class="row-fluid">
				<div class="span3">
					<label for="input_title" class="mandatory">Titel</label>
					<input type="text" name="title" id="input_title" class="span12" required="required"/>
				</div>
				<div class="span3">
					<label for="input_host" class="mandatory">Host-Adresse</label>
					<input type="text" name="host" id="input_host" class="span12" required="required"/>
				</div>
				<div class="span2">
					<label for="input_port">Port</label>
					<input type="text" name="port" id="input_port" class="span12" required="required"/>
				</div>
			</div>
			<div class="buttonbar">
				<a href="./work/mail/group/role" class="btn">'.$iconCancel.'&nbsp;zurück</a>
				<button type="submit" name="save" class="btn btn-primary">'.$iconSave.'&nbsp;speichern</button>
			</div>
		</form>
	</div>
</div>';

$tabs	= $view->renderTabs( $env, 3 );

return $tabs.$panelAdd;
