<?php

use CeusMedia\Common\FS\File\JSON\Reader as JsonFileReader;
use CeusMedia\Common\FS\File\JSON\Writer as JsonFileWriter;
use CeusMedia\HydrogenFramework\Environment;

class Model_Config_Page
{
	protected Environment $env;

	protected string $filePath;
	protected array $fileData;

	/** @var array<Entity_Page> $pages */
	protected array $pages;

	protected array $scopes;

	protected array $types	= [
		0	=> 'page',
		1	=> 'menu',
		2	=> 'module',
		3	=> 'component',
//		4	=> 'redirect',
	];

	/**
	 *	@param		Entity_Page[]	$pages
	 *	@param		array			$indices
	 *	@return		Entity_Page[]
	 */
	public static function filterPagesByIndices( array $pages, array $indices ): array
	{
		$regExp	= '/^(!=|>=|<=|>|<) (.+)$/';
		foreach( $indices as $indexKey => $indexValue ){
			foreach( $pages as $nr => $page ){
				$pageValue	= $page->$indexKey;
				$matches	= [];
				if( is_array( $indexValue ) ){
					if( !in_array( $pageValue, $indexValue ) )
						unset( $pages[$nr] );
				}
				else if( preg_match( $regExp, $indexValue, $matches ) ){
					if( !match( $matches[1] ){
						'!='	=> (string) $pageValue !== trim( (string) $matches[2], '"\'' ),
						'>='	=> (float) $pageValue >= (float) $matches[2],
						'<='	=> (float) $pageValue <= (float) $matches[2],
						'>'		=> (float) $pageValue > (float) $matches[2],
						'<'		=> (float) $pageValue < (float) $matches[2],
					} )
						unset( $pages[$nr] );
				}
				else if( $pageValue != $indexValue )
					unset( $pages[$nr] );
			}
		}
		return $pages;
	}

	public function __construct( Environment $env )
	{
		$this->env		= $env;
		$this->filePath	= $env->uri.'config/pages.json';
		$this->loadPages();
	}

	/**
	 *	@param		Entity_Page|array		$data
	 *	@return		int
	 */
	public function add( Entity_Page|array $data ): int
	{
		$pageId		= max( [0] + array_keys( $this->pages ) ) + 1;

		if( is_array( $data ) ){
			$data['pageId']		= $pageId;
			$data['parentId']	= (int) $data['parentId'] ?? 0;
			$data['type']		= (int) $data['type'] ?? 0;
			$data['status']		= (int) $data['status'] ?? 0;
			$data['rank']		= (int) $data['rank'] ?? 0;
			$parentId	= $data['parentId'];
			if( 0 !== $parentId && isset( $this->pages[$parentId] ) )
				$data['identifier']	= $this->pages[$parentId]->identifier.'/'.$data['identifier'];
			$entity = Entity_Page::fromArray( $data );
		}
		else
			$entity = Entity_Page::mergeWithArray( $data, ['pageId'	=> $pageId] );

		$this->pages[$pageId]	= $entity;
		$this->savePages();
		return $pageId;
	}

	public function getColumns(): array
	{
		return [
			'pageId',
			'parentId',
//			'moduleId',
			'type',
			'scope',
			'status',
			'rank',
			'identifier',
			'fullpath',
			'controller',
			'action',
			'access',
			'title',
			'content',
			'format',
			'description',
			'keywords',
			'changefreq',
			'priority',
			'icon',
			'template',
			'createdAt',
			'modifiedAt'
		];
	}

	/**
	 *	@param		int|string		$pageId
	 *	@param		array			$data
	 *	@param		bool			$stripTags		Flag: strip HTML tags from values, does nothing in this implementation, exists for compatibility with Model_Page
	 *	@return		bool
	 */
	public function edit( int|string $pageId, array $data = [], bool $stripTags = FALSE ): bool
	{
		$pageId		= (int) $pageId;
		$changes	= array_filter( $data, function ( $value, $key ) use ( $pageId ){
			return $this->pages[$pageId]->$key != $value;
		}, ARRAY_FILTER_USE_BOTH );
		if( [] === $changes )
			return TRUE;
		$this->pages[$pageId]	= Entity_Page::mergeWithArray( $this->pages[$pageId], $changes );
		return $this->savePages();
	}

	/**
	 *	@param		int|string		$pageId
	 *	@return		Entity_Page|NULL
	 */
	public function get( int|string $pageId ): ?Entity_Page
	{
		$pageId	= (int) $pageId;
		foreach( $this->pages as $page )
			if( $page->pageId === $pageId )
				return $page;
		return NULL;
	}

	/**
	 *	@return		Entity_Page[]
	 */
	public function getAll(): array
	{
		return $this->pages;
	}

