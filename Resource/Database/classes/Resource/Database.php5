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
class Resource_Database extends \CeusMedia\Database\PDO\Connection
{
	const STATUS_LOST			= -1;
	const STATUS_UNKNOWN		= 0;
	const STATUS_PREPARED		= 1;
	const STATUS_CONNECTED		= 2;

	protected $env;
	/**	@var	ADT_List_Dictionary		$options	Module configuration options */
	protected $options;

	protected $defaultDriverOptions	= array(
		'ATTR_PERSISTENT'				=> TRUE,
		'ATTR_ERRMODE'					=> "PDO::ERRMODE_EXCEPTION",
		'ATTR_DEFAULT_FETCH_MODE'		=> "PDO::FETCH_OBJ",
		'ATTR_CASE'						=> "PDO::CASE_NATURAL",
		'MYSQL_ATTR_USE_BUFFERED_QUERY'	=> TRUE,
		'MYSQL_ATTR_INIT_COMMAND'		=> "SET NAMES 'utf8';",
	);

	protected $status	= self::STATUS_UNKNOWN;

	public function __construct( CMF_Hydrogen_Environment $env ){
		$this->env		= $env;
		$this->options	= $this->env->getConfig()->getAll( 'module.resource_database.', TRUE );
		$this->setUp();
	}

	/**
	 *	Wrapper for PDO::exec to support lazy connection mode.
	 *	Tries to connect database if not connected yet (lazy mode).
	 *	@access		public
	 *	@param		string		$statement		SQL statement to execute
	 *	@return		integer		Number of affected rows
	 */
	public function exec( $statement ): int
	{
		if( $this->status == self::STATUS_UNKNOWN )
			$this->tryToConnect();
		return parent::exec( $statement );
	}

	public function getMode(){
		return isset( $this->options->mode ) ? $this->options->mode : 'instant';
	}

	/**
	 *	Returns database name from database or configuration.
	 *	Returns configuration value if database is not connected.
	 *	@access		public
	 *	@param		boolean		$used		Get currently used database (default) or by configuration
	 *	@return		string
	 */
	public function getName( $used = TRUE ){
		if( $this->status === self::STATUS_CONNECTED && $used )
			return $this->query( 'SELECT DATABASE();' )->fetch( PDO::FETCH_NUM )[0];
		return $this->options->get( 'access.name' );
	}

	/**
	 *	Returns table prefix from configuration used on connecting.
	 *	@access		public
	 *	@return		string
	 */
	public function getPrefix(){
		return $this->options->get( 'access.prefix' );
	}

	/**
	 *	Wrapper for PDO::query to support lazy connection mode.
	 *	Tries to connect database if not connected yet (lazy mode).
	 *	@access		public
	 *	@param		string		$statement		SQL statement to query
	 *	@param		integer		$fetchMode		... (default: 2)
	 *	@return		PDOStatement				PDO statement containing fetchable results
	 */
	public function query( $statement, $fetchMode = 2 ){
		if( $this->status == self::STATUS_UNKNOWN )
			$this->tryToConnect();
		return parent::query( $statement, $fetchMode );
	}

	/**
	 *	Sets database name in configuration.
	 *	@access		public
	 *	@param		boolean		$use		Use in database connection (default) or only edit configuration
	 *	@return		self
	 *	@todo		check persistent mode change on instant connections, see disabled lines below
	 */
	public function setName( $name, $use = TRUE ){
		if( $name !== $this->getName( $use ) ){
			if( $use ){
//				if( $this->getMode() !== 'lazy' )
//					$this->setAttribute( PDO::ATTR_PERSISTENT, FALSE );
				$this->query( 'USE '.$name.';' );
			}
			$this->options->set( 'access.name', $name );
		}
		return $this;
	}

	/**
	 *	Sets table prefix in configuration.
	 *	@access		public
	 *	@param		string		$prefix		Table prefix to set in configuration
	 *	@return		self
	 */
	public function setPrefix( $prefix ){
		$this->options->set( 'access.prefix', $prefix );
		return $this;
	}

	/**
	 *	To be called on connection deconstruction.
	 *	Does nothing right now.
	 *	@access		public
	 *	@return		self
	 */
	public function tearDown(){
		return $this;
	}

	//  --  PROTECTED  --  //

