<?php
class Controller_Admin_Module_Viewer extends CMF_Hydrogen_Controller{								//  @todo	1) inherit from View_Admin_Module after cleanup

	/**	@var	Logic_Module		$logic		Module logic instance */
	protected $logic;
	
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
		$module	= $this->logic->getModule( $moduleId );
		if( !$module ){
			$this->messenger->noteError( 'Invalid module ID "'.$moduleId.'".' );
			$this->restart( './admin/module/viewer' );
		}
		$request		= $this->env->getRequest();
		switch( (int) $request->get( 'stage' ) ){
			case 0:
				$version	= $module->version;
				$version	= "0.1";
				$this->restart( './admin/module/viewer/reload/'.$moduleId.'?stage=1&oldVersion='.$version );
				break;
			case 1:
				$version	= $request->get( 'oldVersion' );
				$path		= 'Modules/'.$module->source.'/'.$moduleId;
				if( $this->env->getCache()->has( $path ) )
					$this->env->getCache()->remove( $path );
				@unlink( 'config/modules/cache/Sources/'.$module->source );									//  @todo	kriss: extract
				$this->restart( './admin/module/viewer/reload/'.$moduleId.'?stage=2&oldVersion='.$version );
				break;
			case 2:
				$version	= $request->get( 'oldVersion' );
				if( $version != $module->version )
					$this->messenger->noteSuccess( 'Modul "'.$module->title.'" neu geladen und <b>neue Version '.$module->version.'</b> gefunden.' );
				else
					$this->messenger->noteNotice( 'Modul "'.$module->title.'" neu geladen aber <b>keine neue Version</b> gefunden.' );
				$this->restart( './admin/module/viewer/view/'.$moduleId );
				break;
		}
	}

	public function view( $moduleId ){
		$module		= $this->logic->model->get( $moduleId );
		$module->neededModules		= $this->logic->model->getNeededModulesWithStatus( $moduleId );
		$module->supportedModules	= $this->logic->model->getSupportedModulesWithStatus( $moduleId );
		$this->addData( 'module', $module );
		$this->addData( 'moduleId', $moduleId );
		$this->addData( 'modules', $this->logic->model->getAll() );
	}

	public function uninstall( $moduleId, $verbose = TRUE ){
		$words		= (object) $this->getWords( 'msg' );
		$module		= $this->logic->getModule( $moduleId );
		if( !$module )
			$this->restart( './admin/module/editor' );
		if( $this->logic->uninstallModule( $moduleId, $verbose ) ){
			$this->messenger->noteSuccess( $words->moduleUninstalled, $module->title );
			if( $module->type == Model_Module::TYPE_CUSTOM )
				$this->restart( './admin/module/viewer' );
		}
		else
			$this->messenger->noteError( $this->words['msg']['moduleNotUninstalled'], $module->title );
		$this->restart( './admin/module/viewer/index/'.$moduleId );
	}

	public function viewCode( $moduleId, $type, $fileName ){
		$fileName	= base64_decode( $fileName );
		$pathModule	= $this->logic->getModulePath( $moduleId );
		$pathFile	= '';
		$xmpClass	= '';
		switch( $type ){
			case 'class':
				$pathFile	= 'classes/';
				$xmpClass	= 'php';
				break;
			case 'locale':
				$pathFile	= 'locales/';
				$xmpClass	= 'ini';
				break;
			case 'script':
				$pathFile	= 'js/';
				$xmpClass	= 'js';
				break;
			case 'style':
				$pathFile	= 'css/';
				$xmpClass	= 'css';
				break;
			case 'template':
				$pathFile	= 'templates/';
				$xmpClass	= 'php';
				break;
		}
		if( !file_exists( $pathModule.$pathFile.$fileName ) )
			die( 'Invalid file: '.$pathModule.$pathFile.$fileName );
		$content	= File_Reader::load( $pathModule.$pathFile.$fileName );
		$code		= UI_HTML_Tag::create( 'pre', htmlentities( $content ), array( 'class' => 'code '.$xmpClass ) );
		$body		= '<h2>'.$moduleId.' - '.$fileName.'</h2>'.$code;
		$page		= new UI_HTML_PageFrame();
		$page->addStylesheet( 'css/reset.css' );
		$page->addStylesheet( 'css/typography.css' );
		$page->addStylesheet( 'css/xmp.formats.css' );
		$page->addBody( $body );
		print( $page->build( array( 'style' => 'margin: 1em' ) ) );
		exit;
	}
}
?>
