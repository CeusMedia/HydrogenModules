<?php
class Logic_Database_Backup_Copy extends CMF_Hydrogen_Logic{

	protected $moduleConfig;
	protected $dumps;
	protected $prefixPlaceholder	= '<%?prefix%>';

	public function __onInit(){
		die("Logic_Database_Backup_Copy::onInit!");
		$this->config		= $this->env->getConfig();
		$this->request		= $this->env->getRequest();
		$this->moduleConfig	= $this->config->getAll( 'module.admin_database_backup.', TRUE );

		$this->logicBackup	= Logic_Database_Backup::getInstance( $this->env );

		$this->path			= $this->moduleConfig->get( 'path' );
		$this->commentsFile	= $this->path.'comments.json';
		if( !file_exists( $this->path ) )
			\FS_Folder_Editor::createFolder( $this->path );
		if( !file_exists( $this->commentsFile ) )
			file_put_contents( $this->commentsFile, '[]' );
		$this->comments	= \FS_File_JSON_Reader::load( $this->commentsFile, TRUE );
	}

	//  --  PROTECTED  --  //

	protected function check( $id ){
		if( ( $dump = $this->logicDump->check( $id, FALSE ) ) )
			return $dump;
		$this->messenger->noteError( 'Invalid dump ID' );
		$this->restart( 'admin/database/backup' );
	}
}
