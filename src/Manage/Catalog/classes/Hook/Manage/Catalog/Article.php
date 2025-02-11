<?php

use CeusMedia\Common\Alg\Text\Trimmer as TextTrimmer;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Manage_Catalog extends Hook
{
	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function onTinyMCE_getImageList(): void
	{
		$cache		= $this->env->getCache();
		if( !( $list = $cache->get( 'catalog.tinymce.images.articles' ) ) ){
			$logic		= new Logic_Catalog( $this->env );
			$frontend	= Logic_Frontend::getInstance( $this->env );
			$config		= $this->env->getConfig()->getAll( 'module.manage_catalog.', TRUE );				//  focus module configuration
			$pathCovers	= $frontend->getPath('contents') . $config->get('path.covers');			//  get path to cover images
			$pathCovers	= substr( $pathCovers, strlen( $frontend->getPath() ) );					//  strip frontend base path
			$list       = [];
			$conditions	= ['cover' => '> 0'];
			$orders		= ['articleId' => 'DESC'];
			foreach( $logic->getArticles( $conditions, $orders, [0, 200] ) as $item ){
				$id		= str_pad( $item->articleId, 5, 0, STR_PAD_LEFT );
				$list[] = (object) array(
					'title'	=> TextTrimmer::trimCentric( $item->title, 60 ),
					'value'	=> $pathCovers.$id.'__'.$item->cover,
				);
			}
			$cache->set( 'catalog.tinymce.images.articles', $list );
		}
		$this->context->list  = array_merge( $this->context->list, [(object) [				//  extend global collection by submenu with list of items
			'title'	=> 'Veröffentlichungen:',												//  label of submenu @todo extract
			'menu'	=> array_values( $list ),												//  items of submenu
		]] );
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function onTinyMCE_getLinkList(): void
	{
		$cache		= $this->env->getCache();
		$logic		= new Logic_Catalog( $this->env );
		$frontend	= Logic_Frontend::getInstance( $this->env );
		$config		= $this->env->getConfig()->getAll( 'module.manage_catalog.', TRUE );

		if( !( $articles = $cache->get( 'catalog.tinymce.links.articles' ) ) ){
			$orders		= ['articleId' => 'DESC'];
			$articles	= $logic->getArticles( [], $orders, [0, 200] );
			foreach( $articles as $nr => $item ){
				/*				$category	= $logic->getCategoryOfArticle( $article->articleId );
								if( $category->volume )
									$item->title	.= ' - Band '.$category->volume;
				*/				$articles[$nr]	= (object) array(
					'title'	=> TextTrimmer::trimCentric( $item->title, 80 ),
					'value'	=> $logic->getArticleUri( $item ),
				);
			}
			$cache->set( 'catalog.tinymce.links.articles', $articles );
		}
		$this->context->list	= array_merge( $this->context->list, [(object) [
			'title'	=> 'Veröffentlichungen:',
			'menu'	=> array_values( $articles ),
		]] );

		if( !( $documents = $cache->get( 'catalog.tinymce.links.documents' ) ) ){
			$pathDocs	= $frontend->getPath('contents') . $config->get('path.documents');
			$documents	= $logic->getDocuments( [], ['articleDocumentId' => 'DESC'], [0, 200] );
			foreach( $documents as $nr => $item ){
				$id				= str_pad( $item->articleId, 5, 0, STR_PAD_LEFT );
				$article		= $logic->getArticle( $item->articleId );
				$documents[$nr]	= (object) array(
					'title'	=> TextTrimmer::trimCentric( $article->title, 40 ).' - '.$item->title,
					'value'	=> $pathDocs.$id.'_'.$item->url,
				);
			}
			$cache->set( 'catalog.tinymce.links.documents', $documents );
		}
		$this->context->list	= array_merge( $this->context->list, [(object) [
			'title'	=> 'Dokuments:',
			'menu'	=> array_values( $documents ),
		]] );
	}

}