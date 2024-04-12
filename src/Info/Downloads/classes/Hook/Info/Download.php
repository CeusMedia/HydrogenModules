<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Info_Download extends Hook
{
	public static function onCollectNovelties( Environment $env, $context, $module, $payload = [] ): void
	{
		$model		= new Model_Download_File( $env );
		$conditions	= ['uploadedAt' => '> '.( time() - 270 * 24 * 60 * 60 )];
		$files		= $model->getAll( $conditions, ['uploadedAt' => 'DESC'] );
		foreach( $files as $file ){
			$context->add( (object) [
				'module'	=> 'Info_Downloads',
				'type'		=> 'file',
				'typeLabel'	=> 'Datei',
				'id'		=> $file->downloadFolderId,
				'title'		=> $file->title,
				'timestamp'	=> $file->uploadedAt,
				'url'		=> './info/download/download/'.$file->downloadFolderId,
			] );
		}
	}

	public static function onPageCollectNews( Environment $env, $context, $module, $payload = [] ): void
	{
		$model		= new Model_Download_File( $env );
		$conditions	= ['uploadedAt' => '> '.( time() - 270 * 24 * 60 * 60 )];
		$files		= $model->getAll( $conditions, ['uploadedAt' => 'DESC'] );
		foreach( $files as $file ){
			$context->add( (object) [
				'module'	=> 'Info_Downloads',
				'type'		=> 'file',
				'typeLabel'	=> 'Datei',
				'id'		=> $file->downloadFolderId,
				'title'		=> $file->title,
				'timestamp'	=> $file->uploadedAt,
				'url'		=> './info/download/download/'.$file->downloadFolderId,
			] );
		}
	}
}
