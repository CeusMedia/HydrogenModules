<?php
class Controller_Admin_Module_Viewer extends CMF_Hydrogen_Controller{								//  @todo	1) inherit from View_Admin_Module after cleanup

	/**	@var	Logic_Module									$logic		Module logic instance */
	protected $logic;
	/** @var	CMF_Hydrogen_Environment_Resource_Messenger		$messenger	Messenger Object */
	protected $messenger;
#	/**	@var	Net_HTTP_Request_Receiver						$request	HTTP Request Object */
#	protected $request;

	protected function __onInit(){
#		$this->request		= $this->env->getRequest();
		$this->messenger	= $this->env->getMessenger();
		$this->logic		= Logic_Module::getInstance( $this->env );
#		$this->categories	= $this->logic->getCategories();
		$this->env->getPage()->addThemeStyle( 'site.admin.module.css' );
#		$this->env->getPage()->addThemeStyle( 'site.admin.module.viewer.css' );
#		$this->env->getPage()->js->addUrl( $this->env->getConfig()->get( 'path.scripts' ).'site.admin.module.js' );	//  @todo	2) move to parent class after 1)
	}

	public function ajaxEditConfig( $moduleId, $key, $value ){
		$fileName	= $this->env->pathConfig.'modules/'.$moduleId.'.xml';
		$module		= XML_ElementReader::readFile( $fileName );
		foreach( $module->config as $pair )
			if( $pair->getAttribute( 'name' ) == $key )
				$pair->{0}	= $value;
		return File_Editor::save( $fileName, $module->asXML() );
	}

	public function index( $moduleId = NULL ){
#		$c	= new Alg_Time_Clock();
		if( $moduleId )
			return $this->redirect( 'admin/module/viewer', 'view', array( $moduleId ) );
		$this->addData( 'sources', $this->logic->listSources() );
		$this->addData( 'categories', $this->logic->getCategories() );
		$this->addData( 'modules', $this->logic->model->getAll() );
#		remark( $c->stop( 3, 1 ).' ms' );
#		die;
	}

	public function reload( $moduleId ){
		$request	= $this->env->getRequest();
		$cache		= $this->env->getCache();
		$words		= (object) $this->getWords( 'reload' );
		$module		= $this->logic->getModule( $moduleId );
		if( !$module ){
			$this->messenger->noteError( 'Invalid module ID "'.$moduleId.'".' );
			$this->restart( './admin/module/viewer' );
		}
		switch( (int) $request->get( 'stage' ) ){
			case 0:
				$version	= $module->versionAvailable;
				if( $cache->has( $cacheKey = 'Modules/'.$module->source.'/'.$moduleId ) )			//  module has been cached
					$cache->remove( $cacheKey );													//  remove module from cache
				if( $cache->has( $cacheKey = 'Sources/'.$module->source ) )							//  module source has been cached
					$cache->remove( $cacheKey );													//  remove whole module source from cache
				$this->logic->invalidateFileCache( $this->env->getRemote() );
				$this->logic->invalidateFileCache( $this->env );
				$this->restart( './admin/module/viewer/reload/'.$moduleId.'?stage=1&oldVersion='.$version );
				break;
			case 1:
				$title		= $module->title;
				$newVersion	= $module->versionAvailable;											//  take version number of source module
				if( version_compare( $module->versionInstalled, $module->versionAvailable ) > 0  )	//  local version is newer
					$newVersion	= $module->versionInstalled;										//  take version number of local module
				
				$oldVersion	= $request->get( 'oldVersion' );										//  get old version from request
				if( $newVersion != $oldVersion )													//  if version numbers differ
					$this->messenger->noteSuccess( $words->msgNewVersion, $title, $newVersion );	//  inform about new version
				else																				//  otherwise if no module update
					$this->messenger->noteNotice( $words->msgNoNewVersion, $title, $newVersion );	//	note not changes
				$this->restart( './admin/module/viewer/view/'.$moduleId );
				break;
		}
	}

	public function view( $moduleId ){
		$words		= (object) $this->getWords( 'msg' );
		$hasUpdate	= $this->logic->checkForUpdate( $moduleId );
		$module		= $this->logic->model->get( $moduleId );		
		if( $hasUpdate )
			$this->messenger->noteNotice( $words->updateAvailable, $module->versionInstalled, $module->versionAvailable );
		$module->neededModules		= $this->logic->model->getNeededModulesWithStatus( $moduleId );
		$module->neededByModules	= $this->logic->model->getNeedingModulesWithStatus( $moduleId );
		$module->supportedModules	= $this->logic->model->getSupportedModulesWithStatus( $moduleId );
		$module->supportedByModules	= $this->logic->model->getSupportingModulesWithStatus( $moduleId );
		$this->addData( 'hasUpdate', $hasUpdate );
		$this->addData( 'isInstalled', (int) $this->logic->isInstalled( $module ) );
		$this->addData( 'module', $module );
		$this->addData( 'moduleId', $moduleId );
		$this->addData( 'modules', $this->logic->model->getAll() );
	}

	public function viewCode( $moduleId, $type, $fileName ){
		$fileName	= base64_decode( $fileName );
		$pathModule	= $this->logic->getModulePath( $moduleId );
		$pathFile	= '';
		switch( $type ){
			case 'class':
				$pathFile	= 'classes/';
				break;
			case 'locale':
				$pathFile	= 'locales/';
				break;
			case 'script':
				$pathFile	= 'js/';
				break;
			case 'style':
				$pathFile	= 'css/';
				break;
			case 'template':
				$pathFile	= 'templates/';
				break;
		}
		if( !file_exists( $pathModule.$pathFile.$fileName ) )
			die( 'Invalid file: '.$pathModule.$pathFile.$fileName );
		
		$this->addData( 'moduleId', $moduleId );
		$this->addData( 'type', $type );
		$this->addData( 'fileName', $fileName );
		$this->addData( 'filePath', $pathModule.$pathFile.$fileName );
		$this->addData( 'pathFile', $pathFile );
		$this->addData( 'pathModule', $pathModule );
		$this->addData( 'content', File_Reader::load( $pathModule.$pathFile.$fileName ) );
	}
}
?>
