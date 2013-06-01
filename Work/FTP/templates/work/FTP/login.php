<?php
extract( $fromSession );

$texts	= $this->populateTexts( array( 'login.info' ), 'work/ftp/' );
$port	= strlen( trim( $port ) ) ? $port : 21;

return '
<div class="row-fluid">
	<div class="span6">
		<h3>Verbindung zu FTP-Server herstellen</h3>
		<form action="./work/FTP/login" methods="post">
			<div class="row-fluid">
				<div class="span5">
					<label for="input_ftp_host">Host <small class="muted"><em>(z.B. example.org)</em></small></label>
					<input type="text" name="ftp_host" id="input_ftp_host" class="span12" value="'.htmlentities( $host, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span1">
					<label for="input_ftp_host">Port</label>
					<input type="text" name="ftp_port" id="input_ftp_port" class="span12" value="'.htmlentities( $port, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span6">
					<label for="input_ftp_path">Pfad</label>
					<input type="text" name="ftp_path" id="input_ftp_path" class="span12" value="'.htmlentities( $path, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span6">
					<label for="input_ftp_username">Benutzer</label>
					<input type="text" name="ftp_username" id="input_ftp_username" class="span12" value="'.htmlentities( $username, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span6">
					<label for="input_ftp_password">Passwort</label>
					<input type="password" name="ftp_password" id="input_ftp_password" class="span12"/>
				</div>
			</div>
			<div class="buttonbar">
				<button type="submit" name="save" class="btn btn-small btn-success"><i class="icon-ok icon-white"></i> einrichten</button>
			</div>
		</form>
	</div>
	<div class="span6">
		'.$texts['login.info'].'
	</div>
</div>
';
?>
