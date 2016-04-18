<?php

extract( $view->populateTexts( array( 'top', 'bottom' ), 'html/info/contact/developer/' ) );

return $textTop.'
<div class="content-panel">
	<h3>Report senden</h3>
	<div class="content-panel-inner">
		<form action="./info/contact/developer" method="POST">
			<div class="row-fluid">
				<div class="span6">
					<label for="input_subject">Betrifft</label>
					<input type="text" name="subject" id="input_subject" class="span12" value="'.htmlentities( $sender, ENT_QUOTES, 'UTF-8' ).'" required="required"/>
				</div>
				<div class="span6">
					<label for="input_sender">E-Mail-Adresse </label>
					<input type="text" name="sender" id="input_sender" class="span12" value="'.htmlentities( $subject, ENT_QUOTES, 'UTF-8' ).'" required="required"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_message">Nachricht</label>
					<textarea name="message" id="input_message" class="span12" rows="8" required="required">'.htmlentities( $message, ENT_QUOTES, 'UTF-8' ).'</textarea>
				</div>
			</div>
			<div class="buttonbar">
				<button type="submit" name="save" class="btn btn-primary"><i class="icon-check icon-white"></i>&nbsp;absenden</button>
			</div>
		</form>
	</div>
</div>
'.$textBottom;
