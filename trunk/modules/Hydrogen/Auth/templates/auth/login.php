<?php
$content	= $this->loadContentFile( 'html/auth.login.info.html' );
return '
<div style="float: left; width: 200px; margin-right: 20px">
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
<div style="float: left; width: 770px">
		<fieldset>
			<legend class="info">'.$words['login-info']['legend'].'</legend>
			'.$content.'
		</fieldset>
</div>
';
?>
