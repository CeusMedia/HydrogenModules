<?php

$text	= $this->populateTexts( array( 'top', 'info', 'bottom' ), 'html/auth.login.' );
$heading	= empty( $words['login']['heading'] ) ? "" : UI_HTML_Tag::create( 'h2', $words['login']['heading'] );

return '
'.$heading.'
<div class="auth-login-text-top">'.$text['top'].'</div>
<div class="auth-login-text-info">'.$text['info'].'</div>
<div class="auth-login-form">
	<form name="editUser" action="./auth/login" method="post">
		<fieldset>
			<legend>'.$words['login']['legend'].'</legend>
			<ul class="input">
				<li>
					<label for="username">'.$words['login']['labelUsername'].'</label><br/>
					'.UI_HTML_Elements::Input( 'username', $data['username'], 'm' ).'
				</li>
				<li>
					<label for="password">'.$words['login']['labelPassword'].'</label><br/>
					'.UI_HTML_Elements::Password( 'password', 'm' ).'
				</li>
			</ul>
			<div class="buttonbar">
				'.UI_HTML_Elements::Button( 'login', $words['login']['button'], 'button save' ).'
			</div>
		</fieldset>
	</form>
</div>
<div class="auth-login-text-bottom">'.$text['bottom'].'</div>
';
?>
