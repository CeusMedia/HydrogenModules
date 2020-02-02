<?php
class Model_Config_Page
{
	public function __construct( CMF_Hydrogen_Environment $env ){
		$this->env		= $env;
		$this->filePath	= $env->uri.'config/pages.json';
		$this->loadPages();
	}

	public function edit( $pageId, $data = array() ){
		throw new RuntimeException( 'Not implemented yet' );
	}

	public function get( $pageId ){
		$pageId	= (int) $pageId;
		foreach( $this->pages as $page )
			if( $page->pageId === $pageId )
				return $page;
		return NULL;
	}

	public function getAll(){
		return $this->pages;
	}

	public function getByIndices( $indices = array(), $orders = array() ){
		return current( $this->getAllByIndices( $indices, $orders, array( 0, 1 ) ) );
	}

	public function getAllByIndices( $indices = array(), $orders = array(), $limits = array() ){
		$data	= $this->pages;
		$regExp	= '/^(!=|>=|<=|>|<) (.+)$/';
		foreach( $indices as $indexKey => $indexValue ){
			foreach( $data as $nr => $page ){
				$pageValue	= $page->$indexKey;
				$matches	= array();
				if( preg_match( $regExp, $indexValue, $matches ) ){
					if( $matches[1] === '!= ' && $matches[2] !== (string) $pageValue ||
						$matches[1] === '>= ' && (int) $matches[1] >= (int) $pageValue ||
						$matches[1] === '<= ' && (int) $matches[1] <= (int) $pageValue ||
						$matches[1] === '> ' && (int) $matches[1] > (int) $pageValue ||
						$matches[1] === '< ' && (int) $matches[1] < (int) $pageValue )
						unset( $data[$nr] );
				}
				else if( $pageValue != $indexValue )
					unset( $data[$nr] );
			}
		}
		return $data;
	}

	//  --  PROTECTED  --  //

	protected function loadPages(){
		$this->fileData	= FS_File_JSON_Reader::load( $this->filePath, TRUE );
		$this->scopes	= array_keys( $this->fileData );
		$this->pages	= array();
		$pageId			= 0;
		$baseItem		= array(
			'parentId'		=> 0,
			'status'		=> 0,
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
				$pageItem	= (object) array_merge( $baseItem, array(
					'pageId'		=> $pageId,
					'status'		=> 1,			//@todo realize
					'type'			=> (int) array_search( $page['type'], $types ),
					'scope'			=> $scopeNr,
					'rank'			=> $pageNr + 1,
					'identifier'	=> $page['path'],
					'title'			=> $page['label'],
					'description'	=> !empty( $page['desc'] ) ? $page['desc'] : '',
					'icon'			=> !empty( $page['icon'] ) ? $page['icon'] : '',
					'template'		=> !empty( $page['template'] ) ? $page['template'] : '',
				) );
				$this->pages[$pageItem->pageId]	= $pageItem;
				$this->fileData[$scope][$pageNr]['pageId']	= $pageItem->pageId;
				if( !empty( $page['pages'] ) ){
					foreach( $page['pages'] as $subpageNr => $subpage ){
						$pageId++;
						$subpageItem	= (object) array_merge( $baseItem, array(
							'pageId'		=> $pageId,
							'parentId'		=> $pageItem->pageId,
							'status'		=> 1,			//@todo realize
							'type'			=> (int) array_search( $subpage['type'], $types ),
							'scope'			=> $scopeNr,
							'rank'			=> $subpageNr + 1,
							'identifier'	=> $subpage['path'],
							'title'			=> $subpage['label'],
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
