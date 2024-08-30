<?php

use CeusMedia\Common\FS\File\JSON\Reader as JsonFileReader;
use CeusMedia\Common\FS\File\JSON\Writer as JsonFileWriter;
use CeusMedia\HydrogenFramework\Environment;

class Model_Config_Page
{
	protected Environment $env;
	protected string $filePath;
	protected object|array $fileData;
	protected array $pages;
	protected array $scopes;
	protected array $baseItem	= [
		'parentId'		=> 0,
		'status'		=> 0,
		'type'			=> 0,
		'controller'	=> '',
		'action'		=> '',
		'access'		=> 'acl',
		'content'		=> '',
		'keywords'		=> '',
		'changefreq'	=> '',
		'priority'		=> '',
		'icon'			=> '',
		'format'		=> 'HTML',
		'template'		=> '',
		'createdAt'		=> 0,
		'modifiedAt'	=> 0,
	];
	protected array $types	= [
		0	=> 'page',
		1	=> 'menu',
		2	=> 'module'
	];

	public function __construct( Environment $env )
	{
		$this->env		= $env;
		$this->filePath	= $env->uri.'config/pages.json';
		$this->loadPages();
	}

	/**
	 *	@param		array		$data
	 *	@return		int
	 */
	public function add( array $data ): int
	{
		$pageId		= max( [0] + array_keys( $this->pages ) ) + 1;
		$page		= $this->transformInputToPage( $pageId, $data );
		$this->pages[$pageId]	= $page;
		$this->savePages();
		return $pageId;
	}

	/**
	 *	@param		int|string		$pageId
	 *	@param		array			$data
	 *	@return		bool
	 */
	public function edit( int|string $pageId, array $data = [] ): bool
	{
		$changes	= [];
		foreach( $data as $key => $value )
			if( $this->pages[$pageId]->$key	!= $value )
				$changes[$key]	= $value;
		if( 0 === count( $changes ) )
			return TRUE;
		$this->pages[$pageId]	= (object) array_merge( (array) $this->pages[$pageId], $changes );
		return $this->savePages();
	}

	/**
	 *	@param		int|string		$pageId
	 *	@return		object|NULL
	 */
	public function get( int|string $pageId ): ?object
	{
		$pageId	= (int) $pageId;
		foreach( $this->pages as $page )
			if( $page->pageId === $pageId )
				return $page;
		return NULL;
	}

	public function getAll(): array
	{
		return $this->pages;
	}

