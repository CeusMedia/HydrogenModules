<?php

$w			= (object) $words['login'];
extract( $view->populateTexts( array( 'top', 'info', 'bottom' ), 'html/auth/login.' ) );
$formUrl	= './auth/login' . ( $from ? '?from='.rawurlencode( $from ) : '' );

return '
<div class="auth-login-text-top">'.$textTop.'</div>
<div class="row-fluid">
	<div class="span4">
		<div class="content-panel content-panel-form">
			<h3>'.$w->legend.'</h3>
			<div class="content-panel-inner">
				<div class="auth-login-form">
					<form name="editUser" action="'.$formUrl.'" method="post">
						<div class="row-fluid">
							<div class="span7">
								<label for="login_username" class="mandatory">'.$w->labelUsername.'</label>
								'.UI_HTML_Tag::create( 'input', NULL, array( 'value' => htmlentities( $login_username, ENT_QUOTES, 'UTF-8' ), 'class' => 'span12 mandatory', 'type' => 'text', 'name' => 'login_username', 'id' => 'input_login_username', 'required' => 'required' ) ).'
							</div>
							<div class="span5">
								<label for="login_password" class="mandatory">'.$w->labelPassword.'</label>
								'.UI_HTML_Tag::create( 'input', NULL, array( 'value' => NULL, 'class' => 'span12 mandatory', 'type' => 'password', 'name' => 'login_password', 'id' => 'input_login_password', 'required' => 'required' ) ).'
							</div>
						</div>
						<div class="row-fluid">
							<div class="span12 buttonbar">
								<button type="submit" name="doLogin" class="btn btn-primary"><i class="icon-ok icon-white"></i> '.$w->button.'</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="span6">
		<div class="auth-login-text-info">'.$textInfo.'</div>
	</div>
</div>
<div class="auth-login-text-bottom">'.$textBottom.'</div>
';
?>
