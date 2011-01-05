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

	public function link( $moduleId, $thenViewModule = NULL ){
		$config	= $this->env->getConfig();
		$model	= new Model_Module( $this->env );
		$path	= $model->getPath( $moduleId );
		$module	= $model->get( $moduleId );
		
		remark( 'moduleId: '.$moduleId );
//		print_m( $module );
		foreach( $module->files->classes as $class )
			$this->linkModuleFile( $moduleId, $class, 'classes/' );
		foreach( $module->files->templates as $template )
			$this->linkModuleFile( $moduleId, $template, $config->get( 'path.templates' ) );
		foreach( $module->files->locales as $locale )
			$this->linkModuleFile( $moduleId, $locale, $config->get( 'path.locales' ) );
		foreach( $module->files->scripts as $script )
			$this->linkModuleFile( $moduleId, $script, $config->get( 'path.javascripts' ) );

		$pathTheme	= $config->get( 'path.themes' ).$config->get( 'layout.theme' ).'/';
		foreach( $module->files->styles as $style )
			$this->linkModuleFile( $moduleId, $style, $pathTheme );

		remark( 'link( '.$module->file.', config/modules/'.$moduleId.'.xml);');
		if( file_exists( $path.'config.ini' ) )
			remark( 'copy( '.$path.'config.ini, config/modules/'.$moduleId.'.ini);');
		if( $thenViewModule )
			$this->redirect( 'admin/module', 'view', array( $moduleId ) );
		else
			$this->redirect( 'admin/module' );
	}

	public function copy( $moduleId ){
		$config	= $this->env->getConfig();
		$model	= new Model_Module( $this->env );
		$path	= $model->getPath( $moduleId );
		$module	= $model->get( $moduleId );

		remark( 'moduleId: '.$moduleId );
//		print_m( $module );
		foreach( $module->files->classes as $class )
			$this->copyModuleFile( $moduleId, $class, 'classes/' );
		foreach( $module->files->templates as $template )
			$this->copyModuleFile( $moduleId, $template, $config->get( 'path.templates' ) );
		foreach( $module->files->locales as $locale )
			$this->copyModuleFile( $moduleId, $locale, $config->get( 'path.locales' ) );
		foreach( $module->files->scripts as $script )
			$this->copyModuleFile( $moduleId, $script, $config->get( 'path.javascripts' ) );

		$pathTheme	= $config->get( 'path.themes' ).$config->get( 'layout.theme' ).'/';
		foreach( $module->files->styles as $style )
			$this->copyModuleFile( $moduleId, $style, $pathTheme );

		remark( 'copy( '.$module->file.', config/modules/'.$moduleId.'.xml);');
		if( file_exists( $path.'config.ini' ) )
			remark( 'copy( '.$path.'config.ini, config/modules/'.$moduleId.'.ini);');
		if( $thenViewModule )
			$this->redirect( 'admin/module', 'view', array( $moduleId ) );
		else
			$this->redirect( 'admin/module' );
	}

	public function uninstallModule( $moduleId ){
		$config	= $this->env->getConfig();
		$model	= new Model_Module( $this->env );
		$path	= $model->getPath( $moduleId );
		$module	= $model->get( $moduleId );

		remark( 'moduleId: '.$moduleId );
//		print_m( $module );
		foreach( $module->files->classes as $class )
			$this->unlinkModuleFile( $moduleId, $class, 'classes/' );
		foreach( $module->files->templates as $template )
			$this->unlinkModuleFile( $moduleId, $template, $config->get( 'path.templates' ) );
		foreach( $module->files->locales as $locale )
			$this->unlinkModuleFile( $moduleId, $locale, $config->get( 'path.locales' ) );
		foreach( $module->files->scripts as $script )
			$this->unlinkModuleFile( $moduleId, $script, $config->get( 'path.javascripts' ) );

		$pathTheme	= $config->get( 'path.themes' ).$config->get( 'layout.theme' ).'/';
		foreach( $module->files->styles as $style )
			$this->copyModuleFile( $moduleId, $style, $pathTheme );

		remark( 'unlink( config/modules/'.$moduleId.'.xml);');
		remark( 'unlink( config/modules/'.$moduleId.'.ini);');
		$this->redirect( 'admin/module', 'view', array( $moduleId ) );
	}

	protected function unlinkModuleFile( $moduleId, $fileName, $path )
	{
		$fileName	= $path.$fileName;
		if( file_exists( $fileName ) )
			unlink( $fileName );
	}

	protected function linkModuleFile( $moduleId, $fileName, $path ){
		$fileSource	= './modules/'.$moduleId.'/'.$path.$fileName;
		$fileTarget	= $path.$fileName;
		self::createPath( dirname( $fileTarget ) );
		remark( 'link('.$fileSource.','.$fileTarget.');' );
	}

	protected function copyModuleFile( $moduleId, $fileName, $path ){
		$fileSource	= './modules/'.$moduleId.'/'.$path.$fileName;
		$fileTarget	= $path.$fileName;
		self::createPath( dirname( $fileTarget ) );
		remark( 'copy('.$fileSource.','.$fileTarget.');' );
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
