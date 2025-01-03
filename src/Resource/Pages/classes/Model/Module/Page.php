<?php

use CeusMedia\HydrogenFramework\Environment;

class Model_Module_Page
{
	protected Environment $env;
//	protected $acl;
	protected bool $useAcl;

	protected array $scopes		= [
		0	=> 'main',
//		1	=> 'header',
//		2	=> 'footer',
	];

	protected array $types		= [
		0	=> 'page',
		1	=> 'menu',
		2	=> 'module',
		3	=> 'component',
//		4	=> 'redirect',
	];

	/** @var array<Entity_Page> $pages */
	protected array $pages;

	public function __construct( Environment $env )
	{
		$this->env		= $env;
		$this->useAcl	= $this->env->getModules()->has( 'Resource_Users' );
//		$this->acl		= $this->env->getAcl();
		$this->loadPages();
	}

	public function edit( int|string $pageId, array $data = [] )
	{
		throw new RuntimeException( 'Not implemented yet' );
	}

	/**
	 *	@param		int|string		$pageId
	 *	@return		?Entity_Page
	 */
	public function get( int|string $pageId ): ?Entity_Page
	{
		foreach( $this->pages as $page )
			if( (string) $page->pageId === $pageId )
				return $page;
		return NULL;
	}

	/**
	 *	@return Entity_Page[]
	 */
	public function getAll(): array
	{
		return $this->pages;
	}

	/**
	 *	@param		array		$indices
	 *	@param		array		$orders
	 *	@return		?Entity_Page
	 */
	public function getByIndices( array $indices = [], array $orders = [] ): ?Entity_Page
	{
		return current( $this->getAllByIndices( $indices, $orders, [0, 1] ) );
	}

	/**
	 *	@param		array		$indices
	 *	@param		array		$orders
	 *	@param		array		$limits
	 *	@return		Entity_Page[]
	 */
	public function getAllByIndices( array $indices = [], array $orders = [], array $limits = [] ): array
	{
		$indices['title']	= '!= ""';
		if( !isset( $indices['scope'] ) )
			$indices['scope']	= 0;

		$data	= $this->pages;
		$regExp	= '/^(!=|>=|<=|>|<) (.+)$/';
		foreach( $indices as $indexKey => $indexValue ){
			foreach( $data as $nr => $page ){
				$pageValue	= $page->$indexKey;
				$matches	= [];
				if( is_array( $indexValue ) ){
					if( !in_array( $pageValue, $indexValue ) )
						unset( $data[$nr] );
				}
				else if( preg_match( $regExp, $indexValue, $matches ) ){
					if( $matches[1] === '!=' && $pageValue === trim( (string) $matches[2], '"\'' ) ||
						$matches[1] === '>=' && (float) $pageValue < (float) $matches[2] ||
						$matches[1] === '<=' && (float) $pageValue > (float) $matches[2] ||
						$matches[1] === '>' && (float) $pageValue <= (float) $matches[2] ||
						$matches[1] === '<' && (float) $pageValue >= (float) $matches[2] )
							unset( $data[$nr] );
				}
				else if( $pageValue != $indexValue )
					unset( $data[$nr] );
			}
		}
		if( 2 === count( $limits ) )
			$data	= array_slice( $data, $limits[0], $limits[1] );
		return array_values( $data );
	}

	//  --  PROTECTED  --  //

	/**
	 *	@return		void
	 */
	protected function loadPages(): void
	{
		$pageId		= 0;
		$pages		= [];
		foreach( array_keys( $this->scopes ) as $scopeNr => $scope ){
			foreach( $this->env->getModules()->getAll() as $module ){
				foreach( $module->links as $link ){
					$pageId++;
					if( !isset( $link->scope ) )
						$link->scope	= 'main';
					$linkScope	= array_search( strtolower( $link->scope ), $this->scopes );
					$link->scope	= $linkScope >= 0 ? $linkScope : 0;

//					$pathParts	= explode( '/', $link->path );
//					$action		= array_pop( $pathParts );
//					$controller	= implode( '_', $pathParts );

					$rank		= strlen( $link->rank ) ? $link->rank : 50;
					$rank		= (int) preg_replace( '/^0/', '', vsprintf( '%s%s%s', [
						str_pad( $scopeNr, 2, '0', STR_PAD_LEFT ),
						str_pad( $rank, 3, '0', STR_PAD_LEFT ),
						str_pad( $pageId, 3, '0', STR_PAD_LEFT ),
					] ) );

					$controller	= str_replace( '/', '_', ucwords( $link->path, '/' ) );

					$item	= Entity_Page::fromArray( [
						'pageId'		=> $pageId,
						'moduleId'		=> $module->id,
						'type'			=> (int) array_search( 'module', $this->types ),
						'scope'			=> $link->scope,
						'status'		=> Model_Page::STATUS_VISIBLE,
						'access'		=> $link->access,
						'identifier'	=> $link->path,
						'fullpath'		=> $link->path,
						'controller'	=> $controller,
						'action'		=> '',//$action,
						'path'			=> $link->path,
						'link'			=> !empty( $link->link ) ? $link->link : $link->path,
						'icon'			=> !empty( $link->icon ) ? $link->icon : NULL,
						'title'			=> $link->label,
						'language'		=> $link->language,
						'rank'			=> $link->rank,
						'active'		=> FALSE,
					] );
					$pages[$rank]	= $item;
				}
			}
			ksort( $pages );
		}
		$this->pages	= $pages;
	}
}
