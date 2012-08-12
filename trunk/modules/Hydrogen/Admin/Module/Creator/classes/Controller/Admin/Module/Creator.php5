<?php
class Controller_Admin_Module_Creator extends CMF_Hydrogen_Controller{								//  @todo	1) inherit from View_Admin_Module after cleanup

	protected function __onInit(){
		$this->request		= $this->env->getRequest();
		$this->messenger	= $this->env->getMessenger();
		$this->logic		= new Logic_Module_Creator( $this->env );
		$this->env->getPage()->addThemeStyle( 'site.admin.module.css' );
#		$this->env->getPage()->addThemeStyle( 'site.admin.module.creator.css' );
#		$this->env->getPage()->js->addUrl( $this->env->getConfig()->get( 'path.scripts' ).'site.admin.module.js' );	//  @todo	2) move to parent class after 1)
	}

	protected function create(){
		$model		= $this->logic->model;
		$this->addData( 'request', $this->request );
		$words		= (object) $this->getWords( 'index' );

		try{

			$title			= $this->request->get( 'add_title' );
			$description	= $this->request->get( 'add_description' );
			$version		= $this->request->get( 'add_version' );
			$moduleId		= $this->request->get( 'add_id' );
			$path			= $this->request->get( 'add_path' );
			$route			= $this->request->get( 'add_route' );


#			$this->logic->model->registerLocalFile( 'Users', 'class', 'Controller/Test.php5' );

			if( !strlen( $title ) )
				$this->messenger->noteError( $words->msgNoTitle );
			$modules	= $this->logic->model->getAll();
			foreach( $modules as $module )
				if( $module->title == $title )
					$this->messenger->noteError( $words->msgTitleExisting );
			if( in_array( $moduleId, array_keys( $modules ) ) )
				$this->messenger->noteError( $words->msgIdExisting );

			if( !$this->messenger->gotError() ){
				$this->logic->createLocalModule( $moduleId, $title, $description, $version, $route );
				$this->messenger->noteSuccess( $words->msgSuccessCreated );
				if( $this->request->get( 'add_scafold' ) ){
					$this->logic->scafoldLocalModule( $moduleId, $route );
					$this->messenger->noteSuccess( $words->msgSuccessScafold );
				}
	#			if( $request->get( 'add_import' ) )
	#				$this->logic->importModuleFiles( $moduleId );
	#				$this->messenger->noteSuccess( $this->words['add']['msgSuccessImported'] );
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
