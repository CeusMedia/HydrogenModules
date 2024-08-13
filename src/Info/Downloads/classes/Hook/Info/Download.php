<?php

use CeusMedia\HydrogenFramework\Hook;

class Hook_Info_Download extends Hook
{
	public function onCollectNovelties(): void
	{
		$options	= $this->env->getConfig()->getAll( 'module.info_downloads.', TRUE );
		$logic		= new Logic_Download( $this->env, $options->get( 'path' ) );
		$conditions	= ['uploadedAt' => '> '.( time() - 270 * 24 * 60 * 60 )];
		$files		= $logic->findFiles( $conditions, ['uploadedAt' => 'DESC'] );
		foreach( $files as $file ){
			$this->context->add( (object) [
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

	public function onPageCollectNews(): void
	{
		$options	= $this->env->getConfig()->getAll( 'module.info_files.', TRUE );
		$logic		= new Logic_Download( $this->env, $options->get( 'path' ) );
		$conditions	= ['uploadedAt' => '> '.( time() - 270 * 24 * 60 * 60 )];
		$files		= $logic->findFiles( $conditions, ['uploadedAt' => 'DESC'] );
		foreach( $files as $file ){
			$this->context->add( (object) [
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
