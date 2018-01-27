<?php
class Controller_Admin_App extends CMF_Hydrogen_Controller{

	protected $config;
	protected $language;
	protected $messenger;
	protected $request;

	protected function __onInit(){
		$this->config		= $this->env->getConfig();
		$this->request		= $this->env->getRequest();
		$this->messenger	= $this->env->getMessenger();
		$this->language		= $this->env->getLanguage();
	}
	public function index(){
		$words	= $this->language->getWords( 'main' );
		$this->addData( 'appTitle', $words['main']['title'] );
		$this->addData( 'appBrand', $words['main']['brand'] );
		$this->addData( 'appLogo', $this->config->get( 'app.logo' ) );
		$this->addData( 'appIcon', $this->config->get( 'app.icon' ) );
	}

	public function removeIcon(){
		if( $current = $this->config->get( 'app.icon' ) ){
			@unlink( $current );
			$this->setConfig( 'app.icon', '' );
		}
		$this->restart( NULL, TRUE );
	}

	public function removeLogo(){
		if( $current = $this->config->get( 'app.logo' ) ){
			@unlink( $current );
			$this->setConfig( 'app.logo', '' );
		}
		$this->restart( NULL, TRUE );
	}

	public function setBrand(){
		if( strlen( trim( $brand = $this->request->get( 'brand' ) ) ) ){
			if( $this->setMainWord( 'brand', $brand ) )
				$this->messenger->noteSuccess( 'Der Brand wurde geändert.' );
		}
		$this->restart( NULL, TRUE );
	}

	protected function setConfig( $key, $value ){
		if( $this->config->get( $key ) == $value )
			return NULL;
		$fileName	= 'config/config.ini';
		$editor		= new FS_File_INI_Editor( $fileName );
		$editor->setProperty( $key, $value );
		return TRUE;
	}

	public function setIcon(){
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

	public function setLogo(){
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

	protected function setMainWord( $key, $value ){
		$language	= $this->language->getLanguage();
		$fileName	= $this->config['path.locales'].$language.'/main.ini';
		$editor		= new FS_File_INI_Editor( $fileName );
		if( $value === $editor->getProperty( $key, 'main' ) )
			return FALSE;
		$editor->setProperty( $key, $value, 'main' );
		return TRUE;
	}

	public function setTitle(){
		if( strlen( trim( $title = $this->request->get( 'title' ) ) ) ){
			if( $this->setMainWord( 'title', $title ) )
				$this->messenger->noteSuccess( 'Der Titel wurde geändert.' );
		}
		$this->restart( NULL, TRUE );
	}

	protected function uploadImage( $upload ){
		if( !is_object( $upload ) )
			throw new InvalidArgumentException( 'Invalid upload given' );
		if( $upload->error === 4 )
			return NULL;
		try{
			$logicUpload	= new Logic_Upload( $this->env );
			$logicUpload->setUpload( $upload );
			$pathImages		= $this->config->get( 'path.images' ).'/logo/';
			$fileName		= $pathImages.Alg_ID::uuid().'.'.$logicUpload->getExtension();
			FS_Folder_Editor::createFolder( $pathImages );
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