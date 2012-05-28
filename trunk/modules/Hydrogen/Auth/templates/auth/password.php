<?php
$w		= (object) $words['password'];
$text	= $view->populateTexts( array( 'top', 'info', 'bottom' ), 'html/auth/password.' );

return '
'.$text['top'].'
<div class="column-left-50">
	<div class="column-left-90">
		<fieldset>
			<legend>'.$w->legend.'</legend>
			<form action="./auth/password" method="post">
				<ul class="input">
					<li>
						<label for="input_password_email" class="mandatory">'.$w->labelEmail.'</label><br/>
						<input type="text" name="password_email" id="input_password_email" class="max mandatory" value="'.$request->get( 'password_email' ).'">
					</li>
				</ul>
				<div class="buttonbar">
					'.UI_HTML_Elements::Button( 'confirm', $w->buttonSend, 'button save' ).'
				</div>
			</form>
		</fieldset>
	</div>
</div>
<div class="column-left-50">
	'.$text['info'].'
</div>
<div class="column-clear"></div>';
?>
