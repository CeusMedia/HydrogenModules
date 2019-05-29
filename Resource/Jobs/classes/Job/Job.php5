<?php
class Job_Job extends Job_Abstract{

	protected $pathLocks	= 'config/locks/';
	protected $pathJobs		= 'config/jobs/';

	public function __onInit(){
		$this->options	= $this->env->getConfig()->getAll( 'module.resource_cache.', TRUE );
	}

	/**
	 *	@todo		finish implementation
	 */
	public function alert(){
		$skip	= array( 'Not.Job.alert' );
		$locks	= $this->getLocks( $skip );
		$list	= array();
		foreach( $locks as $lockFile ){
			$timestamp	= filemtime( $this->pathLocks.$lockFile.'.lock' );
			$age		= time() - $timestamp;
			$datetime	= date( 'Y-m-d_H:i:s', $timestamp );
			$listKey	= $datetime.'_'.$lockFile;
			$list[$listKey]	= (object) array(
				'listKey'		=> $listKey,
				'timestamp'		=> $timestamp,
				'datetime'		=> $datetime,
				'ageSeconds'	=> $age,
				'filePath'		=> $this->pathLocks.$lockFile.'.lock',
				'fileName'		=> $lockFile.'.lock',
				'jobKey'		=> $lockFile,
			);
		}
		krsort( $list );
		print_m( $list );

		foreach( $list as $item ){


		}
	}

	public function clearLocks(){
		$skip	= array(
			'Job.Lock.clear',
			'Job.clearLocks',					//  @todo remove after migration
		);
		$list	= $this->getLocks( $skip );
		foreach( $list as $item )
			@unlink( $this->pathLocks.$item.'.lock' );
		$this->out( 'Removed '.count( $list ).' locks:' );
		foreach( $list as $item )
			$this->out( ' - '.$item );
		$this->out();
	}

	public function convertJobConfigToJson(){
		$model		= new Model_Job( $this->env );
		$modes		= array();																		//  no specific modes
		$index		= new \FS_File_RegexFilter( $this->pathJobs, '/\.xml$/i' );
		foreach( $index as $file ){
			$this->out( 'Reading job XML: '.$file->getFilename() );
			$moduleJobs		= (object) array(
				'moduleId'	=> NULL,
				'version'	=> NULL,
				'jobs'		=> array(),
			);
			$fileJobs	= $model->readJobsFromXmlFile( $file->getPathname(), $modes );
			foreach( $fileJobs as $jobId => $jobSource ){
				$jobTarget	= (object) array(
					'class'		=> $jobSource->class,
					'method'	=> $jobSource->method,
					'mode'		=> $jobSource->mode,
				);
				if( !empty( $jobSource->interval ) )
					$jobTarget->interval	= $jobSource->interval;
				if( $jobSource->deprecated )
					$jobTarget->deprecated	= $jobSource->deprecated;
				if( $jobSource->multiple )
					$jobTarget->multiple	= $jobSource->multiple;
				if( !empty( $jobSource->arguments ) )
					$jobTarget->arguments	= $jobSource->arguments;
				$moduleJobs->jobs[$jobId]	= $jobTarget;
			}
			$fileName	= preg_replace( '@\.[^.]+$@', '', $file->getFilename() );
			$default	= str_replace( ' ', '_', ucwords( str_replace( '.', ' ', $fileName ) ) );
			$moduleId	= CLI_Question::askStatic( 'Module ID', 'string', $default, NULL, FALSE );
			$moduleJobs->moduleId	= $moduleId;
			$targetFile	= $this->pathJobs.$moduleId.'.json';
			$this->out( 'Writing job JSON: '.$targetFile );
			FS_File_JSON_Writer::save( $targetFile, $moduleJobs, TRUE );
		}
	}

