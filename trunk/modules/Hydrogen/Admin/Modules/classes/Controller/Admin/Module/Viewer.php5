<?php
class Controller_Admin_Module_Viewer extends CMF_Hydrogen_Controller{								//  @todo	1) inherit from View_Manage_Module after cleanup

	protected function __onInit(){
#		$this->request		= $this->env->getRequest();
		$this->messenger	= $this->env->getMessenger();
		$this->logic		= new Logic_Module( $this->env );
#		$this->categories	= $this->logic->getCategories();
		$this->env->getPage()->addThemeStyle( 'site.admin.module.css' );
#		$this->env->getPage()->addThemeStyle( 'site.admin.module.viewer.css' );
		$this->env->getPage()->js->addUrl( $this->env->getConfig()->get( 'path.scripts' ).'site.admin.module.js' );	//  @todo	2) move to parent class after 1)
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
	
	public function view( $moduleId ){
		$module		= $this->logic->model->get( $moduleId );
		$module->neededModules		= $this->logic->model->getNeededModulesWithStatus( $moduleId );
		$module->supportedModules	= $this->logic->model->getSupportedModulesWithStatus( $moduleId );
		$this->addData( 'module', $module );
		$this->addData( 'moduleId', $moduleId );
		$this->addData( 'modules', $this->logic->model->getAll() );
		$this->addData( 'pathModule', $this->logic->model->getPath( $moduleId ) );
	}

	public function uninstall( $moduleId, $verbose = TRUE ){
		$words	= $this->getWords( 'msg' );
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
