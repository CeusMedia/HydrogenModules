<?php
$w		= (object) $words['confirm'];
extract( $view->populateTexts( array( 'top', 'info', 'bottom' ), 'html/auth/confirm.' ) );

return '
'.$textTop.'
<div class="column-left-50">
	<div class="column-left-90">
		<fieldset>
			<legend>'.$w->legend.'</legend>
			<form action="./auth/confirm" method="post">
				<ul class="input">
					<li>
						<label for="input_confirm_code" class="mandatory">'.$w->labelCode.'</label><br/>
						<input type="text" name="confirm_code" id="input_confirm_code" class="max mandatory" value="'.$request->get( 'confirm_code' ).'">
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
	'.$textInfo.'
</div>
<div class="column-clear"></div>';
?>
