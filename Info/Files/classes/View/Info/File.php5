<?php
class View_Info_File extends CMF_Hydrogen_View{

	protected function __onInit(){
		$this->env->getPage()->addThemeStyle( 'module.info.files.css' );
	}

	public function index(){}

	public function editFile(){}

	public function editFolder(){}

	public function view(){}


	static public function renderPosition( CMF_Hydrogen_Environment $env, $folderId, $search ){
		$steps		= self::getStepsFromFolderId( $env, $folderId );
		$folderPath	= self::getPathFromFolderId( $env, $folderId );
		$way		= '';
		$parts		= $folderPath ? explode( "/", '/'.trim( $folderPath, " /\t" ) ) : array( '' );
		$iconHome	= new \CeusMedia\Bootstrap\Icon( 'fa fa-fw fa-home', !$folderPath );
		$buttonHome	= new \CeusMedia\Bootstrap\LinkButton( './info/file/index', $iconHome );
		if( !$folderPath && !$search )
			$buttonHome	= new \CeusMedia\Bootstrap\Button( $iconHome, 'btn-inverse', NULL, TRUE );
		$buttons	= array( $buttonHome );
		foreach( $steps as $nr => $stepFolder ){
			$way		.= strlen( $stepFolder->title ) ? $stepFolder->title.'/' : '';
			$isCurrent	= $folderId === (int) $stepFolder->downloadFolderId;
			$url		= './info/file/index/'.$stepFolder->downloadFolderId;
			$icon		= new \CeusMedia\Bootstrap\Icon( 'fa fa-fw fa-folder-open', $isCurrent );
			$class		= $isCurrent ? 'btn-inverse' : NULL;
			$buttons[]	= new \CeusMedia\Bootstrap\LinkButton( $url, $stepFolder->title, $class, $icon, $isCurrent );
		}
		$position	= new \CeusMedia\Bootstrap\ButtonGroup( $buttons );
		$position->setClass( 'position-bar' );
		return $position;
	}

	static protected function getStepsFromFolderId( CMF_Hydrogen_Environment $env, $folderId ){
		$model	= new Model_Download_Folder( $env );
		$steps		= array();
		while( $folderId ){
			$folder	= $model->get( $folderId );
			if( !$folder )
				throw new RuntimeException( 'Invalid folder ID: %s', $folderId );
			$steps[$folder->downloadFolderId]	= $folder;
			$folderId	= $folder->parentId;
		}
		$steps	= array_reverse( $steps );
		return $steps;
	}

	static protected function getPathFromFolderId( CMF_Hydrogen_Environment $env, $folderId ){
		$model	= new Model_Download_Folder( $env );
		$path	= '';
		while( $folderId ){
			$folder	= $model->get( $folderId );
			if( !$folder )
				throw new RuntimeException( 'Invalid folder ID: %s', $folderId );
			$path		= $folder->title.'/'.$path;
			$folderId	= $folder->parentId;
		}
		return $path;
	}
}
?>
