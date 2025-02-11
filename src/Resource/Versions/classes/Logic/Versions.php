<?php

use CeusMedia\HydrogenFramework\Environment;
class Logic_Versions
{
	protected static self $instance;

	protected Environment $env;
	protected Model_Version $model;
	protected int|string|NULL $userId			= NULL;

	/**
	 *	@param		Environment		$env
	 *	@throws		ReflectionException
	 */
	protected function __construct( Environment $env )
	{
		$this->env		= $env;
		$this->model	= new Model_Version( $env );
		$this->detectUserId();
	}

	protected function __clone()
	{
	}

	/**
	 *	@param		string				$module
	 *	@param		string				$id
	 *	@param		string				$content
	 *	@param		int|string|NULL		$authorId
	 *	@return		string
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function add( string $module, string $id, string $content, int|string|NULL $authorId = NULL ): string
	{
		$data		= [
			'userId'	=> $authorId ?: $this->userId,
			'module'	=> $module,
			'id'		=> $id,
			'version'	=> $this->getNextVersionNr( $module, $id ),
			'timestamp'	=> time(),
		];
		$versionId	= $this->model->add( $data );
		$data		= ['content' => $content];
		$this->model->edit( $versionId, $data, FALSE );
		return $versionId;
	}

	/**
	 *	@return		bool
	 *	@throws		ReflectionException
	 */
	public function detectUserId(): bool
	{
		if( $this->env->getModules()->has( 'Resource_Authentication' ) ){
			$logic			= Logic_Authentication::getInstance( $this->env );
			$this->userId	= $logic->getCurrentUserId();
			return TRUE;
		}
		return FALSE;
	}

	/**
	 *	@param		string			$module
	 *	@param		string			$id
	 *	@param		string|NULL		$version
	 *	@return		object|NULL
	 */
	public function get( string $module, string $id, ?string $version = NULL ): ?object
	{
		if( !is_null( $version ) ){
			$conditions = [
				'module'	=> $module,
				'id'		=> $id,
				'version'	=> $version,
			];
			return $this->model->getByIndices( $conditions );
		}
		$conditions	= [
			'module'	=> $module,
			'id'		=> $id,
		];
		return $this->model->getByIndices( $conditions, ['version' => 'DESC'] );
	}

	/**
	 *	@param		string		$module
	 *	@param		string		$id
	 *	@param		array		$conditions
	 *	@param		array		$orders
	 *	@param		array		$limits
	 *	@return		array
	 */
	public function getAll( string $module, string $id, array $conditions = [], array $orders = [], array $limits = [] ): array
	{
		$indices	= [
			'module'	=> $module,
			'id'		=> $id,
		];
		if( $orders )
			$orders	= ['version' => 'ASC'];
		$conditions	= array_merge( $conditions, $indices );
		return $this->model->getAll( $conditions, $orders, $limits );
	}

	/**
	 *	@param		int|string		$versionId
	 *	@return		object|NULL
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function getById( int|string $versionId ): ?object
	{
		return $this->model->get( $versionId );
	}

	/**
	 *	@param		Environment		$env
	 *	@return		self
	 *	@throws		ReflectionException
	 */
	public static function getInstance( Environment $env ): self
	{
		if( !self::$instance )
			self::$instance	= new self( $env );
		return self::$instance;
	}

	/**
	 *	@param		string		$module
	 *	@param		string		$id
	 *	@return		int
	 */
	protected function getNextVersionNr( string $module, string $id ): int
	{
		$latest		= $this->model->getByIndices( [
			'module'	=> $module,
			'id'		=> $id,
		], ['version' => 'DESC'] );
		if( $latest )
			return (int) $latest->version + 1;
		return 0;
	}

	/**
	 *	@param		string		$module
	 *	@param		string		$id
	 *	@param		?string		$version
	 *	@return		bool
	 */
	public function has( string $module, string $id, ?string $version = NULL ): bool
	{
		if( !is_null( $version ) )
			return (bool) $this->get( $module, $id, $version );
		return 0 !== count( $this->getAll( $module, $id ) );
	}

	/**
	 *	@param		int|string		$versionId
	 *	@return		bool
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function hasById( int|string $versionId ): bool
	{
		return (bool) $this->getById( $versionId );
	}

	/**
	 *	@param		string		$module
	 *	@param		string		$id
	 *	@param		string		$version
	 *	@return		bool
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function remove( string $module, string $id, string $version ): bool
	{
		$entry	= $this->get( $module, $id, $version );
		if( !$entry )
			return FALSE;
		return $this->model->remove( $entry->versionId );
	}

/*	public function set( $versionId, $content, $data ){

	}*/
}
