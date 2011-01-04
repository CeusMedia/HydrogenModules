<?php
class Controller_Admin_Module extends CMF_Hydrogen_Controller{
	public function index(){
		$model	= new Model_Module( $this->env );
		$this->addData( 'modules', $model->getAll() );
		$this->addData( 'modulesAvailable', $model->getAvailable() );
		$this->addData( 'modulesInstalled', $model->getInstalled() );
		$this->addData( 'modulesNotInstalled', $model->getNotInstalled() );
	}

	public function view( $moduleId ){
		$model	= new Model_Module( $this->env );
		$this->addData( 'module', $model->get( $moduleId ) );
	}

	public function install( $moduleId, $thenViewModule = NULL ){
		$config	= $this->env->getConfig();
		$model	= new Model_Module( $this->env );
		$module	= $model->get( $moduleId );
		foreach( $module->links->classes as $class )
			$this->linkModuleFile( $moduleId, $class, 'classes/' );
		foreach( $module->links->templates as $template )
			$this->linkModuleFile( $moduleId, $template, $config->get( 'path.templates' ) );
		foreach( $module->links->locales as $locale )
			$this->linkModuleFile( $moduleId, $locale, $config->get( 'path.locales' ) );
		foreach( $module->links->scripts as $script )
			$this->linkModuleFile( $moduleId, $script, $config->get( 'path.javascripts' ) );

		$pathTheme	= $config->get( 'path.themes' ).$config->get( 'layout.theme' ).'/';
		foreach( $module->links->styles as $style )
			$this->linkModuleFile( $moduleId, $style, $pathTheme );
		remark( 'copy( '.$module->file.', config/modules/'.$moduleId.'.xml);');
		remark( 'moduleId: '.$moduleId );
		print_m( $module );
		$this->redirect( 'admin/module', 'view', array( $moduleId ) );
	}

	protected function linkModuleFile( $moduleId, $fileName, $path ){
		$fileSource	= './modules/'.$moduleId.'/'.$path.$fileName;
		$fileTarget	= $path.$fileName;
		self::createPath( dirname( $fileTarget ) );
		remark( 'link('.$fileSource.','.$fileTarget.');' );
	}

	/**
	 *	Creates a Path by creating all Path Steps.
	 *	@access		protected
	 *	@param		string		$path				Path to create
	 *	@return		void
	 */
	protected static function createPath( $path )
	{
		$dirname	= dirname( $path );
		if( file_exists( $path ) && is_dir( $path ) )
			return;
		$hasParent	= file_exists( $dirname ) && is_dir( $dirname );
		if( $dirname != "./" && !$hasParent )
			self::createPath( $dirname );
		remark( 'createPath('.$path.');' );
	//	return mkdir( $path );
	}
}
?>
