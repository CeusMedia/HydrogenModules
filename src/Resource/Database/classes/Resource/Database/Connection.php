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
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2011-2021 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Database\PDO\Connection as DatabasePdoConnection;

/**
 *	Database resource using PDO wrapper from cmClasses.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2011 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */
class Resource_Database_Connection extends DatabasePdoConnection
{
	const STATUS_LOST			= -1;
	const STATUS_UNKNOWN		= 0;
	const STATUS_PREPARED		= 1;
	const STATUS_CONNECTED		= 2;

	/**	@var	Dictionary		$options	Module configuration options */
	protected Dictionary $options;

	protected int $status	= self::STATUS_UNKNOWN;

	protected object $auth;
	protected string $dsn;
	protected array $driverOptions;


	/**
	 *	Constructor, establishes Database Connection using a DSN. Set Error Handling to use Exceptions.
	 *	@access		public
	 *	@param		string			$dsn			Data Source Name
	 *	@param		?string			$username		Name of Database User
	 *	@param		?string			$password		Password of Database User
	 *	@param		array			$driverOptions	Array of Driver Options
	 *	@param		?Dictionary		$moduleOptions	...
	 *	@return		void
	 *	@see		http://php.net/manual/en/pdo.drivers.php
	 * @noinspection PhpMissingParentConstructorInspection
	 */
	public function __construct( string $dsn, ?string $username = NULL, ?string $password = NULL, array $driverOptions = [], Dictionary $moduleOptions = NULL )
	{
		$this->dsn				= $dsn;
		$this->auth				= (object) ['username' => $username, 'password' => $password ];
		$this->driverOptions	= $driverOptions;
		$this->options			= $moduleOptions ?? new Dictionary();
	}

	/**
	 *	Wrapper for PDO::exec to support lazy connection mode.
	 *	Tries to connect database if not connected yet (lazy mode).
	 *	@access		public
	 *	@param		string		$statement		SQL statement to execute
	 *	@return		integer		Number of affected rows
	 */
	public function exec( string $statement ): int
	{
		if( self::STATUS_UNKNOWN === $this->status )
			throw new RuntimeException( 'No connection options set' );
		if( self::STATUS_PREPARED === $this->status )
			$this->tryToConnect();
		return parent::exec( $statement );
	}

	public function getMode(): string
	{
		return $this->options->mode ?? 'instant';
	}

	/**
	 *	Returns database name from database or configuration.
	 *	Returns configuration value if database is not connected.
	 *	@access		public
	 *	@param		boolean		$used		Get currently used database (default) or by configuration
	 *	@return		string
	 */
	public function getName( bool $used = TRUE ): string
	{
		if( $this->status === self::STATUS_CONNECTED && $used )
			return $this->query( 'SELECT DATABASE();' )->fetch( PDO::FETCH_NUM )[0];
		return $this->options->get( 'access.name' );
	}

	/**
	 *	Returns table prefix from configuration used on connecting.
	 *	@access		public
	 *	@return		string
	 */
	public function getPrefix(): string
	{
		return $this->options->get( 'access.prefix', '' );
	}

	/**
	 *	Wrapper for PDO::query to support lazy connection mode.
	 *	Tries to connect database if not connected yet (lazy mode).
	 *	@access		public
	 *	@param		string		$query			SQL statement to query
	 *	@param		integer		$fetchMode		... (default: 2)
	 *	@param		mixed		$fetchModeArgs	Arguments of custom class constructor when the mode parameter is set to PDO::FETCH_CLASS.
	 *	@return		PDOStatement				PDO statement containing fetchable results
	 */
	public function query( string $query,  ?int $fetchMode = null, mixed ...$fetchModeArgs ): PDOStatement
	{
		if( self::STATUS_UNKNOWN === $this->status )
			throw new RuntimeException( 'No connection options set' );
		if( self::STATUS_PREPARED === $this->status )
			$this->tryToConnect();
		return parent::query( $query, $fetchMode, ...$fetchModeArgs );
	}

	/**
	 *	Sets database name in configuration.
	 *	@access		public
	 *	@param		string		$name		Database name
	 *	@param		boolean		$use		Use in database connection (default) or only edit configuration
	 *	@return		self
	 *	@todo		check persistent mode change on instant connections, see disabled lines below
	 */
	public function setName( string $name, bool $use = TRUE ): self
	{
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

	public function setOptions( Dictionary $options ): self
	{
		$this->options	= $options;
		if( $this->status <= self::STATUS_PREPARED )
			$this->status	= self::STATUS_PREPARED;
		return $this;
	}

	/**
	 *	Sets table prefix in configuration.
	 *	@access		public
	 *	@param		string		$prefix		Table prefix to set in configuration
	 *	@return		self
	 */
	public function setPrefix( string $prefix ): self
	{
		$this->options->set( 'access.prefix', $prefix );
		return $this;
	}

	/**
	 *	To be called on connection deconstruction.
	 *	Does nothing right now.
	 *	@access		public
	 *	@return		self
	 */
	public function tearDown(): self
	{
		return $this;
	}

	//  --  PROTECTED  --  //

	/**
	 *	Sets up connection to database, if configured with database module or main config (deprecated).
	 *
	 *	Attention: If using MySQL and UTF-8 the charset must bet se after connection is established.
	 *	Therefore the option MYSQL_ATTR_INIT_COMMAND is set by default, which hinders lazy connection mode (which is not implemented yet).
	 *	In future, having lazy mode, the config pair "charset" will be realized by implementing a statement queue, which is run before a lazy connection is used the first time.
	 *
	 *	Attention: Using statement log means that EVERY statement sent to database will be logged.
	 *	Applications with heavy database use will slow down and create large log files.
	 *	Be sure to rotate the logs or remove them frequently to avoid low hard disk space.
	 *	Disable this feature after development/debugging!
	 *
	 *	@todo		implement lazy mode
	 *	@todo		realize todos above now that lazy mode has been integrated by a different solution
	 */
	protected function setUp(): void
	{
	}

	public function tryToConnect(): void
	{
		parent::__construct( $this->dsn, $this->auth->username, $this->auth->password, $this->driverOptions );			//  connect to database
		$this->status = self::STATUS_CONNECTED;
		$this->query( 'USE '.$this->getName().';' );
	}
}

