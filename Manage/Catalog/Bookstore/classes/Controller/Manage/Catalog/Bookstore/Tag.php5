<?php
class Controller_Manage_Catalog_Bookstore_Tag extends CMF_Hydrogen_Controller{

	protected $fontend;
	protected $logic;
	protected $messenger;
	protected $request;
	protected $session;
	protected $sessionPrefix;

	public function __onInit(){
		parent::__onInit();
		$this->env->clock->profiler->tick( 'Controller_Manage_Catalog_Article::init start' );
		$this->messenger		= $this->env->getMessenger();
		$this->request			= $this->env->getRequest();
		$this->session			= $this->env->getSession();
		$this->logic			= new Logic_Catalog_Bookstore( $this->env );
		$this->frontend			= Logic_Frontend::getInstance( $this->env );
		$this->moduleConfig		= $this->env->getConfig()->getAll( 'module.manage_catalog_bookstore.', TRUE );
		$this->sessionPrefix	= 'module.manage_catalog_bookstore_article.filter.';
		$this->addData( 'frontend', $this->frontend );
	}

	public function filter( $reset = 0 ){
		if( $reset ){
			$this->session->remove( 'filter_manage_catalog_bookstore_tag_search' );
		}
		else{
			$this->session->set( 'filter_manage_catalog_bookstore_tag_search', $this->request->get( 'search' ) );
		}
		$this->restart( NULL, TRUE );
	}

	public function index( $limit = 10, $page = 0 ){
		$modelTag		= new Model_Catalog_Bookstore_Article_Tag( $this->env );
		$modelArticle	= new Model_Catalog_Bookstore_Article( $this->env );


		$conditions		= array();
		if( $this->session->get( 'filter_manage_catalog_bookstore_tag_search' ) )
			$conditions['tag']	= '%'.$this->session->get( 'filter_manage_catalog_bookstore_tag_search' ).'%';

		$list			= $modelTag->getAll( $conditions, array( 'tag' => 'ASC' ) );
		$tags			= array();
		$articleIds		= array();
//print_m( $list[0] );die;
		foreach( $list as $item ){
			if( !array_key_exists( $item->tag, $tags ) ){
				$tags[$item->tag]	= (object) array(
					'tag'			=> $item->tag,
					'articleIds'	=> array(),
				);
			}
			$tags[$item->tag]->articleIds[]	= $item->articleId;
		}
		foreach( $list as $item ){
			if( !array_key_exists( $item->articleId, $articleIds ) )
				$articleIds[$item->articleId]	= array();
			$articleIds[$item->articleId][]	= $item->tag;
		}
		$articles	= array();
		if( $articleIds ){
			$list		= $modelArticle->getAll( array( 'articleId' => array_keys( $articleIds ) ) );
			foreach( $list as $item ){
				$articles[$item->articleId]	= $item;
			}
		}

		$total		= count( $tags );
		$tags		= array_slice( $tags, $page * $limit, $limit );

		$this->addData( 'page', (int) $page );
		$this->addData( 'limit', $limit );
		$this->addData( 'total', $total );

		$this->addData( 'tags', $tags );
		$this->addData( 'articleIds', $articleIds );
		$this->addData( 'articles', $articles );
		$this->addData( 'filterSearch', $this->session->get( 'filter_manage_catalog_bookstore_tag_search' ) );
	}
}
