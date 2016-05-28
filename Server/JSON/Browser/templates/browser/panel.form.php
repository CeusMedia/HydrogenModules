<?php

$optController	= UI_HTML_Elements::Options( $controllers, $controller );
$optAction		= UI_HTML_Elements::Options( $actions, $action );

return '
<div class="content-panel content-panel-form">
	<h3>Request</h3>
	<div class="content-panel-inner">
		<form id="form_browser" action="" method="post">
			<div class="row-fluid">
				<div class="span6">
					<label>Controller</label>
					<select name="controller" id="input_controller" class="span12">'.$optController.'</select>
				</div>
				<div class="span6">
					<label>Action</label>
					<select name="action" id="input_action" class="span12">'.$optAction.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label>Token</label>
					<input type="text" name="token" id="input_token" class="span12" value="'.$token.'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label>Arguments: '.$arguments.'</label>
					<input type="text" name="path" id="input_path" class="span12" value="'.$path.'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label>POST Data</label>
					<textarea name="post" id="input_post" class="span12" rows="5">'.$post.'</textarea><br/>
				</div>
			</div>
			<div class="buttonbar">
				<button type="submit" class="btn">absenden</button>
			</div>
		</form>
	</div>
</div>';
