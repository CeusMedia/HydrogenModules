<?php
class Hook_Info_Download extends CMF_Hydrogen_Hook
{
	public static function onCollectNovelties( CMF_Hydrogen_Environment $env, $context, $module, $payload = array() )
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

	public static function onPageCollectNews( CMF_Hydrogen_Environment $env, $context, $module, $payload = array() )
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