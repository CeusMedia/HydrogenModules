<?php

$w			= (object) $words['login'];
extract( $view->populateTexts( array( 'top', 'info', 'bottom' ), 'html/auth/login/' ) );
$formUrl	= './auth/login' . ( $from ? '?from='.rawurlencode( $from ) : '' );

return '
<div class="auth-login-form">
	<h3>'.$w->legend.'</h3>
	<div class="row-fluid">
		<div class="span12 auth-login-text-top">'.$textTop.'</div>
	</div>
	<div class="row-fluid">
		<div class="span6">
			<form name="editUser" action="'.$formUrl.'" method="post">
				<div class="row-fluid">
					<div class="span12">
						<label for="input_login_username" class="mandatory">'.$w->labelUsername.'</label>
						<div class="input-prepend">
							<span class="add-on"><b class="fa fa-user fa-fw"></b></span>
							<input type="text" name="login_username" id="input_login_username" class="span10 mandatory" value="'.htmlentities( $login_username, ENT_QUOTES, 'UTF-8' ).'" required/>
						</div>
					</div>
				</div>
				<div class="row-fluid">
					<div class="span12">
						<label for="input_login_password" class="mandatory">'.$w->labelPassword.'</label>
						<div class="input-prepend">
							<span class="add-on"><b class="fa fa-key fa-fw"></b></span>
							<input type="password" name="login_password" id="input_login_password" class="span10 mandatory" required/>
						</div>
					</div>
				</div>
				<div class="row-fluid">
					<div class="buttonbar span12">
						'.UI_HTML_Elements::Button( 'doLogin', '<b class="fa fa-check fa-fw fa-inverse not-icon-ok not-icon-white"></b> '.$w->button, 'btn btn-primary' ).'
					</div>
				</div>
			</form>
		</div>
		<div class="span5">
			<div class="auth-login-text-info">'.$textInfo.'</div>
		</div>
	</div>
	<div class="row-fluid">
		<div class="span12 auth-login-text-bottom">'.$textBottom.'</div>
	</div>
</div>
';
?>
