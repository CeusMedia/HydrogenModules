<?php
class Model_Config_Page
{
	protected $env;
	protected $filePath;
	protected $pages;

	public function __construct( CMF_Hydrogen_Environment $env )
	{
		$this->env		= $env;
		$this->filePath	= $env->uri.'config/pages.json';
		$this->loadPages();
	}

	public function edit( $pageId, $data = array() )
	{
		throw new RuntimeException( 'Not implemented yet' );
	}

	public function get( $pageId )
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
		return $columns	= array(
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
		);
	}

	public function getByIndices( array $indices = array(), array $orders = array() )
	{
		return current( $this->getAllByIndices( $indices, $orders, array( 0, 1 ) ) );
	}

	public function getAllByIndices( array $indices = array(), array $orders = array(), array $limits = array() ): array
	{
		$data	= $this->pages;
		$regExp	= '/^(!=|>=|<=|>|<) (.+)$/';
		foreach( $indices as $indexKey => $indexValue ){
			foreach( $data as $nr => $page ){
				$pageValue	= $page->$indexKey;
				$matches	= array();
				if( is_array( $indexValue ) ){
					if( !in_array( $pageValue, $indexValue ) )
						unset( $data[$nr] );
				}
				else if( preg_match( $regExp, $indexValue, $matches ) ){
					if( $matches[1] === '!= ' && $pageValue !== (string) $matches[2] ||
						$matches[1] === '>= ' && (int) $pageValue >= (int) $matches[2] ||
						$matches[1] === '<= ' && (int) $pageValue <= (int) $matches[2] ||
						$matches[1] === '> ' && (int) $pageValue > (int) $matches[2] ||
						$matches[1] === '< ' && (int) $pageValue < (int) $matches[2] )
						unset( $data[$nr] );
				}
				else if( $pageValue != $indexValue )
					unset( $data[$nr] );
			}
		}
		return $data;
	}

	public function remove()
	{
		throw new RuntimeException( 'Not implemented yet' );
	}

	//  --  PROTECTED  --  //

	protected function loadPages()
	{
		$this->fileData	= FS_File_JSON_Reader::load( $this->filePath, TRUE );
		$this->scopes	= array_keys( $this->fileData );
		$this->pages	= array();
		$pageId			= 0;
		$baseItem		= array(
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
			'format'		=> 'HTML',
			'createdAt'		=> 0,
			'modifiedAt'	=> 0,
		);
		$types			= array( 'page', 'menu', 'module' );
		foreach( $this->scopes as $scopeNr => $scope ){
			foreach( $this->fileData[$scope] as $pageNr => $page ){
				$pageId++;
				if( empty( $page['type'] ) ){
					$page['type']	= 0;
					if( !empty( $page['pages'] ) )
						$page['type']	= 1;
					else if( !empty( $page['controller'] ) )
						$page['type']	= 2;
				}
				$pageItem	= (object) array_merge( $baseItem, array(
					'pageId'		=> $pageId,
					'status'		=> 1,			//@todo realize
					'type'			=> (int) array_search( $page['type'], $types ),
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
						if( empty( $subpage['type'] ) ){
							$subpage['type']	= 0;
							if( !empty( $subpage['pages'] ) )
								$subpage['type']	= 1;
							else if( !empty( $subpage['controller'] ) )
								$subpage['type']	= 2;
						}
						$subpageItem	= (object) array_merge( $baseItem, array(
							'pageId'		=> $pageId,
							'parentId'		=> $pageItem->pageId,
							'status'		=> 1,			//@todo realize
							'type'			=> (int) array_search( $subpage['type'], $types ),
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
