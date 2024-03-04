<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Environment\Resource\Language as LanguageResource;

class Job_Info_Files extends Job_Abstract
{
	protected Dictionary $config;
	protected Dictionary $options;
	protected LanguageResource $language;
	protected Model_Download_Folder $modelFolder;
	protected Model_Download_File $modelFile;

//	protected array $words;

	/**
	 *	@return	void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->config		= $this->env->getConfig();												//  get app config
		$this->language		= $this->env->getLanguage();											//  get language support
		$this->options		= $this->config->getAll( 'module.info_files.', TRUE );					//  get module options for job
//		$this->words		= (object) $this->language->getWords( 'info/files' );					//  get module words

		$this->modelFolder	= new Model_Download_Folder( $this->env );
		$this->modelFile	= new Model_Download_File( $this->env );
	}

	public function migrate(): void
	{
		$count	= $this->migrateFilesInFolderByFolderId( 0, 'contents/files/' );
		$this->out( 'Migrated '.$count.' files.' );
	}

	protected function migrateFilesInFolderByFolderId( int|string $folderId, string $path, int $level = 0 ): int
	{
		$count	= 0;
		$files	= $this->modelFile->getAllByIndex( 'downloadFolderId', $folderId );
		foreach( $files as $file ){
			if( $file->size == 0 ){
				if( file_exists( $path.$file->title ) ){
					$this->out( 'File found and migrated: '.$path.$file->title );
					$data	= ['size' => filesize( $path.$file->title )];
					$count	+= $this->modelFile->edit( $file->downloadFileId, $data );
				}
				else
					$this->out( 'File NOT found: '.$path.$file->title );
			}
		}
		$folders	= $this->modelFolder->getAllByIndex( 'parentId', $folderId );
		foreach( $folders as $folder ){
			$count	+= $this->migrateFilesInFolderByFolderId(
				$folder->downloadFolderId,
				$path.$folder->title.'/',
				$level + 1
			);
		}
		return $count;
	}
}
