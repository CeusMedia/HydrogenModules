<?php

use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\HydrogenFramework\Controller\Ajax as AjaxController;

class Controller_Manage_My_Provision_License extends AjaxController
{
	/**
	 *	@return		int
	 *	@throws		JsonException
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function getUsers(): int
	{
		$logicAuth			= Logic_Authentication::getInstance( $this->env );
		if( !$logicAuth->isAuthenticated() )
			$this->respondError( 400, 'Invalid login status' );

		$logicProvision		= Logic_User_Provision::getInstance( $this->env );
		$logicMember		= Logic_Member::getInstance( $this->env );

		$query		= $this->env->getRequest()->get( 'query', '' );
		$list		= [];
		if( '' !== trim( $query ) ){
			$userIds	= $logicMember->getUserIdsByQuery( trim( $query ) );
//			$userIds	= array_merge( $userIds, $userIds, $userIds, $userIds, $userIds );
			$userIds	= array_slice( $userIds, 0, 10 );
			foreach( $userIds as $userId ){
				$helper		= new View_Helper_Member( $this->env );
				$helper->setMode( "large" );
				$helper->setUser( $userId );
				$user	= $logicProvision->getUser( $userId );
				$list[]	= (object) [
					'user'	=> $user,
					'html'	=> $helper->render(),
					'image'	=> $helper->renderImage(),
				];
			}
		}
		return $this->respondData( [
			'query'		=> $query,
			'count'		=> count( $list ),
			'list'		=> $list
		] );
	}
}
