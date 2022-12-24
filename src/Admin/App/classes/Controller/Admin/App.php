<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Alg\ID;
use CeusMedia\Common\FS\File\INI\Editor as IniFileEditor;
use CeusMedia\Common\FS\Folder\Editor as FolderEditor;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Language as LanguageResource;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;

class Controller_Admin_App extends Controller
{
	protected Dictionary $config;
	protected LanguageResource $language;
	protected ?MessengerResource $messenger;
	protected HttpRequest $request;

	public function index(): void
	{
		$words	= $this->language->getWords( 'main' );
		$this->addData( 'appTitle', $words['main']['title'] );
		$this->addData( 'appBrand', $words['main']['brand'] );
		$this->addData( 'appLogo', $this->config->get( 'app.logo' ) );
		$this->addData( 'appIcon', $this->config->get( 'app.icon' ) );
	}

	public function removeIcon(): void
	{
		if( $current = $this->config->get( 'app.icon' ) ){
			@unlink( $current );
			$this->setConfig( 'app.icon', '' );
		}
		$this->restart( NULL, TRUE );
	}

	public function removeLogo(): void
	{
		if( $current = $this->config->get( 'app.logo' ) ){
			@unlink( $current );
			$this->setConfig( 'app.logo', '' );
		}
		$this->restart( NULL, TRUE );
	}

	public function setBrand(): void
	{
		if( strlen( trim( $brand = $this->request->get( 'brand' ) ) ) ){
			if( $this->setMainWord( 'brand', $brand ) )
				$this->messenger->noteSuccess( 'Der Brand wurde geändert.' );
		}
		$this->restart( NULL, TRUE );
	}

	public function setIcon(): void
	{
		try{
			$icon = (object) $this->request->get( 'icon' );
			if( $fileName = $this->uploadImage( $icon ) ){
				$this->setConfig( 'app.icon', $fileName );
				$this->messenger->noteSuccess( 'Das Icon wurde geändert.' );
			}
		}
		catch( Exception $e ){
			$this->messenger->noteError( 'Fehler: '.$e->getMessage() );
		}
		$this->restart( NULL, TRUE );
	}

	public function setLogo(): void
	{
		try{
			$logo = (object) $this->request->get( 'logo' );
			if( $fileName = $this->uploadImage( $logo ) ){
				$this->setConfig( 'app.logo', $fileName );
				$this->messenger->noteSuccess( 'Das Logo wurde geändert.' );
			}
		}
		catch( Exception $e ){
			$this->messenger->noteError( 'Fehler: '.$e->getMessage() );
		}
		$this->restart( NULL, TRUE );
	}

	public function setTitle(): void
	{
		if( strlen( trim( $title = $this->request->get( 'title' ) ) ) ){
			if( $this->setMainWord( 'title', $title ) )
				$this->messenger->noteSuccess( 'Der Titel wurde geändert.' );
		}
		$this->restart( NULL, TRUE );
	}

	protected function __onInit(): void
	{
		$this->config		= $this->env->getConfig();
		$this->request		= $this->env->getRequest();
		$this->messenger	= $this->env->getMessenger();
		$this->language		= $this->env->getLanguage();
	}

	protected function setConfig( string $key, $value ): ?bool
	{
		if( $this->config->get( $key ) == $value )
			return NULL;
		$fileName	= 'config/config.ini';
		$editor		= new IniFileEditor( $fileName );
		$editor->setProperty( $key, $value );
		return TRUE;
	}

	protected function setMainWord( string $key, $value ): bool
	{
		$language	= $this->language->getLanguage();
		$fileName	= $this->config['path.locales'].$language.'/main.ini';
		$editor		= new IniFileEditor( $fileName );
		if( $value === $editor->getProperty( $key, 'main' ) )
			return FALSE;
		$editor->setProperty( $key, $value, 'main' );
		return TRUE;
	}

	/**
	 *	@param		object		$upload
	 *	@return		string|FALSE|NULL
	 */
	protected function uploadImage( object $upload )
	{
		if( $upload->error === 4 )
			return NULL;

		$logicUpload	= new Logic_Upload( $this->env );
		try{
			$logicUpload->setUpload( $upload );
			$pathImages		= $this->config->get( 'path.images' ).'/logo/';
			$fileName		= $pathImages.ID::uuid().'.'.$logicUpload->getExtension();
			FolderEditor::createFolder( $pathImages );
			$logicUpload->saveTo( $fileName );
			return $fileName;
		}
		catch( Exception $e ){
			$helper	= new View_Helper_UploadError( $this->env );
			$helper->setUpload( $logicUpload );
			$this->messenger->noteError( $helper->render() );
			return FALSE;
		}
	}
}
