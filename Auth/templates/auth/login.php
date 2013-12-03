<?php

$w			= (object) $words['login'];
extract( $view->populateTexts( array( 'top', 'info', 'bottom' ), 'html/auth/login.' ) );
$formUrl	= './auth/login' . ( $from ? '?from='.rawurlencode( $from ) : '' );

return '
<div class="auth-login-text-top">'.$textTop.'</div>
<div class="column-left-50">
	<div class="column-left-90">
		<div class="auth-login-form">
			<form name="editUser" action="'.$formUrl.'" method="post">
				<fieldset>
					<legend class="login">'.$w->legend.'</legend>
					<div class="row-fluid">
						<div class="span6">
							<label for="input_login_username" class="mandatory">'.$w->labelUsername.'</label>
							<div class="input-prepend">
								<span class="add-on"><i class="icon-user"></i></span>
								<input type="text" name="login_username" id="input_login_username" class="span10 mandatory" value="'.htmlentities( $login_username, ENT_QUOTES, 'UTF-8' ).'" required/>
<!--								'.UI_HTML_Elements::Input( 'login_username', $login_username, 'span10 mandatory' ).'
-->							</div>
						</div>
						<div class="span6">
							<label for="input_login_password" class="mandatory">'.$w->labelPassword.'</label>
							<div class="input-prepend">
								<span class="add-on"><i class="icon-lock"></i></span>
								<input type="password" name="login_password" id="input_login_password" class="span10 mandatory" required/>
<!--								'.UI_HTML_Elements::Password( 'login_password', 'span10 mandatory' ).'
-->							</div>
						</div>
					</div>
					<div class="buttonbar">
						'.UI_HTML_Elements::Button( 'doLogin', '<i class="icon-ok icon-white"></i> '.$w->button, 'btn btn-primary' ).'
					</div>
				</fieldset>
			</form>
		</div>
	</div>
</div>
<div class="column-left-50">
	<div class="auth-login-text-info">'.$textInfo.'</div>
</div>
<div class="column-clear"></div>
<div class="auth-login-text-bottom">'.$textBottom.'</div>
';
?>