	protected function realizeDriverOptions(){
		$options	= array_merge(																	//  merge option pairs ...
			$this->defaultDriverOptions,															//  ... from default driver options
			$this->options->getAll( 'option.' )														//  ...  and options configured by module
		);
		$list		= array();
		foreach( $options as $key => $value ){														//  iterate all database options
			if( $key == "ATTR_ERRMODE" ){
				if( !preg_match( '/^PDO/', $value ) ){												//  value is newer style without PDO prefix
					if( !in_array( $value, array( 'SILENT', 'WARNING', 'EXCEPTION' ) ) )			//  invalid option set
						continue;																	//  skip this option
					$value	= 'PDO::ERRMODE_'.$value;												//  extend value by PDO prefix
				}
			}
			else if( $key == "ATTR_CASE" ){
				if( !preg_match( '/^PDO/', $value ) ){												//  value is newer style without PDO prefix
					if( !in_array( $value, array( 'NATURAL', 'LOWER', 'UPPER' ) ) )					//  invalid option set
						continue;																	//  skip this option
					$value	= 'PDO::CASE_'.$value;													//  extend value by PDO prefix
				}
			}
			else if( $key == "ATTR_DEFAULT_FETCH_MODE" ){
				if( !preg_match( '/^PDO/', $value ) ){												//  value is newer style without PDO prefix
					if( !in_array( $value, array( 'ASSOC', 'BOTH', 'NUM', 'OBJ' ) ) )				//  invalid option set
						continue;																	//  skip this option
					$value	= 'PDO::FETCH_'.$value;													//  extend value by PDO prefix
				}
			}
			if( !defined( "PDO::".$key ) )															//  no PDO constant for for option key
				throw new InvalidArgumentException( 'Unknown constant PDO::'.$key );				//  quit with exception
			if( is_string( $value ) && preg_match( "/^[A-Z][A-Z0-9_:]+$/", $value ) )				//  option value is a constant name
				$value	= constant( $value );														//  replace option value string by constant value
			$list[constant( "PDO::".$key )]	= $value;												//  note option
		}
		return $list;
	}

	/**
	 *	Sets up connection to database, if configured with database module or main config (deprecated).
	 *
	 *	Attention: If using MySQL and UTF-8 the charset must bet se after connection is established.
	 *	Therefore the option MYSQL_ATTR_INIT_COMMAND is set by default, which hinders lazy connection mode (which is not implemented yet).
	 *	In future, having lazy mode, the config pair "charset" will be realized by implementing a statement queue, which is run before a lazy connection is used the first time.
	 *
	 *	Attention: Using statement log means that EVERY statement send to database will be logged.
	 *	Applications with heavy database use will slow down and create large log files.
	 *	Be sure to rotate the logs or remove them frequently to avoid low hard disk space.
	 *	Disable this feature after development/debugging!
	 *
	 *	@todo		implement lazy mode
	 *	@todo		realize todos above now that lazy mode has been integrated by a different solution
	 */
	protected function setUp(){
		if( $this->getMode() === 'instant' )
			$this->tryToConnect();
	}

	protected function tryToConnect(){
		$access		= (object) $this->options->getAll( 'access.' );									//  extract connection access configuration
		if( empty( $access->driver ) )
			throw new RuntimeException( 'No database driver set' );
		$dsn		= new \CeusMedia\Database\PDO\DataSourceName( $access->driver, $access->name );
		!empty( $access->host )		? $dsn->setHost( $access->host ) : NULL;
		!empty( $access->port )		? $dsn->setPort( $access->port ) : NULL;
		!empty( $access->username )	? $dsn->setUsername( $access->username ) : NULL;
		!empty( $access->password )	? $dsn->setPassword( $access->password ) : NULL;

		$driverOptions	= $this->realizeDriverOptions();

		parent::__construct( $dsn, $access->username, $access->password, $driverOptions );			//  connect to database
		$this->status = self::STATUS_CONNECTED;
		$this->query( 'USE '.$access->name.';' );

		$log		= $this->options->getAll( 'log.', TRUE);
		$pathLogs	= $this->env->getConfig()->get( 'path.logs' );
		if( $log->get( 'statements' ) && $log->get( 'file.statements' ) )
			$this->setStatementLogFile( $pathLogs.$log->get( 'file.statements' ) );
		if( $log->get( 'errors' ) && $log->get( 'file.errors' ) )
			$this->setErrorLogFile( $pathLogs.$log->get( 'file.errors' ) );
#		if( $charset && $this->driver == 'mysql' )													//  a character set is configured on a MySQL database
#			$this->exec( "SET NAMES '".$charset."';" );												//  set character set
	}
}
?>
