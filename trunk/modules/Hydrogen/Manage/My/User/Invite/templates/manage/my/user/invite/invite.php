<?php
$subject	= 'Einladung zu "%s"';
$subject	= sprintf( $subject, $config->get( 'app.name' ) );
if( $request->get( 'subject' ) )
	$subject	= $request->get( 'subject' );

return '
<form action="./manage/my/user/invite/?code='.$code.'" method="post">
	<fieldset>
		<legend>Einladung</legend>
		<ul class="input">
			<li>
				<label for="input_email" class="mandatory">E-Mail-Adresse</label><br/>
				<input type="text" name="email" id="input_email" class="max mandatory" value="'.htmlentities( $request->get( 'email' ), ENT_COMPAT, 'UTF-8' ).'"/>
			</li>
			<li>
				<label for="input_subject" class="mandatory">Betreff</label><br/>
				<input type="text" name="subject" id="input_subject" class="max mandatory" value="'.htmlentities( $subject, ENT_COMPAT, 'UTF-8' ).'"/>
			</li>
			<li>
				<label for="input_message" class="mandatory">Nachricht <small>(Link zur Registrierung wird automatisch eingetragen)</small></label><br/>
				<textarea name="message" id="input_message" class="m-m max mandatory"></textarea>
			</li>
		</ul>
		<div class="buttonbar">
			'.UI_HTML_Elements::LinkButton( './', 'zurück', 'button cancel' ).'
			'.UI_HTML_Elements::Button( 'send', 'senden', 'button save' ).'
		</div>
	</fieldset>
</form>
';
?>