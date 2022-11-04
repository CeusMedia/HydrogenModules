<?php

use CeusMedia\HydrogenFramework\Controller;

class Controller_Manage_Shop_Special extends Controller
{
	public function ajaxLoadCatalogArticles( $bridgeId )
	{
		$bridge 	= $this->logicBridge->getBridge( $bridgeId );
		$articles	= $bridge->object->getAll( [], ['title' => 'ASC'] );

		$data	=  [];
		$column	= $bridge->data->articleIdColumn;
		foreach( $articles as $article )
			$data[]	= (object) array(
				'id'	=> $article->{$column},
				'title'	=> $article->title,
			);
		print( json_encode( $data ) );
		die;
	}

	public function add()
	{
		if( $this->request->getMethod()->isPost() ){
			$logicAuth	= $this->env->getLogic()->get( 'authentication' );
			$data	= array(
				'bridgeId'		=> $this->request->get( 'bridgeId' ),
				'articleId'		=> $this->request->get( 'articleId' ),
				'creatorId'		=> $logicAuth->getCurrentUserId(),
				'title'			=> $this->request->get( 'title' ),
				'createdAt'		=> time(),
				'modifiedAt'	=> time(),
			);
			$specialId	= $this->modelSpecial->add( $data );
			$this->messenger->noteSuccess( 'Spezialität hinzugefügt.' );
			$this->restart( 'edit/'.$specialId, TRUE );
		}
		$this->addData( 'specials', $this->modelSpecial->getAll() );
	}

	public function edit( $specialId )
	{
		if( !( $special = $this->checkId( $specialId, FALSE ) ) ){
			$this->messenger->noteError( 'Ungültige ID.' );
			$this->restart( NULL, TRUE );
		}
		$bridge	= $this->logicBridge->getBridge( $special->bridgeId );
		$special->article	= $bridge->object->get( $special->articleId );
		if( !strlen( trim( $special->styleFiles ) ) )
			$special->styleFiles	= '[]';
		$special->styleFiles	= json_decode( $special->styleFiles );

		if( $this->request->getMethod()->isPost() ){
			$data	= [];
			if( strlen( trim( $this->request->get( 'title' ) ) ) ){
				if( $special->title != $this->request->get( 'title' ) )
					$data['title']	= $this->request->get( 'title' );
			}
			if( $this->request->has( 'styleRules' ) )
				$data['styleRules']	= $this->request->get( 'styleRules' );
			if( $this->request->get( 'styleFile' ) ){
				$special->styleFiles[]	= $this->request->get( 'styleFile' );
				$data['styleFiles']	= json_encode( $special->styleFiles );
			}
			if( $data ){
				$data['modifiedAt']	= time();
				$this->modelSpecial->edit( $special->shopSpecialId, $data );
			}
			$this->restart( 'edit/'.$special->shopSpecialId, TRUE );
		}

		if( $this->request->getMethod()->isPost() ){
			$specialId	= $this->modelSpecial->add( [] );
			$this->messenger->noteSuccess( 'Spezialität hinzugefügt.' );
			$this->restart( 'edit/'.$specialId, TRUE );
		}
		$this->addData( 'specials', $this->modelSpecial->getAll() );
		$this->addData( 'special', $special );
	}

	public function index()
	{
	}

	public function removeStyleFile( $specialId, $nr )
	{
		if( !( $special = $this->checkId( $specialId, FALSE ) ) ){
			$this->messenger->noteError( 'Ungültige ID.' );
			$this->restart( NULL, TRUE );
		}
		if( !strlen( trim( $special->styleFiles ) ) )
			$special->styleFiles	= '[]';
		$files	= json_decode( $special->styleFiles, TRUE );
		if( isset( $files[(int) $nr] ) ){
			unset( $files[(int) $nr] );
			$this->modelSpecial->edit( $specialId, array(
				'styleFiles'	=> json_encode( $files ),
				'modifiedAt'	=> time(),
			) );
		}
		$this->restart( 'edit/'.$specialId, TRUE );
	}

	protected function __onInit(): void
	{
		$this->request		= $this->env->getRequest();
		$this->messenger	= $this->env->getMessenger();
		$this->modelSpecial	= new Model_Shop_Special( $this->env );
		$this->logicBridge	= new Logic_ShopBridge( $this->env );
		if( $this->env->getModules()->has( 'Resource_Frontend' ) ){
			$frontend	= Logic_Frontend::getInstance( $this->env );
			$this->appPath	= $frontend->getPath();
			$this->appUrl	= $frontend->getUri();
		}
		else{
			$this->appPath	= $this->env->path;
			$this->appUrl	= $this->env->url;
		}
		$this->addData( 'appPath', $this->appPath );
		$this->addData( 'appUrl', $this->appUrl );
		$this->shopBridges	= $this->logicBridge->getBridges();
		$this->addData( 'catalogs', $this->shopBridges );
		$this->addData( 'specials', $this->modelSpecial->getAll() );
	}

	protected function checkId( $specialId, bool $strict = TRUE  )
	{
		$special	= $this->modelSpecial->get( $specialId );
		if( $special )
			return $special;
		if( $strict )
			throw new RangeException( 'Invalid special ID' );
		return FALSE;
	}
}
