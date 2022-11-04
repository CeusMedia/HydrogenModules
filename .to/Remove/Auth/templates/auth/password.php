<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;

$w		= (object) $words['password'];
extract( $view->populateTexts( array( 'top', 'info', 'bottom' ), 'html/auth/password.' ) );

return '
'.$textTop.'
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
					'.HtmlElements::Button( 'confirm', $w->buttonSend, 'button save' ).'
				</div>
			</form>
		</fieldset>
	</div>
</div>
<div class="column-left-50">
	'.$textInfo.'
</div>
<div class="column-clear"></div>';
?>
