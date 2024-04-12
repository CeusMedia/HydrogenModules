<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Info_File extends Hook
{
	static public function onCollectNovelties( Environment $env, $context, $module, $payload = [] )
	{
		$model		= new Model_Download_File( $env );
		$conditions	= ['uploadedAt' => '> '.( time() - 270 * 24 * 60 * 60 )];
		$files		= $model->getAll( $conditions, ['uploadedAt' => 'DESC'] );
		foreach( $files as $file ){
			$context->add( (object) array_merge( View_Helper_NewsList::$defaultAttributes, [
				'module'	=> 'Info_Files',
				'type'		=> 'file',
				'typeLabel'	=> 'Datei',
				'id'		=> $file->downloadFolderId,
				'title'		=> $file->title,
				'timestamp'	=> $file->uploadedAt,
				'url'		=> './info/file/download/'.$file->downloadFolderId,
				'icon'		=> 'fa fa-fw fa-file-o',
			] ) );
		}
	}

	static public function onPageCollectNews( Environment $env, $context, $module, $payload = [] )
	{
		$model		= new Model_Download_File( $env );
		$conditions	= ['uploadedAt' => '> '.( time() - 270 * 24 * 60 * 60 )];
		$files		= $model->getAll( $conditions, ['uploadedAt' => 'DESC'] );
		foreach( $files as $file ){
			$context->add( (object) array_merge( View_Helper_NewsList::$defaultAttributes, [
				'module'	=> 'Info_Files',
				'type'		=> 'file',
				'typeLabel'	=> 'Datei',
				'id'		=> $file->downloadFileId,
				'title'		=> $file->title,
				'timestamp'	=> $file->uploadedAt,
				'url'		=> './info/file/download/'.$file->downloadFileId,
				'icon'		=> 'fa fa-fw fa-folder',
			] ) );
		}
	}
}
