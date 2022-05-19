<?php
class Hook_Info_File extends CMF_Hydrogen_Hook
{
	static public function onCollectNovelties( CMF_Hydrogen_Environment $env, $context, $module, $payload = [] )
	{
		$model		= new Model_Download_File( $env );
		$conditions	= array( 'uploadedAt' => '> '.( time() - 270 * 24 * 60 * 60 ) );
		$files		= $model->getAll( $conditions, array( 'uploadedAt' => 'DESC' ) );
		foreach( $files as $file ){
			$context->add( (object) array_merge( View_Helper_NewsList::$defaultAttributes, array(
				'module'	=> 'Info_Files',
				'type'		=> 'file',
				'typeLabel'	=> 'Datei',
				'id'		=> $file->downloadFolderId,
				'title'		=> $file->title,
				'timestamp'	=> $file->uploadedAt,
				'url'		=> './info/file/download/'.$file->downloadFolderId,
				'icon'		=> 'fa fa-fw fa-file-o',
			) ) );
		}
	}

	static public function onPageCollectNews( CMF_Hydrogen_Environment $env, $context, $module, $payload = [] )
	{
		$model		= new Model_Download_File( $env );
		$conditions	= array( 'uploadedAt' => '> '.( time() - 270 * 24 * 60 * 60 ) );
		$files		= $model->getAll( $conditions, array( 'uploadedAt' => 'DESC' ) );
		foreach( $files as $file ){
			$context->add( (object) array_merge( View_Helper_NewsList::$defaultAttributes, array(
				'module'	=> 'Info_Files',
				'type'		=> 'file',
				'typeLabel'	=> 'Datei',
				'id'		=> $file->downloadFileId,
				'title'		=> $file->title,
				'timestamp'	=> $file->uploadedAt,
				'url'		=> './info/file/download/'.$file->downloadFileId,
				'icon'		=> 'fa fa-fw fa-folder',
			) ) );
		}
	}
}
