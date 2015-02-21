<?php
$w		= (object) $words['password'];
extract( $view->populateTexts( array( 'top', 'info', 'bottom' ), 'html/auth/password/' ) );

return '
'.$textTop.'
<div class="content-panel content-panel-form">
	<h3>'.$w->legend.'</h3>
	<div class="content-panel-inner">
		<div class="row-fluid">
			<div class="span6">
				<form action="./auth/password" method="post">
					<div class="row-fluid">
						<div class="span12">
							<label for="input_password_email" class="mandatory">'.$w->labelEmail.'</label>
							<input type="text" name="password_email" id="input_password_email" class="span12 -max mandatory" value="'.$request->get( 'password_email' ).'">
						</div>
					</div>
					<div class="buttonbar">
						<button type="submit" class="btn btn-primary" name="confirm"><i class="icon-envelope icon-white"></i> '.$w->buttonSend.'</button>
					</div>
				</form>
			</div>
			<div class="span6">
				'.$textInfo.'
			</div>
		</div>
	</div>
</div>';
?>
