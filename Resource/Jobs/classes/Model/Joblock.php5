<?php
class Model_Joblock {

	public $lockExt		= ".lock";
	public $lockPath	= "config/locks/";

	public function __construct( CMF_Hydrogen_Environment_Abstract $env = NULL ){
		$this->env		= $env;
		$config			= $this->env->getConfig();
		if( $config->get( 'path.jobs.locks' ) )
			$this->lockPath		= $config->get( 'path.jobs.locks' );
		if( !file_exists( $this->lockPath ) )
			FS_Folder_Editor::createFolder( $this->lockPath );
		$this->cleanup( 0 );
	}

	public function cleanup( $maxTime = 0 ){
		foreach( $this->index() as $lock )
			$this->isLocked( $lock->class, $lock->method, $maxTime );
	}

	public function getLockFileName( $className, $methodName ){
		return $this->lockPath.$className.'.'.$methodName.$this->lockExt;
	}

	public function index(){
		$map	= array();
		$index	= new DirectoryIterator( $this->lockPath );
		foreach( $index as $entry ){
			if( $entry->isDir() || $entry->isDot() )
				continue;
			$lifetime	= FS_File_Reader::load( $entry->getPathname() );
			list( $className, $methodName, $ext ) = explode( ".", $entry->getFilename() );
			$map[]	= (object) array(
				'class'		=> $className,
				'method'	=> $methodName,
				'pathname'	=> $entry->getPathname(),
				'filename'	=> $entry->getFilename(),
				'filetime'	=> filemtime( $entry->getPathname() ),
				'lifetime'	=> FS_File_Reader::load( $entry->getPathname() ),
			);
		}
		return $map;
	}

	public function isLocked( $className, $methodName, $maxTime = 0 ){
		$lockFile	= $this->getLockFileName( $className, $methodName );
		if( file_exists( $lockFile ) ){
			$lifetime	= (int) FS_File_Reader::load( $lockFile );
			$age		= time() - filemtime( $lockFile );
			if( $lifetime && $age >= $lifetime )
				$this->unlock( $className, $methodName );
			else if( $maxTime && $age >= $maxTime )
				$this->unlock( $className, $methodName );
		}
		return file_exists( $lockFile );
	}

	public function lock( $className, $methodName, $lifetime = 0 ){
		$filename	= $this->getLockFileName( $className, $methodName );							//  
		FS_File_Writer::save( $filename, (string) $lifetime );											//  
	}

	/**
	 *	Removes job call log and returns job lock remove status.
	 *	@access		protected
	 *	@param		string		$className		Name of job class
	 *	@param		string		$methodName		Name of job method
	 *	@return		integer		Status: 1 - success | 0 - not locked | -1 - error
	 */
	public function unlock( $className, $methodName ){
		$fileName	= $this->getLockFileName( $className, $methodName );							//  build lock file name
		if( !file_exists( $fileName ) )																//  lock file not existing
			return 0;																				//  quit with neutral status
		if( !@unlink( $fileName ) )																	//  removing job lock file failed
			return -1;																				//  quit with negative status
		return 1;																					//  otherwise quit with positive status
	}
}
?>
