<?php
/**
 *	Database resource using PDO wrapper from cmClasses.
 *
 *	Copyright (c) 2011 Christian Würker (ceusmedia.de)
 *
 *	This program is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation, either version 3 of the License, or
 *	(at your option) any later version.
 *
 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU General Public License for more details.
 *
 *	You should have received a copy of the GNU General Public License
 *	along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *	@category		cmFrameworks
 *	@package		Hydrogen.Environment.Resource.Database
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2011 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.4
 */
/**
 *	Database resource using PDO wrapper from cmClasses.
 *	@category		cmFrameworks
 *	@package		Hydrogen.Environment.Resource.Database
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2011 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.4
 */
class Resource_Database extends \DB_PDO_Connection
{
	protected $env;
	/**	@var	ADT_List_Dictionary		$options	Module configuration options */
	protected $options;

	public function __construct( CMF_Hydrogen_Environment_Abstract $env ){
		$this->env		= $env;
		$this->options	= $this->env->getConfig()->getAll( 'module.resource_database.', TRUE );
		$this->setUp();
	}

	/**
	 *	Returns table prefix from configuration.
	 *	@access		public
	 *	@return		string
	 */
	public function getPrefix(){
		return $this->options->get( 'access.prefix' );
	}

	/**
	 *	Sets up connection to database, if configured with database module or main config (deprecated).
	 *
	 *	Attention: If using MySQL and UTF-8 the charset must bet set after connection established.
	 *	Therefore the option MYSQL_ATTR_INIT_COMMAND is set by default, which hinders lazy connection mode (which is not implemented yet).
	 *	In future, having lazy mode, the config pair "charset" will be realized by implementing a statement queue, which is run before a lazy connection is used the first time.
	 *
	 *	Attention: Using statement log means that EVERY statement send to database will be logged.
	 *	Applications with heavy database use will slow down and create large log files.
	 *	Be sure to rotate the logs or remove them frequently to avoid low hard disk space.
	 *	Disable this feature after development/debugging!
	 *
	 *	@todo		implement lazy mode
	 *	@todo		0.7: clean deprecated code
	 */
	protected function setUp(){
		$access		= (object) $this->options->getAll( 'access.' );									//  extract connection access configuration
		if( empty( $access->driver ) )
			throw new RuntimeException( 'No database driver set' );
		$dsn		= new \DB_PDO_DataSourceName( $access->driver, $access->name );
		!empty( $access->host )		? $dsn->setHost( $access->host ) : NULL;
		!empty( $access->port )		? $dsn->setPort( $access->port ) : NULL;
		!empty( $access->username )	? $dsn->setUsername( $access->username ) : NULL;
		!empty( $access->password )	? $dsn->setPassword( $access->password ) : NULL;

		$options	= $this->options->getAll( 'option.' );											//  get connection options
		$defaultOptions	= array(
			'ATTR_PERSISTENT'				=> TRUE,
			'ATTR_ERRMODE'					=> "PDO::ERRMODE_EXCEPTION",
			'ATTR_DEFAULT_FETCH_MODE'		=> "PDO::FETCH_OBJ",
			'ATTR_CASE'						=> "PDO::CASE_NATURAL",
			'MYSQL_ATTR_USE_BUFFERED_QUERY'	=> TRUE,
			'MYSQL_ATTR_INIT_COMMAND'		=> "SET NAMES 'utf8';",
		);
		$options	+= $defaultOptions;

		//  --  DATABASE OPTIONS  --  //
		$driverOptions	= array();																	//  @todo: to be implemented
		foreach( $options as $key => $value ){														//  iterate all database options
			if( !defined( "PDO::".$key ) )															//  no PDO constant for for option key
				throw new InvalidArgumentException( 'Unknown constant PDO::'.$key );				//  quit with exception
			if( is_string( $value ) && preg_match( "/^[A-Z][A-Z0-9_:]+$/", $value ) )				//  option value is a constant name
				$value	= constant( $value );														//  replace option value string by constant value 
			$driverOptions[constant( "PDO::".$key )]	= $value;									//  note option
		}

		parent::__construct( $dsn, $access->username, $access->password, $driverOptions );			//  connect to database

		$log		= $this->options->getAll( 'log.', TRUE);
		$pathLogs	= $this->env->getConfig()->get( 'path.logs' );
		if( $log->get( 'statements' ) && $log->get( 'file.statements' ) )
			$this->setStatementLogFile( $pathLogs.$log->get( 'file.statements' ) );
		if( $log->get( 'errors' ) && $log->get( 'file.errors' ) )
			$this->setErrorLogFile( $pathLogs.$log->get( 'file.errors' ) );
#		if( $charset && $this->driver == 'mysql' )													//  a character set is configured on a MySQL database
#			$this->exec( "SET NAMES '".$charset."';" );												//  set character set
	}

	public function tearDown(){}
}
?>
