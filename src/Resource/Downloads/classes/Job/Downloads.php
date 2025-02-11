<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Environment\Resource\Language as LanguageResource;

class Job_Downloads extends Job_Abstract
{
	protected Logic_Download $logic;
	protected Dictionary $config;
	protected Dictionary $options;
	protected LanguageResource $language;
	protected Model_Download_Folder $modelFolder;
	protected Model_Download_File $modelFile;
//	protected array $words;

	public function migrate(): void
	{
		$count	= $this->migrateFilesInFolderByFolderId( 0, 'contents/files/' );
		$this->out( 'Migrated '.$count.' files.' );
	}

	/**
	 *	@return		void
	 */
	protected function __onInit(): void
	{
		$this->config		= $this->env->getConfig();																	//  get app config
		$this->language		= $this->env->getLanguage();																//  get language support

		$options		= $this->config->getAll( 'module.resource_downloads.', TRUE );					//  get module options for job
		$this->logic	= new Logic_Download( $this->env, $options->get( 'path' ) );
	}

	/**
	 *	@param		int|string		$folderId
	 *	@param		string			$path
	 *	@param		int				$level
	 *	@return		int
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function migrateFilesInFolderByFolderId( int|string $folderId, string $path, int $level = 0 ): int
	{
		$count	= 0;
		$files	= $this->logic->findFiles( ['downloadFolderId' => $folderId] );
		foreach( $files as $file ){
			$count	+= $this->setFileSize( $path, $file );
		}
		$folders	= $this->logic->findFolders( ['parentId' => $folderId] );
		foreach( $folders as $folder ){
			$count	+= $this->migrateFilesInFolderByFolderId(
				$folder->downloadFolderId,
				$path.$folder->title.'/',
				$level + 1
			);
		}
		return $count;
	}

	/**
	 *	@param		string		$path
	 *	@param		object		$file
	 *	@return		int
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function setFileSize( string $path, object $file ): int
	{
		if( 0 === (int) $file->size ){
			if( !file_exists( $path.$file->title ) ){
				$this->out( 'File found and migrated: '.$path.$file->title );
				$data	= ['size' => filesize( $path.$file->title )];
				$this->logic->editFile( $file->downloadFileId, $data );
				return 1;
			}
			$this->out( 'File NOT found: '.$path.$file->title );
		}
		return 0;
	}
}