	/**
	 *	Returns current date time depending on format parameter.
	 *	Uses parameter --format (-f), default: 'r' (RFC 2822).
	 *	Supports all date formats (http://php.net/manual/de/function.date.php).
	 *	Supports format constants, like DATE_W3C.
	 *	Removes milliseconds (.v) below PHP version 7.
	 *	@access		public
	 *	@return		string		Current date time in requested format
	 *	@todo		use environment date after framework update, see below
	 */
	public function getDate(){
		$format	= 'r';
		if( $this->parameters->get( '-f' ) && !$this->parameters->get( '--format' ) )
			$this->parameters->set( '--format', $this->parameters->get( '-f' ) );
		if( $this->parameters->get( '--format' ) )
			$format	= $this->parameters->get( '--format' );
		if( preg_match( '/^[A-Z0-9_]+$/', $format ) && ADT_Constant::has( $format ) ){
			if( $this->verbose )
				$this->out( 'Found format by constant.' );
			$format	= ADT_Constant::get( $format );
		}
		else if( version_compare( PHP_VERSION, '7.0', '<' ) ){
			if( $this->verbose )
				$this->out( 'Removing milliseconds for PHP < 7.' );
			$format	= preg_replace( '/\.v/', '', $format );
		}
		$this->out( date_create()->format( $format ) );							//  @todo replace by line below after framework update
//		$this->out( $this->env->date->now->format( $format ) );
	}

	public function getExtensionVersion(){
		if( !$this->commands ){
			$this->out( 'No extension given' );
			return;
		}
		foreach( $this->commands as $command ){
			$version	= preg_replace( '/(-.+)$/', '', phpversion( $command ) );
			if( count( $commands ) > 1 )
				$version		= $command.': '.$version;
			$this->out( $version );
		}
	}

	public function getPhpVersion(){
		$this->out( preg_replace( '/(-.+)$/', '', phpversion() ) );
	}

	public function index(){
	//	print( json_encode( $this->env->getRequest()->getAll()));
		foreach( $this->manager->getJobs() as $jobId => $jobData ){
			if( $jobData->deprecated )
				$this->out( '- '.$jobId.' (deprecated: '.$jobData->deprecated.')' );
			else
				$this->out( '- '.$jobId );
		}
	}

	public function info( $jobKeys = NULL ){
		if( !( $this->manager instanceof Jobber ) ){
			$class	= get_class( $this->manager );
			throw new RuntimeException( 'Manager "'.$class.'" is not supported, yet' );
		}
		$jobs	= $this->manager->getJobs();
		if( !count( $jobKeys ) ){
			$this->out( 'No job keys given' );
			return;
		}
		foreach( $jobKeys as $jobKey ){
			if( !array_key_exists( (string) $jobKey, $jobs ) ){
				$this->out( 'Job "'.$jobKey.'" is not existing' );
				continue;
			}
			print_m( $jobs[$jobKey] );
		}
	}

	public function listLocks(){
		$skip	= array(
			'Job.Lock.list',
			'Job.listLocks',					//  @todo remove after migration
		);
		$list	= $this->getLocks( $skip );
		$this->out( 'Found '.count( $list ).' locks:' );
		foreach( $list as $item )
			$this->out( ' - '.$item );
		$this->out();
	}

	public function reflect(){
		$this->reflectCommands();
		$this->reflectParameters();
	}

	public function reflectCommands(){
//		$this->out( json_encode( $this->commands ) );
		$this->out( 'Commands: '.join( ', ', $this->commands ) );
	}

	public function reflectParameters(){
//		$this->out( json_encode( $this->parameters->getAll() ) );
		$this->out( 'Parameters: ' );
		foreach( $this->parameters as $key => $value )
			$this->out( '  '.$key.' => '.$value );
	}

	//  --  PRIVATE  METHODS  --  //
	protected function getLocks( $skip = array() ){
		$list	= array();
		if( file_exists( $this->pathLocks ) ){
			$index	= new DirectoryIterator( $this->pathLocks );
			foreach( $index as $entry ){
				if( $entry->isDot() )
					continue;
				$jobId	= preg_replace( '/\.lock$/', '', $entry->getFilename() );
				if( !in_array( $jobId, $skip ) )
					$list[]	= $jobId;
			}
		}
		return $list;
	}
}
?>
