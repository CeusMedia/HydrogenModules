<?php

$w	= (object) $words['login'];

$text	= $this->populateTexts( array( 'top', 'info', 'bottom' ), 'html/auth.login.' );

return '
<div class="auth-login-text-top">'.$text['top'].'</div>
<div class="column-left-50">
	<div class="column-left-90">
		<div class="auth-login-form">
			<form name="editUser" action="./auth/login" method="post">
				<fieldset>
					<legend class="login">'.$w->legend.'</legend>
					<ul class="input">
						<li>
							<div class="column-left-60">
								<label for="login_username" class="mandatory">'.$w->labelUsername.'</label><br/>
								'.UI_HTML_Elements::Input( 'login_username', $data['login_username'], 'max mandatory' ).'
							</div>
							<div class="column-left-40">
								<label for="login_password" class="mandatory">'.$w->labelPassword.'</label><br/>
								'.UI_HTML_Elements::Password( 'login_password', 'max mandatory' ).'
							</div>
							<div class="column-clear"></div>
						</li>
					</ul>
					<div class="buttonbar">
						'.UI_HTML_Elements::Button( 'doLogin', $w->button, 'button save' ).'
					</div>
				</fieldset>

			</form>
		</div>
	</div>
</div>
<div class="column-left-50">
	<div class="auth-login-text-info">'.$text['info'].'</div>
</div>
<div class="column-clear"></div>
<div class="auth-login-text-bottom">'.$text['bottom'].'</div>
';
?>
