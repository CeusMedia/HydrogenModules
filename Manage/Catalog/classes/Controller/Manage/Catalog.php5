<?php
class Controller_Manage_Catalog extends CMF_Hydrogen_Controller{

	/**	@var		Logic_Catalog		$logic */
	protected $logic;

	protected function __onInit(){
		$this->logic		= new Logic_Catalog( $this->env );
		$this->session		= $this->env->getSession();
		$this->request		= $this->env->getRequest();
		$this->messenger	= $this->env->getMessenger();
	}

	/**
	 *	...
	 *	@static
	 *	@access		public
	 *	@param		object		$env
	 *	@param		object		$context
	 *	@param		unknown		$module
	 *	@param		unknown		$arguments
	 *	@return		void
	 *	@todo		kriss: add authors and categories
	 *	@todo		kriss: code doc
	 */
	static public function ___onTinyMCE_getLinkList( $env, $context, $module, $arguments = array() ){
		return "";
		$config			= $env->getConfig()->getAll( 'module.manage_catalog.', TRUE );
		$logic			= new Logic_Catalog( $env );

		$words = $env->getLanguage()->getWords( 'js/tinymce' );
		$prefixes		= (object) $words['link-prefixes'];

		$list			= array();
		$articles		= $logic->getArticles( array(), array( 'title' => 'ASC' ) );
		foreach( $articles as $article ){
			$list[]	= (object) array(
				'title'	=> $prefixes->article.Alg_Text_Trimmer::trim( $article->title, 40 ),
				'url'	=> $logic->getArticleUri( $article->articleId ),
			);
		}
		$context->list	= array_merge( $context->list, $list );
	}

/*	static public function ___onTinyMCE_getImageList( $env, $context, $module, $arguments = array() ){
		self::___onTinyMCE_getImageList_Covers( $env, $context, $module, $arguments );
		self::___onTinyMCE_getImageList_Authors( $env, $context, $module, $arguments );
	}

	static public function ___onTinyMCE_getImageList_Authors( $env, $context, $module, $arguments = array() ){
		$logic		= new Logic_Catalog( $env );
		$config		= $env->getConfig()->getAll( 'module.manage_catalog.', TRUE );
		$pathImages	= 'contents/'.$config->get( 'path.frontend.authors' );							//  @todo resolve base path
		$list       = array();
		$authors	= $logic->getAuthors( array(), array( 'lastname' => 'ASC', 'firstname' => 'ASC' ) );
		foreach( $authors as $item ){
			if( $item->image ){
				$label	= $item->lastname.( $item->firstname ? ', '.$item->firstname : "" );
				$list[] = (object) array(
					'title'	=> $item->authorId.' - '.Alg_Text_Trimmer::trimCentric( $label, 60 ),
					'value'	=> $pathImages.$item->image,
				);
			}
		}
        $context->list  = array_merge( $context->list, array(						//  extend global collection
			(object) array(															//  by submenu with list of items
				'title'	=> 'Covers:',//$prefixes->image,							//  label of submenu
				'menu'	=> array_values( $list ),									//  items of submenu
			)
		) );
	}

	static public function ___onTinyMCE_getImageList_Covers( $env, $context, $module, $arguments = array() ){
		$logic		= new Logic_Catalog( $env );
		$config		= $env->getConfig()->getAll( 'module.manage_catalog.', TRUE );
		$pathCovers	= 'contents/'.$config->get( 'path.frontend.covers' );							//  @todo resolve base path
		$list       = array();
		$articles	= $logic->getArticles( array(), array( 'articleId' => 'DESC', 'title' => 'ASC' ) );
		foreach( $articles as $item ){
			if( $item->cover ){
				$id		= str_pad( $item->articleId, 5, 0, STR_PAD_LEFT );
				$label	= Alg_Text_Trimmer::trimCentric( $item->title, 60 );
				$list[] = (object) array(
					'title'	=> '<small class="muted">#'.$item->articleId.'</small> '.$label,
					'value'	=> $pathCovers.$id.'__'.$item->cover,
				);
			}
		}
        $context->list  = array_merge( $context->list, array(						//  extend global collection
			(object) array(															//  by submenu with list of items
				'title'	=> 'Covers:',//$prefixes->image,							//  label of submenu
				'menu'	=> array_values( $list ),									//  items of submenu
			)
		) );
	}
*/
	public function index(){
	}

	public function setTab( $newsletterId, $tabKey ){
		$this->session->set( 'manage.catalog.tab', $tabKey );
	}
}
?>
