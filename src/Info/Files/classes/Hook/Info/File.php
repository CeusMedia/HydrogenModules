<?php

use CeusMedia\HydrogenFramework\Hook;

class Hook_Info_File extends Hook
{
	public function onCollectNovelties(): void
	{
		$options	= $this->env->getConfig()->getAll( 'module.info_files.', TRUE );
		$logic		= new Logic_Download( $this->env, $options->get( 'path' ) );
		$conditions	= ['uploadedAt' => '> '.( time() - 270 * 24 * 60 * 60 )];
		$files		= $logic->findFiles( $conditions, ['uploadedAt' => 'DESC'] );
		foreach( $files as $file ){
			$this->context->add( (object) array_merge( View_Helper_NewsList::$defaultAttributes, [
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

	public function onPageCollectNews(): void
	{
		$options	= $this->env->getConfig()->getAll( 'module.info_files.', TRUE );
		$logic		= new Logic_Download( $this->env, $options->get( 'path' ) );
		$conditions	= ['uploadedAt' => '> '.( time() - 270 * 24 * 60 * 60 )];
		$files		= $logic->findFiles( $conditions, ['uploadedAt' => 'DESC'] );
		foreach( $files as $file ){
			$this->context->add( (object) array_merge( View_Helper_NewsList::$defaultAttributes, [
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
