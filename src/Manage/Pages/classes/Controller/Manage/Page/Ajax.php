<?php

use CeusMedia\Common\Alg\Text\Filter as TextFilter;
use CeusMedia\Common\Alg\Text\TermExtractor as TextTermExtractor;
use CeusMedia\Common\FS\File\Collection\Reader as ListFileReader;
use CeusMedia\Common\FS\File\Collection\Editor as ListFileEditor;
use CeusMedia\HydrogenFramework\Controller\Ajax as AjaxController;

class Controller_Manage_Page_Ajax extends AjaxController
{
	protected $sessionPrefix		= 'filter_manage_pages_';
	protected $model;
	protected $envManaged;

	protected function __onInit(): void
	{
		$this->request	= $this->env->getRequest();
		$this->session	= $this->env->getSession();
		$this->appFocus	= $this->session->get( $this->sessionPrefix.'app' );

		$this->envManaged	= $this->env;

		if( $this->appFocus === 'frontend' ){
			$this->frontend		= Logic_Frontend::getInstance( $this->env );
			$this->envManaged	= $this->frontend->getEnv();
		}
		$source	= $this->envManaged->getModules( TRUE )->get( 'UI_Navigation' )->config['menu.source']->value;
		if( $source === 'Database' )
			$this->model		= new Model_Page( $this->envManaged );
		else if( $source === 'Config' )
			$this->model		= new Model_Config_Page( $this->envManaged );
	}

	public function blacklistSuggestedKeywords(){
		try{
			$pageId			= $this->request->get( 'pageId' );					//  get page ID from request
			$page			= $this->checkPageId( $pageId );					//  check if page ID is valid
			$blacklistFile	= 'config/terms.blacklist.txt';
			$wordsInput		= trim( $this->request->get( 'words' ) );			//  get string of whitespace concatenated words from request
			$wordsGiven		= [];											//  prepare empty list of given words to add to blacklist
			if( strlen( trim( $wordsInput ) ) )									//  given string of listed keywords is not empty
				$wordsGiven		= preg_split( '/\s*(,|\s)\s*/', $wordsInput );	//  split to list of words to add to blacklist
			$wordsAdded		= [];											//  prepare empty list of words added to blacklist
			if( count( $wordsGiven ) ){											//  atleast one word is given
				if( !file_exists( $blacklistFile ) )							//  blacklist file is not existing, yet
					touch( $blacklistFile );										//  create empty list file
				$editor	= new ListFileEditor( $blacklistFile );			//  start list editor
				foreach( $wordsGiven as $wordToAdd ){							//  iterate trimmed words
					if( !$editor->hasItem( $wordToAdd ) )						//  word is not in list
						$editor->add( trim( $wordToAdd ) );						//  add word to list and save
				}
			}
			$blacklist	= ListFileReader::read( $blacklistFile );			//  read list of words in blacklist

			$pages	= $this->model->getAll();
			foreach( $pages as $page ){
				$keywords	= [];
				if( strlen( trim( $page->keywords ) ) )
					$keywords	= preg_split( '/\s*,\s*/', $page->keywords );
				if( $keywords ){
					$reduced	= array_diff( $keywords, $blacklist );
					if( count( $reduced ) !== count( $keywords ) ){
						$this->model->edit( $page->pageId, array(
							'keywords'	=> join( ', ', $reduced )
						) );
					}
				}
			}
			$page		= $this->checkPageId( $pageId );						//  get updated page object
			$keywords	= preg_split( '/\s*,\s*/', $page->keywords );
			$this->respondData( array(											//  respond to client
//				'changed'	=> count( $wordsGiven ),
				'keywords'	=> $keywords,										//  updated page keywords
				'blacklist'	=> $blacklist,										//  updated blacklisted words
			) );
		}
		catch( Exception $e ){													//  an exception has been thrown
			$this->respondException( $e );										//  respond to client
		}
	}

