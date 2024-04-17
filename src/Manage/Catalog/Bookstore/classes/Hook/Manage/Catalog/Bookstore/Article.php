<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\Alg\Text\Trimmer as TextTrimmer;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Manage_Catalog_Bookstore_Article extends Hook
{
	/**
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function onTinyMCE_getImageList(): void
	{
		$cache		= $this->env->getCache();
		if( 1 || !( $list = $cache->get( 'catalog.tinymce.images.catalog.bookstore.articles' ) ) ){
			$logic		= new Logic_Catalog_BookstoreManager( $this->env );
			$frontend	= Logic_Frontend::getInstance( $this->env );
			$config		= $this->env->getConfig()->getAll( 'module.manage_catalog_bookstore.', TRUE );				//  focus module configuration
			$pathCovers	= $frontend->getPath( 'contents' ).$config->get( 'path.covers' );			//  get path to cover images
			$pathCovers	= substr( $pathCovers, strlen( $frontend->getPath() ) );					//  strip frontend base path
			$list		= [];
			$conditions	= ['cover' => '> 0'];
			$orders		= ['title' => 'ASC'];
			foreach( $logic->getArticles( $conditions, $orders, [0, 200] ) as $item ){
				$id		= str_pad( $item->articleId, 5, 0, STR_PAD_LEFT );
				$list[] = (object) [
					'title'	=> TextTrimmer::trimCentric( $item->title, 60 ),
					'value'	=> 'file/bookstore/article/m/'.$item->cover,
				];
			}
			$cache->set( 'catalog.tinymce.images.catalog.bookstore.articles', $list );
		}
		$this->context->list	= array_merge( $this->context->list, [(object) [				//  extend global collection by submenu with list of items
			'title'	=> 'Veröffentlichungen:',									//  label of submenu @todo extract
			'menu'	=> array_values( $list ),									//  items of submenu
		]] );
	}

	/**
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function onTinyMCE_getLinkList(): void
	{
		$cache		= $this->env->getCache();
		$logic		= new Logic_Catalog_BookstoreManager( $this->env );
		$frontend	= Logic_Frontend::getInstance( $this->env );
		$config		= $this->env->getConfig()->getAll( 'module.manage_catalog_bookstore.', TRUE );

		if( !( $articles = $cache->get( 'catalog.tinymce.links.catalog.bookstore.articles' ) ) ){
			$orders		= ['articleId' => 'DESC'];
			$limits		= [];//array( 0, 200 );
			$articles	= $logic->getArticles( [], $orders, $limits );
			foreach( $articles as $nr => $item ){
/*				$category	= $logic->getCategoryOfArticle( $article->articleId );
				if( $category->volume )
					$item->title	.= ' - Band '.$category->volume;
*/				$articles[$nr]	= (object) [
					'title'	=> TextTrimmer::trimCentric( $item->title, 80 ),
					'value'	=> $logic->getArticleUri( $item ),
				];
			}
			$cache->set( 'catalog.tinymce.links.catalog.bookstore.articles', $articles );
		}
		$words	= $this->env->getLanguage()->getWords( 'manage/catalog/bookstore' );
		$this->context->list	= array_merge( $this->context->list, [(object) [
			'title'	=> $words['tinymce-menu-links']['articles'],
			'menu'	=> array_values( $articles ),
		]] );

		if( 1 ||  !( $documents = $cache->get( 'catalog.tinymce.links.catalog.bookstore.documents' ) ) ){
			$pathDocs	= $frontend->getPath( 'contents' ).$config->get( 'path.documents' );
			$limits		= [];//array( 0, 200 );
			$orders		= ['articleDocumentId' => 'DESC'];
			$documents	= $logic->getDocuments( [], $orders, $limits );
			foreach( $documents as $nr => $item ){
				$id				= str_pad( $item->articleId, 5, 0, STR_PAD_LEFT );
				$article		= $logic->getArticle( $item->articleId, FALSE );
				if( $article )
					$documents[$nr]	= (object) [
//					'title'	=> TextTrimmer::trimCentric( $article->title, 40 ).' - '.$item->title,
					'title'	=> $article->title.' - '.$item->title,
					'value'	=> 'file/bookstore/document/'.$item->url,
				];
			}
			$cache->set( 'catalog.tinymce.links.catalog.bookstore.documents', $documents );
		}
		$this->context->list	= array_merge( $this->context->list, [(object) [
			'title'	=> $words['tinymce-menu-links']['documents'],
			'menu'	=> array_values( $documents ),
		]] );
	}
}
