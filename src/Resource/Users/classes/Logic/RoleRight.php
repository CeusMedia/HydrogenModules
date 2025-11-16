<?php

use CeusMedia\HydrogenFramework\Logic\Shared as SharedLogic;

class Logic_RoleRight extends SharedLogic
{
	protected Model_Role_Right $model;

	/**
	 *	@param		Entity_Role|int|string		$role
	 *	@param		string			$controller
	 *	@param		string			$action
	 *	@return		int
	 */
	public function add( Entity_Role|int|string $role, string $controller, string $action ): int
	{
		$roleId	= is_object( $role ) ? $role->roleId : $role;
		$data	= [
			'roleId'		=> $roleId,
			'controller'	=> Model_Role_Right::minimizeController( $controller ),
			'action'		=> $action,
			'timestamp'		=> time()
		];
		return $this->model->add( $data );
	}

	/**
	 *	@param		Entity_Role|int|string		$role
	 *	@return		int
	 */
	public function count( Entity_Role|int|string $role ): int
	{
		$roleId	= is_object( $role ) ? $role->roleId : $role;
		return $this->model->countByIndex( 'roleId', $roleId );
	}

	/**
	 *	@param		Entity_Role|int|string		$role
	 *	@param		array						$orders
	 *	@return		array
	 */
	public function getRights( Entity_Role|int|string $role, array $orders = [] ): array
	{
		$orders	= [] === $orders ? ['controller' => 'ASC', 'action' => 'ASC'] : $orders;
		$roleId	= is_object( $role ) ? $role->roleId : $role;
		return $this->model->getAllByIndex( 'roleId', $roleId, $orders );
	}

	public function has( Entity_Role|int|string $role, string $controller, string $action ): bool
	{
		$roleId	= is_object( $role ) ? $role->roleId : $role;
		return $this->model->hasByIndices( [
			'roleId'		=> $roleId,
			'controller'	=> Model_Role_Right::minimizeController( $controller ),
			'action'		=> $action
		] );
	}

	/**
	 *	@return		array
	 */
	public function listControllers(): array
	{
		return $this->model->getDistinct( 'controller', [], ['controller' => 'ASC'] );
	}

	/**
	 *	@param		string		$controller
	 *	@return		array
	 */
	public function listControllerActions( string $controller ): array
	{
		return $this->model->getDistinct( 'action', ['controller' => $controller], ['action' => 'ASC'] );
	}

	/**
	 *	@param		Entity_Role|int|string		$role
	 *	@param		string			$controller
	 *	@param		string			$action
	 *	@return		int
	 */
	public function remove( Entity_Role|int|string $role, string $controller, string $action ): int
	{
		$roleId		= is_object( $role ) ? $role->roleId : $role;
		$indices	= [
			'roleId'		=> $roleId,
			'controller'	=> Model_Role_Right::minimizeController( $controller ),
			'action'		=> $action,
		];
		return $this->model->removeByIndices( $indices );
	}

	/**
	 *	Removes all rights of a role.
	 *	@param		Entity_Role|int|string	$role
	 *	@return		int						Number of removed rights
	 */
	public function removeAll( Entity_Role|int|string $role ): int
	{
		return $this->model->removeByIndex( 'roleId', $role );
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function __onInit(): void
	{
		$this->model	= new Model_Role_Right( $this->env );
	}
}