	public function orderPages(){
		$pageIds	= $this->request->get( 'pageIds' );
		foreach( $pageIds as $nr => $pageId )
			$this->model->edit( $pageId, ['rank' => $nr + 1] );
		$this->respondData( TRUE );												//  respond to client
	}

	public function saveContent(){
		$content	= $this->request->get( 'content' );
		$pageId		= $this->request->get( 'pageId' );
		$result		= ['status' => FALSE];
		try{
			/*	@todo remove this old string-based solution soon */
			if( preg_match( '/[a-z]/', $pageId ) ){
				if( $page = $this->model->getByIndex( 'identifier', $pageId ) ){
					$this->model->edit( $page->pageId, array(
						'content'		=> $content,
						'modifiedAt'	=> time(),
					), FALSE );
					$result	= ['pageId' => $pageId, 'content' => $content];
					$result	= ['status' => TRUE];
				}
			}
			else if( $pageId ){
				if( $page = $this->model->get( (int) $pageId ) ){
					$this->model->edit( $page->pageId, array(
						'content'		=> $content,
						'modifiedAt'	=> time(),
					), FALSE );
					$result	= ['status' => TRUE];
				}
			}
			$this->respondData( TRUE );											//  respond to client
		}
		catch( Exception $e ){
			$this->respondException( $e );										//  respond to client
		}
	}

	public function setEditor( $editor, $format = 'HTML' ){
		$sessionKey	= $this->sessionPrefix.$this->appFocus.'.editor.'.strtolower( $format );
		$this->session->set( $sessionKey, $editor );
		$this->respondData( TRUE );
	}

	public function setTab( $tabKey ){
		$this->session->set( $this->sessionPrefix.$this->appFocus.'.tab', $tabKey );
		$this->respondData( array(
			'app'		=> $this->appFocus,
			'tab'		=> $tabKey,
			'result'	=> TRUE,
		) );
	}

	public function suggestKeywords(){
		$pageId	= $this->request->get( 'pageId' );
		$page	= $this->checkPageId( $pageId );
		$html	= TextFilter::stripComments( $page->content );
		$html	= TextFilter::stripScripts( $html );
		$html	= TextFilter::stripStyles( $html );
		$html	= TextFilter::stripEventAttributes( $html );
//		$html	= TextFilter::stripTags( $html );
//		$html	= htmlspecialchars_decode( $html );
		$html	= preg_replace( "@<[\/\!]*?[^<>]*?>@si", " ", $html );
		$html	= str_replace( "&nbsp;", " ", $html );
		$blacklist	= 'config/terms.blacklist.txt';
		if( file_exists( $blacklist ) )
			TextTermExtractor::loadBlacklist( $blacklist );
		$terms	= TextTermExtractor::getTerms( $html );
		$list	= [];
		foreach( $terms as $term => $count )
			if( preg_match( '/^[A-Z]/', $term ) )
				if( preg_match( '/[A-Z]$/i', $term ) )
					$list[]	= htmlspecialchars_decode( html_entity_decode( $term ) );
		$this->respondData( $list );
	}

	protected function checkPageId( $pageId, $strict = FALSE ){
		if( !$pageId ){
			if( $strict )
				throw new OutOfRangeException( 'No page ID given' );
			return FALSE;
		}
		$page	= $this->model->get( $pageId );
		if( !$page ){
			if( $strict )
				throw new OutOfRangeException( 'Invalid page ID given' );
			return FALSE;
		}
		return $page;
//		return $this->translatePage( $page );
	}

/*	protected function translatePage( $page ){
		if( !class_exists( 'Logic_Localization' ) )							//  localization module is not installed
			return $page;
		$localization	= new Logic_Localization( $this->env );
		$localization->setLanguage( $this->appSession->get( 'language' ) );
//		remark( $localization->getLanguage() );
		$id	= 'page.'.$page->identifier.'-title';
//		remark( $id );
		$page->title	= $localization->translate( $id, $page->title );
		$id	= 'page.'.$page->identifier.'-content';
		$page->content	= $localization->translate( $id, $page->content );
		return $page;
	}*/
}
