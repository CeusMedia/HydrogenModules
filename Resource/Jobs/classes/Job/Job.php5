<?php
class Job_Job extends Job_Abstract{

	protected $pathLocks	= 'config/locks/';

	public function __onInit(){
		$this->options	= $this->env->getConfig()->getAll( 'module.resource_cache.', TRUE );
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

	public function getPhpVersion(){
		$this->out( preg_replace( '/(-.+)$/', '', phpversion() ) );
	}

	public function getExtensionVersion( $commands, $parameters ){
		if( !$commands ){
			$this->out( 'No extension given' );
			return;
		}
		foreach( $commands as $command ){
			$version	= preg_replace( '/(-.+)$/', '', phpversion( $command ) );
			if( count( $commands ) > 1 )
				$version		= $command.': '.$version;
			$this->out( $version );
		}
	}

	public function reflectParameters( $commands, $parameters ){
		$this->out( json_encode( $parameters ) );
	}

	public function getDate( $commands, $parameters ){
		$format	= 'r';
		if( isset( $parameters['--format'] ) && $parameters['--format'] )
			$format	= $parameters['--format'];
		$this->out( date( $format ) );
	}

	public function index(){
	//	print( json_encode( $this->env->getRequest()->getAll()));
		foreach( $this->manager->getJobs() as $jobId => $jobData )
			$this->out( '- '.$jobId );
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
}
?>