	/**
	 *	@param		array		$indices
	 *	@param		array		$orders
	 *	@return		Entity_Page|FALSE
	 */
	public function getByIndices( array $indices = [], array $orders = [] ): Entity_Page|FALSE
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
		$data	= $this->pages;
		if( [] !== $indices )
			$data	= Model_Config_Page::filterPagesByIndices( $data, $indices );
//		if( [] !== $orders )
//			$data	= Model_Config_Page::orderPages( $data, $orders );
		if( 2 === count( $limits ) )
			$data	= array_slice( $data, $limits[0], $limits[1] );
		return $data;
	}

	public function remove( int|string $pageId )
	{
		throw new RuntimeException( 'Not implemented yet' );
	}

	//  --  PROTECTED  --  //

	protected function getTypeFromInput( array $input ): int
	{
		$input['type']	??= reset( $this->types );
		$type	= (int) array_search( $input['type'], $this->types );
		if( !empty( $input['pages'] ) )
			return Model_Page::TYPE_BRANCH;
		else if( !empty( $input['controller'] ) )
			return Model_Page::TYPE_MODULE;
		return $type;
	}

	protected function loadPages(): void
	{
		$this->fileData	= JsonFileReader::load( $this->filePath, TRUE );
		$this->scopes	= array_keys( (array) $this->fileData );
		$this->pages	= [];
		$pageId			= 0;
		foreach( $this->scopes as $scopeNr => $scope ){
			foreach( $this->fileData[$scope] as $pageNr => $page ){
				$pageId++;
				$pageItem	= Entity_Page::fromArray( [
					'pageId'		=> $pageId,
					'type'			=> $this->getTypeFromInput( $page ),
					'status'		=> Model_Page::STATUS_VISIBLE,			//@todo realize
					'scope'			=> $scopeNr,
					'rank'			=> $pageNr + 1,
					'identifier'	=> $page['path'],
					'fullpath'		=> $page['path'],
					'title'			=> $page['label'],
					'controller'	=> $page['controller'] ?? NULL,
					'action'		=> $page['action'] ?? NULL,
					'access'		=> $page['access'] ?? NULL,
					'description'	=> $page['desc'] ?? NULL,
					'icon'			=> $page['icon'] ?? NULL,
					'template'		=> $page['template'] ?? NULL,
				] );

				$this->pages[$pageItem->pageId]	= $pageItem;
				$this->fileData[$scope][$pageNr]['pageId']	= $pageItem->pageId;
				if( !empty( $page['pages'] ) ){
					foreach( $page['pages'] as $subpageNr => $subpage ){
						$pageId++;
						$subpageItem	= Entity_Page::fromArray( [
							'pageId'		=> $pageId,
							'parentId'		=> $pageItem->pageId,
							'type'			=> $this->getTypeFromInput( $subpage ),
							'status'		=> Model_Page::STATUS_VISIBLE,			//@todo realize
							'scope'			=> $scopeNr,
							'rank'			=> $subpageNr + 1,
							'identifier'	=> $subpage['path'],
							'fullpath'		=> $page['path'].'/'.$subpage['path'],
							'title'			=> $subpage['label'],
							'controller'	=> $subpage['controller'] ?? NULL,
							'action'		=> $subpage['action'] ?? NULL,
							'access'		=> $subpage['access'] ?? NULL,
							'description'	=> $subpage['desc'] ?? NULL,
							'icon'			=> $subpage['icon'] ?? NULL,
							'template'		=> $subpage['template'] ?? NULL,
						] );

						$this->pages[$subpageItem->pageId]	= $subpageItem;
						$this->fileData[$scope][$pageNr]['pageId']	= $pageItem->pageId;
					}
				}
			}
		}
	}

	protected function savePages(): bool
	{
		return (bool) JsonFileWriter::save( $this->filePath, $this->transformPagesToJsonTree(), TRUE );
	}

	protected function transformPageToJsonItem( Entity_Page $page ): array
	{
		$item	= [
			'path'	=> $page->identifier,
			'label'	=> $page->title,
		];
		if( Model_Page::TYPE_MODULE === $page->type ){
			$item['controller']	= $page->controller;
			$item['action']		= $page->action;
		}

		if( isset( $page->template ) && 'default' === $page->template )
			unset( $page->template );

		$optionals	= ['desc', 'icon', 'access', 'template'/*, 'rank'*/];
		foreach( $optionals as $option )
			if( '' !== ( $page->$option ?? '' ) )
				$item[$option]	= $page->$option;

		if( Model_Page::TYPE_BRANCH === $page->type ){	//  add subpages (at the end for better readable json structure)
			$item['pages']	= [];
		}
		return $item;
	}

	protected function transformPagesToJsonTree(): array
	{
		$tree	= [];
		foreach( $this->scopes as $scopeNr => $scope ){
			$tree[$scope]	= [];
			$pages1	= $this->getAllByIndices( ['scope' => $scopeNr, 'parentId' => 0] );
			foreach( $pages1 as $page1Nr => $page1 ){
				$data1	= $this->transformPageToJsonItem( $page1 );
				if( Model_Page::TYPE_BRANCH === $page1->type ){
					$pages2	= $this->getAllByIndices( ['scope' => $scopeNr, 'parentId' => $page1->pageId] );
					foreach( $pages2 as $page2Nr => $page2 ){
						$stamp	= ( $page1Nr * 100 - $page2Nr ) / 100000;
						$rank	= ($page2->rank ?: 0 ) + $stamp;
						$data1['pages'][(string) $rank]	= $this->transformPageToJsonItem( $page2 );
					}
					ksort( $data1['pages'] );
					$data1['pages']	= array_values( $data1['pages'] );
				}
				$stamp	= ( $page1Nr * 100 ) / 100000;
				$rank	= ($page1->rank ?: 0 ) + $stamp;
				$tree[$scope][(string) $rank]	= $data1;
			}
			ksort( $tree[$scope] );
			$tree[$scope]	= array_values( $tree[$scope] );
		}
		return $tree;
	}
}
