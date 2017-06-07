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

	public function getExtensionVersion( $parameters ){
		$parameters	= array_keys( $parameters );
		$extension	= array_shift( $parameters );
		if( !$extension ){
			$this->out( 'No extension given' );
			return;
		}
		$this->out( preg_replace( '/(-.+)$/', '', phpversion( $extension ) ) );
	}

	public function reflectParameters( $parameters ){
		$this->out( json_encode( $parameters ) );
	}

	public function getDate( $parameters ){
		$format	= 'r';
		if( isset( $parameters['--format'] ) && $parameters['--format'] )
			$format	= $parameters['--format'];
		$this->out( date( $format ) );
	}

	public function index(){
		print( json_encode( $this->env->getRequest()->getAll()));


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
