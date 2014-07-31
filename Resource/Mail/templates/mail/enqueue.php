<?php

$optClass	= UI_HTML_Elements::Options( array_combine( $classes, $classes ), $class );
//$optClass['_selected']	= $class;

return '
<h2>Mail::enqueue</h2>
<div class="layout-panel">
	<div class="layout-panel-inner">
		<form action="./mail/enqueue" method="post">
			<div class="row">
				<div class="span12">
					<label for="input_class">Mail-Klasse</label>
					<select id="input_class" name="class">'.$optClass.'</select>
				</div>
			</div>
			<div class="row">
				<div class="span6">
					<label for="input_sender">Absender</label>
					<input type="text" id="input_sender" name="sender" class="span12" value="'.htmlentities( $sender, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span6">
					<label for="input_receiver">Empfänger</label>
					<input type="text" id="input_receiver" name="receiver" class="span12" value="'.htmlentities( $receiver, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<div class="row">
				<div class="span12">
					<label for="input_subject">Betreff</label>
					<input type="text" id="input_subject" name="subject" class="span12" value="'.htmlentities( $subject, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<div class="row">
				<div class="span12">
					<label for="input_body">Nachricht</label>
					<textarea id="input_body" name="body" class="span12" rows="20">'.htmlentities( $body, ENT_QUOTES, 'UTF-8' ).'</textarea>
				</div>
			</div>
			<div class="buttonbar">
				<button type="submit" name="add" class="btn btn-success"><i class="icon-plus icon-white"></i> einreihen</button>
			</div>
		</form>
	</div>
</div>
';
?>
