<?php

//  --  CHECK: DATABASE  --  //
if( !preg_match( '/^\//', $instance->path ) )
	$instance->path	= getEnv( 'DOCUMENT_ROOT' ).'/'.$instance->path;

if( !$instance->configPath )
	$instance->configPath	= 'config/';
if( !$instance->configFile )
	$instance->configFile	= 'config.ini';

$fileConfig	= $instance->path.$instance->configPath.$instance->configFile;

$panelDatabase	= '';
if( file_exists( $fileConfig ) ){
	$config	= new ADT_List_Dictionary( parse_ini_file( $fileConfig, FALSE ) );

	$status	= '';
	if( $config->get( 'database.driver' ) ){
		$hint	= 'Die Datenbankeinstellungen sind unvollständig';
		if( $config->get( 'database.driver' ) == "sqlite" ){
			$status	= 0;
			$hint	= 'Die Datenbank-Datei existiert nicht. Eine leere Datenbank wird bei Bedarf erzeugt.';
			$fileDb	= $config->get( 'database.name' );
			if( !preg_match( '/^\//', $fileDb ) )
					$fileDb	= $instance->path.$fileDb;
			if( file_exists( $fileDb ) ){
				$status	= 1;
				$hint	= 'Die Datenbank-Datei wurde gefunden.';
			}
			
		}
		else{
			$dsn	= Database_PDO_DataSourceName::renderStatic(
				$config->get( 'database.driver' ),
				$config->get( 'database.name' ),
				$config->get( 'database.host' ),
				$config->get( 'database.port' ),
				$config->get( 'database.username' ),
				$config->get( 'database.password' )
			);
			try{
				$dbc	= new Database_PDO_Connection( $dsn, $config->get( 'database.username' ), $config->get( 'database.password' ) );
				unset( $dbc );
				$status	= 1;
				$hint	= 'Die Datenbank konnte angesprochen werden.';

			}
			catch( PDOException $e ){
				$status	= 0;
				$hint	= $e->getMessage();
			}
		}
		$status	= '<li class="column-clear database-status-'.$status.'">'.$hint.'</li>';

	}
	
	$drivers	= array( '' => '- keiner -' );
	foreach( PDO::getAvailableDrivers() as $driver )
		$drivers[$driver]	= $words['database-drivers'][$driver];
	$optDriver	= UI_HTML_Elements::Options( $drivers, $config->get( 'database.driver' ) );
	$panelDatabase	= '
<form action="./admin/instance/configureDatabase/'.$instance->id.'" method="post">
	<fieldset>
		<legend class="database">Datenbank</legend>
		<ul class="input">
			<li class="column-left-30">
				<label for="input_database_driver" class="mandatory">Typ / Treiber</label><br/>
				<select name="database_driver" id="input_database_driver" class="max mandatory" onchange="showOptionals(this);">'.$optDriver.'</select>
			</li>
			<li class="column-left-60 optional database_driver database_driver-mysql database_driver-pgsql database_driver-mssql">
				<label for="input_database_host" class="mandatory">Host</label><br/>
				<input type="text" name="database_host" id="input_database_host" class="max mandatory" value="'.htmlentities( $config->get( 'database.host' ) ).'"/>
			</li>
			<li class="column-left-10 optional database_driver database_driver-mysql database_driver-pgsql database_driver-mssql">
				<label for="input_database_port" class="optional">Port</label><br/>
				<input type="text" name="database_port" id="input_database_port" class="max numeric" value="'.htmlentities( $config->get( 'database.port' ) ).'"/>
			</li>
			<li class="column-clear column-left-40 optional database_driver database_driver-mysql database_driver-pgsql database_driver-mssql database_driver-sqlite">
				<label for="input_database_name" class="mandatory">Name der Datenbank</label><br/>
				<input type="text" name="database_name" id="input_database_name" class="max mandatory" value="'.htmlentities( $config->get( 'database.name' ) ).'"/>
			</li>
			<li class="column-left-20 optional database_driver database_driver-mysql database_driver-pgsql database_driver-mssql database_driver-sqlite">
				<label for="input_database_prefix" class="optional">Tabellenpräfix</label><br/>
				<input type="text" name="database_prefix" id="input_database_prefixs" class="max" value="'.htmlentities( $config->get( 'database.prefix' ) ).'"/>
			</li>
			<li class="column-left-20 optional database_driver database_driver-mysql database_driver-pgsql database_driver-mssql">
				<label for="input_database_username" class="mandatory">Benutzername</label><br/>
				<input type="text" name="database_username" id="input_database_username" class="max mandatory" value="'.htmlentities( $config->get( 'database.username' ) ).'"/>
			</li>
			<li class="column-left-20 optional database_driver database_driver-mysql database_driver-pgsql database_driver-mssql">
				<label for="input_database_password" class="mandatory">Passwort</label><br/>
				<input type="password" name="database_password" id="input_database_password" class="max mandatory" value="'.htmlentities( $config->get( 'database.password' ) ).'"/>
			</li>
<!--			<li class="column-clear">
				<label for="input_database_log">Fehler-Log-Datei</label><br/>
				<input type="text" name="database_log" id="input_database_log" class="" value="'.htmlentities( $config->get( 'database.log.error' ) ).'"/>
			</li>-->
			'.$status.'
		</ul>
		<div class="buttonbar">
			'.UI_HTML_Elements::Button( 'save', 'speichern', 'button edit save' ).'
		</div>
	</fieldset>
</form>
	';
}

return $panelDatabase;
?>