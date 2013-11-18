<?php
class Controller_Admin_Module_Creator extends CMF_Hydrogen_Controller{								//  @todo	1) inherit from View_Admin_Module after cleanup

	/**	@var	Logic_Module									$logic		Module logic instance */
	protected $logic;
	/** @var	CMF_Hydrogen_Environment_Resource_Messenger		$messenger	Messenger Object */
	protected $messenger;
	/**	@var	Net_HTTP_Request_Receiver						$request	HTTP Request Object */
	protected $request;

	protected function __onInit(){
		$this->request		= $this->env->getRequest();
		$this->messenger	= $this->env->getMessenger();
		$this->logic		= Logic_Module::getInstance( $this->env );
		$this->env->getPage()->addThemeStyle( 'site.admin.module.css' );
#		$this->env->getPage()->addThemeStyle( 'site.admin.module.creator.css' );
#		$this->env->getPage()->js->addUrl( $this->env->getConfig()->get( 'path.scripts' ).'site.admin.module.js' );	//  @todo	2) move to parent class after 1)
		if( !$this->env->getSession()->get( 'instanceId' ) ){
			$words	= $this->getWords( 'msg' );
			$this->messenger->noteError( $words['noInstanceSelected'] );
			$this->restart( 'admin/module/viewer' );
		}
	}

	protected function create(){
		$model		= $this->logic->model;
		$this->addData( 'request', $this->request );
		$words		= (object) $this->getWords( 'msg' );

		try{
			$title			= $this->request->get( 'add_title' );
			$description	= $this->request->get( 'add_description' );
			$version		= $this->request->get( 'add_version' );
			$moduleId		= $this->request->get( 'add_id' );
			$path			= $this->request->get( 'add_path' );
			$route			= $this->request->get( 'add_route' );

#			$this->logic->model->registerLocalFile( 'Users', 'class', 'Controller/Test.php5' );

			if( !strlen( $title ) )
				$this->messenger->noteError( $words->noTitle );
			$modules	= $this->logic->model->getAll();
			foreach( $modules as $module )
				if( $module->title == $title )
					$this->messenger->noteError( $words->titleExisting );
			if( in_array( $moduleId, array_keys( $modules ) ) )
				$this->messenger->noteError( $words->idExisting );

			if( !$this->messenger->gotError() ){
				$this->logic->createLocalModule( $moduleId, $title, $description, $version, $route );
				$this->messenger->noteSuccess( $words->moduleCreated );
				if( $this->request->get( 'add_scafold' ) ){
					$this->logic->scafoldLocalModule( $moduleId, $route );
					$this->messenger->noteSuccess( $words->scafoldCreated );
				}
	#			if( $request->get( 'add_import' ) )
	#				$this->logic->importModuleFiles( $moduleId );
	#				$this->messenger->noteSuccess( $words['moduleImported'] );
				return $moduleId;
			}
		}
		catch( Exception $e ){
			$this->messenger->noteFailure( $e->getMessage() );
		}
	}

	public function index(){
		$modules	= $this->logic->model->getAll();
		if( $this->request->get( 'create' ) ){
			$moduleId	= $this->create();
			if( $moduleId )
				$this->restart( './admin/module/editor/'.$moduleId );
		}
	}
}
?>
