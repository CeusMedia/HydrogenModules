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

	static public function ___onTinyMCE_getLinkList( $env, $context, $module, $arguments = array() ){
		return "";
		$config			= $env->getConfig()->getAll( 'module.manage_catalog.', TRUE );
		$logic			= new Logic_Catalog( $env );

		$words			= $env->getLanguage()->getWords( 'js/tinymce' );
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

	public function index(){
	}

	public function setTab( $newsletterId, $tabKey ){
		$this->session->set( 'manage.catalog.tab', $tabKey );
	}
}
?>
