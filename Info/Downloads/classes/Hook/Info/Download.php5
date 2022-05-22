<?php

use CeusMedia\HydrogenFramework\Environment;

class Hook_Info_Download extends CMF_Hydrogen_Hook
{
	public static function onCollectNovelties( Environment $env, $context, $module, $payload = [] )
	{
		$model		= new Model_Download_File( $env );
		$conditions	= array( 'uploadedAt' => '> '.( time() - 270 * 24 * 60 * 60 ) );
		$files		= $model->getAll( $conditions, array( 'uploadedAt' => 'DESC' ) );
		foreach( $files as $file ){
			$context->add( (object) array(
				'module'	=> 'Info_Downloads',
				'type'		=> 'file',
				'typeLabel'	=> 'Datei',
				'id'		=> $file->downloadFolderId,
				'title'		=> $file->title,
				'timestamp'	=> $file->uploadedAt,
				'url'		=> './info/download/download/'.$file->downloadFolderId,
			) );
		}
	}

	public static function onPageCollectNews( Environment $env, $context, $module, $payload = [] )
	{
		$model		= new Model_Download_File( $env );
		$conditions	= array( 'uploadedAt' => '> '.( time() - 270 * 24 * 60 * 60 ) );
		$files		= $model->getAll( $conditions, array( 'uploadedAt' => 'DESC' ) );
		foreach( $files as $file ){
			$context->add( (object) array(
				'module'	=> 'Info_Downloads',
				'type'		=> 'file',
				'typeLabel'	=> 'Datei',
				'id'		=> $file->downloadFolderId,
				'title'		=> $file->title,
				'timestamp'	=> $file->uploadedAt,
				'url'		=> './info/download/download/'.$file->downloadFolderId,
			) );
		}
	}
}
