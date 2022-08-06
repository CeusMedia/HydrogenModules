<?php

use CeusMedia\Bootstrap\Button as BootstrapButton;
use CeusMedia\Bootstrap\Button\Group as BootstrapButtonGroup;
use CeusMedia\Bootstrap\Button\Link as BootstrapLinkButton;
use CeusMedia\Bootstrap\Icon as BootstrapIcon;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

class View_Info_File extends View
{
	public function index()
	{
	}

	public function editFile()
	{
	}

	public function editFolder()
	{
	}

	public function view()
	{
	}

	public static function renderPosition( Environment $env, $folderId, $search ): string
	{
		$steps		= self::getStepsFromFolderId( $env, $folderId );
		$folderPath	= self::getPathFromFolderId( $env, $folderId );
		$way		= '';
		$parts		= $folderPath ? explode( "/", '/'.trim( $folderPath, " /\t" ) ) : array( '' );
		$iconHome	= new BootstrapIcon( 'fa fa-fw fa-home', !$folderPath );
		$buttonHome	= new BootstrapLinkButton( './info/file/index', $iconHome );
		if( !$folderPath && !$search )
			$buttonHome	= new BootstrapButton( $iconHome, 'btn-inverse', NULL, TRUE );
		$buttons	= array( $buttonHome );
		foreach( $steps as $nr => $stepFolder ){
			$way		.= strlen( $stepFolder->title ) ? $stepFolder->title.'/' : '';
			$isCurrent	= $folderId === (int) $stepFolder->downloadFolderId;
			$url		= './info/file/index/'.$stepFolder->downloadFolderId;
			$icon		= new BootstrapIcon( 'fa fa-fw fa-folder-open', $isCurrent );
			$class		= $isCurrent ? 'btn-inverse' : NULL;
			$buttons[]	= new BootstrapLinkButton( $url, $stepFolder->title, $class, $icon, $isCurrent );
		}
		$position	= new BootstrapButtonGroup( $buttons );
		$position->setClass( 'position-bar' );
		return $position;
	}

	protected function __onInit()
	{
		$this->env->getPage()->addThemeStyle( 'module.info.files.css' );
	}

	protected static function getStepsFromFolderId( Environment $env, $folderId ): array
	{
		$model	= new Model_Download_Folder( $env );
		$steps		= [];
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

	protected static function getPathFromFolderId( Environment $env, $folderId ): string
	{
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