	public function getColumns(): array
	{
		return [
			'pageId',
			'parentId',
			'type',
			'scope',
			'status',
			'rank',
			'identifier',
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
	 *	@param		array		$indices
	 *	@param		array		$orders
	 *	@return		object|FALSE
	 */
	public function getByIndices( array $indices = [], array $orders = [] ): object|FALSE
	{
		return current( $this->getAllByIndices( $indices, $orders, [0, 1] ) );
	}

	public function getAllByIndices( array $indices = [], array $orders = [], array $limits = [] ): array
	{
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
		return $data;
	}

	public function remove( int|string $pageId )
	{
		throw new RuntimeException( 'Not implemented yet' );
	}

	//  --  PROTECTED  --  //

	protected function loadPages(): void
	{
		$this->fileData	= JsonFileReader::load( $this->filePath, TRUE );
		$this->scopes	= array_keys( (array) $this->fileData );
		$this->pages	= [];
		$pageId			= 0;
		foreach( $this->scopes as $scopeNr => $scope ){
			foreach( $this->fileData[$scope] as $pageNr => $page ){
				$pageId++;
				$page['type']	??= reset( $this->types );
				$type	= (int) array_search( $page['type'], $this->types );
				if( !empty( $page['pages'] ) )
					$type	= 1;
				else if( !empty( $page['controller'] ) )
					$type	= 2;

				$pageItem	= (object) array_merge( $this->baseItem, array(
					'pageId'		=> $pageId,
					'parentId'		=> 0,
					'status'		=> 1,			//@todo realize
					'type'			=> $type,
					'controller'	=> !empty( $page['controller'] ) ? $page['controller'] : '',
					'action'		=> !empty( $page['action'] ) ? $page['action'] : '',
					'scope'			=> $scopeNr,
					'rank'			=> $pageNr + 1,
					'identifier'	=> $page['path'],
					'fullpath'		=> $page['path'],
					'title'			=> $page['label'],
					'access'		=> !empty( $page['access'] ) ? $page['access'] : '',
					'description'	=> !empty( $page['desc'] ) ? $page['desc'] : '',
					'icon'			=> !empty( $page['icon'] ) ? $page['icon'] : '',
					'template'		=> !empty( $page['template'] ) ? $page['template'] : '',
				) );

				$this->pages[$pageItem->pageId]	= $pageItem;
				$this->fileData[$scope][$pageNr]['pageId']	= $pageItem->pageId;
				if( !empty( $page['pages'] ) ){
					foreach( $page['pages'] as $subpageNr => $subpage ){
						$pageId++;
						$subpage['type']	??= reset( $this->types );
						$type	= (int) array_search( $subpage['type'], $this->types );
						if( !empty( $subpage['pages'] ) )
							$type	= 1;
						else if( !empty( $subpage['controller'] ) )
							$type	= 2;

						$subpageItem	= (object) array_merge( $this->baseItem, array(
							'pageId'		=> $pageId,
							'parentId'		=> $pageItem->pageId,
							'status'		=> 1,			//@todo realize
							'type'			=> $type,
							'controller'	=> !empty( $subpage['controller'] ) ? $subpage['controller'] : '',
							'action'		=> !empty( $subpage['action'] ) ? $subpage['action'] : '',
							'scope'			=> $scopeNr,
							'rank'			=> $subpageNr + 1,
							'identifier'	=> $subpage['path'],
							'fullpath'		=> $page['path'].'/'.$subpage['path'],
							'title'			=> $subpage['label'],
							'access'		=> !empty( $subpage['access'] ) ? $subpage['access'] : '',
							'description'	=> !empty( $subpage['desc'] ) ? $subpage['desc'] : '',
							'icon'			=> !empty( $subpage['icon'] ) ? $subpage['icon'] : '',
							'template'		=> !empty( $subpage['template'] ) ? $subpage['template'] : '',
						) );
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


	protected function transformInputToPage( int $pageId, array $data ): object
	{
		$page	= (object) [
			'pageId'		=> $pageId,
			'parentId'		=> $data['parentId'],
			'status'		=> $data['status'] ?? 0,
			'type'			=> (int) $data['type'] ?? 0,
			'controller'	=> $data['controller'] ?? '',
			'action'		=> $data['action'] ?? '',
			'scope'			=> $data['scope'],
			'rank'			=> $data['rank'] ?? 0,
			'identifier'	=> $data['identifier'],
			'title'			=> $data['title'],
//			'access'		=> $data['access'] ?? '',
//			'description'	=> $data['description'] ?? '',
			'icon'			=> $data['icon'] ?? '',
			'template'		=> $data['template'] ?? '',
		];
		$parentId	= (int) $data['parentId'] ?? 0;
		if( 0 != $parentId )
			$page->identifier	= $this->pages[$parentId]->identifier.'/'.$page->identifier;
		return $page;
	}

	protected function transformPageToJsonItem( object $page ): array
	{
		$item	= [
			'path'	=> $page->identifier,
			'label'	=> $page->title,
		];
		switch( $page->type ){
			case Model_Page::TYPE_BRANCH:
				$item['pages']	= [];
				break;
			case Model_Page::TYPE_MODULE:
				$item['controller']	= $page->controller;
				$item['action']		= $page->action;
				break;
		}

		if( isset( $page->template ) && 'default' === $page->template )
			unset( $page->template );

		$optionals	= ['desc', 'icon', 'access', 'template', 'rank'];
		foreach( $optionals as $option )
			if( '' !== ( $page->$option ?? '' ) )
				$item[$option]	= $page->$option;
		return $item;
	}

	protected function transformPagesToJsonTree(): array
	{
		$tree	= [];
		foreach( $this->scopes as $scopeNr => $scope ){
			$tree[$scope]	= [];
			$pages1	= $this->getAllByIndices( ['scope' => $scopeNr, 'parentId' => 0] );
			foreach( $pages1 as $page1 ){
				$data1	= $this->transformPageToJsonItem( $page1 );
				if( Model_Page::TYPE_BRANCH === $page1->type ){
					$pages2	= $this->getAllByIndices( ['scope' => $scopeNr, 'parentId' => $page1->pageId] );
					foreach( $pages2 as $page2 )
						$data1['pages']	= $this->transformPageToJsonItem( $page2 );
				}
				$tree[$scope][]	= $data1;
			}
		}
		return $tree;
	}
}
