<?php
/**
 *	Setup for access control list using a remote server.
 *
 *	Copyright (c) 2010-2024 Christian Würker (ceusmedia.de)
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
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource.Acl
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2024 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */

use CeusMedia\HydrogenFramework\Environment as Environment;
use CeusMedia\HydrogenFramework\Environment\Resource\Acl\Abstraction;

/**
 *	Setup for access control list using a remote server.
 *
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource.Acl
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2024 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
class Resource_JSON_Acl extends Abstraction
{
	protected Resource_JSON_Client $server;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		Environment		$env	Environment Object
	 *	@return		void
	 */
	public function __construct( Environment $env )
	{
		parent::__construct( $env );
		$config			= $env->getConfig();
		$this->server	= $this->env->get( $config->get( 'module.resource_json_client.envKey' ) );
	}

	/**
	 *	Returns all rights of a role.
	 *	@access		protected
	 *	@param		string		$roleId			Role ID
	 *	@return		array
	 */
	protected function getRights( string $roleId ): array
	{
		if( $this->hasFullAccess( $roleId ) )
			return [];
		if( $this->hasNoAccess( $roleId ) )
			return [];
		if( !isset( $this->rights[$roleId] ) ) {
			$rights	= $this->server->getData( 'role', 'getRights', [$roleId] );
			$this->rights[$roleId]	= [];
			foreach( $rights as $right ){
				if( !isset( $this->rights[$roleId][$right->controller] ) )
					$this->rights[$roleId][$right->controller]	= [];
				$this->rights[$roleId][$right->controller][]	= $right->action;
			}
		}
		return $this->rights[$roleId];
	}

	/**
	 *	Return list controller actions or matrix of controllers and actions of role.
	 *	@abstract
	 *	@public
	 *	@param		string|NULL		$controller		Controller to list actions for, otherwise return matrix
	 *	@param		string|NULL		$roleId			Specified role, otherwise current role
	 *	@return		array							List of actions or matrix of controllers and actions
	 */
	public function index( string $controller = NULL, ?string $roleId = NULL ): array
	{
		throw new Exception( 'Not implemented yet' );
	}

	/**
	 *	Allows access to a controller action for a role.
	 *	@access		public
	 *	@param		string		$roleId			Role ID
	 *	@param		string		$controller		Name of Controller
	 *	@param		string		$action			Name of Action
	 *	@return		integer
	 */
	public function setRight( string $roleId, string $controller, string $action ): int
	{
		if( $this->hasFullAccess( $roleId ) )
			return -1;
		if( $this->hasNoAccess( $roleId ) )
			return -2;
		$data	= ['controller' => $controller, 'action' => $action];
		return $this->server->postData( 'role', 'setRight', [$roleId], $data );
	}

	//  --  PROTECTED  --  //

	/**
	 *	Returns Role.
	 *	@access		protected
	 *	@param		string		$roleId			Role ID
	 *	@return		array|object
	 */
	protected function getRole( string $roleId ): object|array
	{
		if( !$this->roles )
			foreach( $this->server->getData( 'role', 'index' ) as $role )
				$this->roles[$role->roleId]	= $role;
		return $this->roles[$roleId];
	}
}
