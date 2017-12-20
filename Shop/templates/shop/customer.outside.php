<?php

return '<div class="row-fluid">
	<div class="span4 offset1">
		<div class="content-panel">
			<h3>Einloggen</h3>
			<div class="content-panel-inner">
				<p>Sie haben bereits ein Benutzerkonto?<br/>Dann bitte hier mit den Zugangsdaten einloggen.</p>
				<form action="./shop/login" method="post">
					<label for="input_email">E-Mail-Adresse</label>
					<input type="text" name="email" id="input_email" class="span10" value="'.htmlentities( $email, ENT_QUOTES, 'UTF-8' ).'"/>
					<label for="input_password">Passwort</label>
					<input type="password" name="password" id="input_password" class="span10"/>
					<div class="buttonbar">
						<button type="submit" name="save" class="btn btn-primary"><i class="fa fa-fw fa-sign-in"></i> einloggen</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	<div class="span4 offset1">
		<div class="content-panel">
			<h3>Registrieren</h3>
			<div class="content-panel-inner">
				<p>Sie haben noch kein Benutzerkonto?<br/>...</p>
<!--				<form action="./shop/register" method="post">
					<label for="input_email">E-Mail-Adresse</label>
					<input type="text" name="email" id="input_email" class="span10"/>
					<p><small>Ein Passwort wird automatisch vergeben und an die angegebene E-Mail-Adresse gesendet.</small></p>
					<div class="buttonbar">
						<button type="submit" name="save" class="btn btn-primary"><i class="fa fa-fw fa-sign-in"></i> registrieren</button>
					</div>-->
						<a href="./auth/register?from=shop/customer" class="btn btn-primary"><i class="fa fa-fw fa-pencil"></i> registrieren</a>
				</form>
			</div>
		</div>
	</div>
</div>';

?>
