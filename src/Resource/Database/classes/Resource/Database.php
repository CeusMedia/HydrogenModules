<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

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
 *	along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2011-2024 Christian Würker
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Database\PDO\DataSourceName as PdoDataSourceName;
use CeusMedia\HydrogenFramework\Environment;

/**
 *	Database resource using PDO wrapper from cmClasses.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2011 Christian Würker
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */
class Resource_Database
{
	protected Environment $env;
	/**	@var	Dictionary		$options	Module configuration options */
	protected Dictionary $options;
	protected array $connections	= [];
	protected array $defaultDriverOptions	= [
		'ATTR_PERSISTENT'				=> TRUE,
		'ATTR_ERRMODE'					=> "PDO::ERRMODE_EXCEPTION",
		'ATTR_DEFAULT_FETCH_MODE'		=> "PDO::FETCH_OBJ",
		'ATTR_CASE'						=> "PDO::CASE_NATURAL",
		'MYSQL_ATTR_USE_BUFFERED_QUERY'	=> TRUE,
		'MYSQL_ATTR_INIT_COMMAND'		=> "SET NAMES 'utf8';",
	];

	protected string $currentKey	= 'default';

	/**
	 *	@param		Environment		$env
	 *	@throws		Exception
	 */
	public function __construct( Environment $env )
	{
		$this->env		= $env;
		$this->options	= $this->env->getConfig()->getAll( 'module.resource_database.', TRUE );
		$this->setUp();
	}

	public function __call( string $name, array $arguments )
	{
		return $this->connections[$this->currentKey]->$name( ...$arguments );
	}

	public function __get( string $name )
	{
		return $this->connections[$this->currentKey]->$name;
	}

	public function getConnection( ?string $key = 'default' ): PDO
	{
		if( !array_key_exists( $key, $this->connections ) )
			throw new DomainException( 'No connection with key "'.$key.'"' );
		return $this->connections[$key];
	}

	protected function realizeDriverOptions(): array
	{
		$options	= array_merge(																	//  merge option pairs ...
			$this->defaultDriverOptions,															//  ... from default driver options
			$this->options->getAll( 'option.' )												//  ...  and options configured by module
		);
		$list		= [];
		foreach( $options as $key => $value ){														//  iterate all database options
			if( $key == "ATTR_ERRMODE" ){
				if( !str_starts_with( $value, 'PDO' ) ){										//  value is newer style without PDO prefix
					if( !in_array( $value, ['SILENT', 'WARNING', 'EXCEPTION'] ) )					//  invalid option set
						continue;																	//  skip this option
					$value	= 'PDO::ERRMODE_'.$value;												//  extend value by PDO prefix
				}
			}
			else if( $key == "ATTR_CASE" ){
				if( !str_starts_with( $value, 'PDO' ) ){										//  value is newer style without PDO prefix
					if( !in_array( $value, ['NATURAL', 'LOWER', 'UPPER'] ) )						//  invalid option set
						continue;																	//  skip this option
					$value	= 'PDO::CASE_'.$value;													//  extend value by PDO prefix
				}
			}
			else if( $key == "ATTR_DEFAULT_FETCH_MODE" ){
				if( !str_starts_with( $value, 'PDO' ) ){										//  value is newer style without PDO prefix
					if( !in_array( $value, ['ASSOC', 'BOTH', 'NUM', 'OBJ'] ) )						//  invalid option set
						continue;																	//  skip this option
					$value	= 'PDO::FETCH_'.$value;													//  extend value by PDO prefix
				}
			}
			if( !defined( "PDO::".$key ) )												//  no PDO constant for option key
				throw new InvalidArgumentException( 'Unknown constant PDO::'.$key );		//  quit with exception
			if( is_string( $value ) && preg_match( "/^[A-Z][A-Z0-9_:]+$/", $value ) )		//  option value is a constant name
				$value	= constant( $value );														//  replace option value string by constant value
			$list[constant( "PDO::".$key )]	= $value;										//  note option
		}
		return $list;
	}

	/**
	 *	@return		void
	 *	@throws		Exception
	 */
	protected function setUp(): void
	{
		$dba	= (object) $this->options->getAll( 'access.' );									//  extract connection access configuration
		if( empty( $dba->driver ) )
			throw new RuntimeException( 'No database driver set' );

		$dbc	= new Resource_Database_Connection(
			PdoDataSourceName::renderStatic(
				$dba->driver,
				$dba->name,
				( '' !== ( $dba->host ?? '' ) ) ? $dba->host : NULL,
				( '' !== ( $dba->port ?? '' ) ) ? (int) $dba->port : NULL,
				( '' !== ( $dba->username ?? '' ) ) ? $dba->username : NULL,
				( '' !== ( $dba->password ?? '' ) ) ? $dba->password : NULL,
			),
			$dba->username,
			$dba->password,
			$this->realizeDriverOptions(),
			$this->options,
		);
		if( 'instant' === $dbc->getMode() )
			$dbc->tryToConnect();

		$log		= $this->options->getAll( 'log.', TRUE);
		$pathLogs	= $this->env->getConfig()->get( 'path.logs' );
		if( $log->get( 'statements' ) && $log->get( 'file.statements' ) )
			$dbc->setStatementLogFile( $pathLogs.$log->get( 'file.statements' ) );
		if( $log->get( 'errors' ) && $log->get( 'file.errors' ) )
			$dbc->setErrorLogFile( $pathLogs.$log->get( 'file.errors' ) );
#		if( $charset && $this->driver == 'mysql' )													//  a character set is configured on a MySQL database
#			$this->exec( "SET NAMES '".$charset."';" );												//  set character set

		$this->connections['default']	= $dbc;
	}
}
