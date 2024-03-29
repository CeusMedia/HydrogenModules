<?php
use CeusMedia\Common\FS\File\Writer as FileWriter;
use CeusMedia\Common\FS\Folder\Editor as FolderEditor;

class Logic_Module_Creator extends Logic_Module{
	
	public function createLocalModule( $moduleId, $title, $description = NULL, $version = NULL, $route = NULL ){
		$data	= [
			'title'			=> $title,
			'description'	=> $description,
			'version'		=> $version,
			'route'			=> $route
		];
		$xml	= UI_Template::render( 'templates/admin/module/creator/module.xml.tmpl', $data );
		return (bool) FileWriter::save( $path.$moduleId.'.xml', $xml );
	}

	public function scafoldLocalModule( $moduleId, $route ){

		$language	= $this->env->getConfig()->get( 'locale.default' ).'/';
		if( !$language )
			$language	= 'en/';

		if( !trim( $route ) )
			throw new InvalidArgumentException( 'Route cannot by empty' );
		$folders	= explode( "/", $route );
		$className	= ucfirst( array_pop( $folders ) );

		$path	= "";
		if( $folders ){
			$path	= "";
			foreach( $folders as $folder ){
				$path	.= ucfirst( $folder )."/";
				FolderEditor::createFolder( $this->env->pathApp.'classes/Controller/'.$path, 0770 );
				FolderEditor::createFolder( $this->env->pathApp.'classes/View/'.$path, 0770 );
				FolderEditor::createFolder( $this->env->pathApp.'templates/'.strtolower( $path ), 0770 );
				FolderEditor::createFolder( $this->env->pathApp.'locales/'.$language.strtolower( $path ), 0770 );
			}
		}
		FolderEditor::createFolder( $this->env->pathApp.'templates/'.strtolower( $path ).strtolower( $className ), 0770 );
		if( !file_exists( $this->env->pathApp.'classes/Logic' ) )
			FolderEditor::createFolder( $this->env->pathApp.'classes/Logic', 0770 );
		$classPath	= $path.$className;
		$tmplFile	= strtolower( $classPath ).'/index.php';
		$localFile	= strtolower( $path ).strtolower( $className ).'.ini';
		$classKey	= str_replace( '/', '_', $classPath );
		$data	= [
			'moduleId'	=> $moduleId,
			'className'	=> $className,
			'classPath'	=> $classPath,
			'classKey'	=> $classKey,
			'tmplFile'	=> $tmplFile,
		];
		print_m( $data );
		
		$fileLogic		= $this->env->pathApp.'classes/Logic/'.$className.'.php5';
		$fileModel		= $this->env->pathApp.'classes/Model/'.$className.'.php5';
		$fileController	= $this->env->pathApp.'classes/Controller/'.$classPath.'.php5';
		$fileView		= $this->env->pathApp.'classes/View/'.$classPath.'.php5';
		$fileTemplate	= $this->env->pathApp.'templates/'.$tmplFile;
		$fileLocale		= $this->env->pathApp.'locales/'.$language.strtolower( $classPath).'.ini';
		$codeLogic		= UI_Template::render( 'templates/admin/module/creator/logic.tmpl', $data );
		$codeModel		= UI_Template::render( 'templates/admin/module/creator/model.tmpl', $data );
		$codeController	= UI_Template::render( 'templates/admin/module/creator/controller.tmpl', $data );
		$codeView		= UI_Template::render( 'templates/admin/module/creator/view.tmpl', $data );
		$codeTemplate	= UI_Template::render( 'templates/admin/module/creator/template.tmpl', $data );
		$codeLocal		= UI_Template::render( 'templates/admin/module/creator/locale.tmpl', $data );

		$this->model->registerLocalFile( $moduleId, 'class', 'Logic/'.$className.'.php5' );
		$this->model->registerLocalFile( $moduleId, 'class', 'Model/'.$className.'.php5' );
		$this->model->registerLocalFile( $moduleId, 'class', 'Controller/'.$classPath.'.php5' );
		$this->model->registerLocalFile( $moduleId, 'class', 'View/'.$classPath.'.php5' );
		$this->model->registerLocalFile( $moduleId, 'template', $tmplFile );
		$this->model->registerLocalFile( $moduleId, 'locale', $language.strtolower( $classPath).'.ini' );

		FileWriter::save( $fileLogic, $codeLogic );
		FileWriter::save( $fileModel, $codeModel );
		FileWriter::save( $fileController, $codeController );
		FileWriter::save( $fileView, $codeView );
		FileWriter::save( $fileTemplate, $codeTemplate );
		FileWriter::save( $fileLocale, $codeLocal );
	}
}
?>
