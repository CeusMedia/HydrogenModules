<?php

use CeusMedia\Common\FS\File\JSON\Reader as JsonFileReader;
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

	public function edit( $pageId, $data = [] )
	{
		throw new RuntimeException( 'Not implemented yet' );
	}

	public function get( string $pageId ): ?object
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
		return $columns	= [
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

	public function getByIndices( array $indices = [], array $orders = [] )
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
		if( count( $limits ) === 2 )
			$data	= array_slice( $data, $limits[0], $limits[1] );
		return $data;
	}

	public function remove()
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
}
