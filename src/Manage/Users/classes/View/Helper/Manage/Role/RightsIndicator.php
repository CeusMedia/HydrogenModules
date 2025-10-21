<?php
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use CeusMedia\Common\UI\HTML\Indicator;
class View_Helper_Manage_Role_RightsIndicator
{
	/** @var WebEnvironment $env */
	protected WebEnvironment $env;
	protected int $max				= 0;
	protected int|string $roleId	= 0;
	protected array $rights			= [];

	public function __construct( WebEnvironment $env ){
		$this->env	= $env;
	}

	public function setRoles( array $roles ): self
	{
		$max	= 0;
		$this->rights	= [];

		/** @var Entity_Role $role */
		foreach( $roles as $role ){
			$rights	= $this->env->getAcl()->index( NULL, $role->roleId );
			$count	= array_sum( array_map( 'count', $rights ) );
			$this->rights[$role->roleId]	= $count;
			$max	= max( $max, $count );
		}
		$this->max	= $max;
		return $this;
	}

	public function setRoleId( int|string $roleId ): self
	{
		$this->roleId	= $roleId;
		return $this;
	}

	public function render(): string
	{
		return Indicator::render( $this->rights[$this->roleId], $this->max );
	}
}
