<?php
/**
 *	Server Action Dispatcher Class.
 *
 *	Copyright (c) 2010-2013 Christian Würker (ceus-media.de)
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
 *	@category		cmApps
 *	@package		Chat.Server.Resource
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2010-2024 Ceus Media (https://ceusmedia.de/)
 *	@since			0.1
 */

use CeusMedia\Common\Alg\Obj\Factory as ObjectFactory;
use CeusMedia\Common\Alg\Obj\MethodFactory as ObjectMethodFactory;
use CeusMedia\HydrogenFramework\Controller as Controller;
use CeusMedia\HydrogenFramework\Dispatcher\General as GeneralDispatcher;

/**
 *	Server Action Dispatcher Class.
 *	@category		cmApps
 *	@package		Chat.Server.Resource
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2010-2024 Ceus Media (https://ceusmedia.de/)
 */
class Dispatcher extends GeneralDispatcher
{

	public bool $checkClassActionArguments	= TRUE;

	/**
	 *	Checks whether token authentication is needed or invalid.
	 *	An exception will be thrown if token authentication is active and:
	 *	- path is not token-free
	 *	- no token given
	 *	- given token is invalid
	 *	Nothing will happen if token authentication is not active or:
	 *	- path is token-free
	 *	- a token is given and it is valid
	 *	@access		protected
	 *	@param		string		$controller		Controller class name
	 *	@param		string		$action			Action method name
	 *	@return		void
	 *	@throws		RuntimeException if authentication is active and not fulfilled
	 */
	protected function checkAuth( string $controller, string $action )
	{
		$config	= $this->env->getConfig()->getAll( 'module.server_json.', TRUE );					//  shortcut module config
		if( !$config->get( 'token.active' ) )														//  token is not needed for authentication
			return;
		$excludes	= preg_split( '/, */', $config->get( 'token.excludes' ) );							//  extract paths accessible without token

		if( !in_array( $controller.'/'.$action, $excludes ) ){										//  not a token-free resource
			$token	= $this->request->get( 'token' );												//  extract sent token
			if( !trim( $token ) )																	//  no token given
				throw new RuntimeException( 'Access denied: missing token', 220 );					//  break with internal error
			$store	= Resource_TokenStore::getInstance( $this->env );								//  get instance of token storage resource
			if( !$store->validateToken( $token ) )													//  token is invalid
				throw new RuntimeException( 'Access denied: invalid token', 220 );					//  break with internal error
		}
	}

	/**
	 *	Calls controller method depending on request and returns result.
	 *	Notifies Piwik tracker if enabled.
	 *	@access		public
	 *	@return		mixed		Result returned by called controller method
	 *	@throws		ReflectionException
	 */
	public function dispatch(): string
	{
		$this->realizeCall();																		//  set defaults if necessary

		$controller	= trim( $this->request->get( '__controller' ) );								//  get called controller
		$action		= trim( $this->request->get( '__action' ) );									//  get called action
		$arguments	= $this->request->get( '__arguments' );											//  get given arguments

		if( $this->env->getModules()->has( 'Resource_Tracker_Piwik' ) )								//  Piwik tracker is installed
			if( $this->env->getConfig()->get( 'module.resource_tracker_piwik.tracker.enabled' ) )	//  a tracker is enabled
				$this->env->get( 'piwik' )->doTrackPageView( $controller.' > '.$action );			//  track request

		$this->checkAuth( $controller, $action );													//  ensure authentication
		$className	= self::getControllerClassFromPath( $controller );								// get controller class name from requested controller path
		$this->checkClass( $className );															//  ensure controller class

		$factory	= new ObjectFactory();															//  raise object factory
		/** @var Controller $instance */
		$instance	= $factory->createObject( $className, [&$this->env] );							//  build controller instance
		$this->checkClassAction( $className, $instance, $action );									//  ensure action method
		if( $this->checkClassActionArguments )														//  action method arguments are to be checked
			$this->checkClassActionArguments( $className, $instance, $action );						//  ensure action method arguments

		$data	= ObjectMethodFactory::staticCallObjectMethod( $instance, $action, $arguments );	//  call action method in controller class with arguments
		$this->noteLastCall( $instance );															//  store this call to avoid loops
		return $data;																				//  return result of controller method
	}
}
