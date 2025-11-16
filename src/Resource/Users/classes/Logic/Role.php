<?php

use CeusMedia\HydrogenFramework\Logic\Shared as SharedLogic;

class Logic_Role extends SharedLogic
{
	protected Model_Role $model;

	public function __onInit(): void
	{
		$this->model	= Model_Role::getInstance( $this->env );
	}

	public function add( string $title, string $description, int $access, int $register ): int
	{
		return $this->model->add( Entity_Role::fromArray( [
			'title'			=> $title,
			'description'	=> $description,
			'access'		=> $access,
			'register'		=> $register,
			'createdAt'		=> time(),
			'modifiedAt'	=> time(),
		] ) );
	}

	public function get( int|string $roleId ): ?Entity_Role
	{
		/** @var ?Entity_Role $role */
		$role	= $this->model->get( $roleId );
		return $role;
	}

	/**
	 *	@param		array		$conditions
	 *	@param		array		$orders
	 *	@param		array		$limits
	 *	@return		Entity_Role[]
	 */
	public function getAll( array $conditions = [], array $orders = [], array $limits = [] ): array
	{
		/** @var Entity_Role[] $roles */
		$roles	= $this->model->getAll( $conditions, $orders, $limits );
		return $roles;
	}

	public function hasByTitle( string $title ): bool
	{
		return $this->model->getByIndex( 'title', $title );
	}

	/**
	 *	@param		Entity_Role|int|string	$role
	 *	@return		bool
	 *	@throws		ReflectionException
	 */
	public function remove( Entity_Role|int|string $role ): bool
	{
		$roleId			= is_object( $role ) ? $role->roleId : $role;
		$nrRoleUsers	= count( Logic_User::getInstance( $this->env )->getRoleUsers( $role ) );
		if( 0 !== $nrRoleUsers )
			throw new RuntimeException( 'Role is used by '.$nrRoleUsers.' users' );

		Logic_RoleRight::getInstance( $this->env )->removeAll( $roleId );
		return $this->model->remove( $roleId );
	}
}