<?php
class Model_Job{

	protected $pathJobs		= 'config/jobs/';

	public function __construct(){
	}

	public function get( $jobId ){
		if( $this->has( $jobId ) )
			return $this->jobs[$jobId];
		throw new RangeException( 'Job with ID "'.$jobId.'" is not existing' );
	}

	public function getAll(){
		return $this->jobs;
	}

	public function has( $jobId ){
		return array_key_exists( $jobId, $this->jobs );
	}

	public function load( $modes, $strict = TRUE ){
		$this->jobs	= array();
		foreach( self::readJobXmlFile( $modes )->jobs as $jobId => $job ){
			if( $strict && array_key_exists( $jobId, $this->jobs ) )
				throw new \DomainException( 'Duplicate job ID "'.$jobId.'"' );
			$this->jobs[$jobId]	= $job;
		}
		foreach( self::readJobJsonFile( $modes )->jobs as $jobId => $job ){
			if( $strict && array_key_exists( $jobId, $this->jobs ) )
				throw new \DomainException( 'Duplicate job ID "'.$jobId.'"' );
			$this->jobs[$jobId]	= $job;
		}
		ksort( $this->jobs/*, SORT_NATURAL | SORT_FLAG_CASE*/ );
	}


	/**
	 *	@todo  			convert module job files from XML to JSON
	 */
	protected function readJobJsonFile( $modes = array() ){
		$map			= new \stdClass();
		$map->jobs		= array();
		$map->intervals	= array();
		$index			= new \FS_File_RegexFilter( $this->pathJobs, '/\.json$/i' );
		foreach( $index as $file ){
			$json	= \FS_File_JSON_Reader::load( $file->getPathname(), FALSE );
			foreach( $json as $jobId => $job ){
				$job->id		= $jobId;
				$job->source	= 'json';
				$job->mode		= explode( ",", $job->mode );
				$job->multiple	= isset( $job->multiple ) ? $job->multiple : FALSE;
				if( $modes && !array_intersect( $job->mode, $modes ) )
					continue;
				$map->jobs[$jobId]	= $job;
			}
		}
		return $map;
	}

	protected function readJobXmlFile( $modes = array() ){
		$map			= new \stdClass();
		$map->jobs		= array();
		$map->intervals	= array();
		$index			= new \FS_File_RegexFilter( $this->pathJobs, '/\.xml$/i' );
		foreach( $index as $file ){
			$xml	= \XML_ElementReader::readFile( $file->getPathname() );
			foreach( $xml->job as $job ){
				$jobObj				= new \stdClass();
				$jobObj->id			= $job->getAttribute( 'id' );
				$jobObj->source		= 'xml';
				$jobObj->mode		= array( 'dev' );
				$jobObj->multiple	= $job->hasAttribute( 'multiple' ) ? TRUE : FALSE;
				$jobObj->deprecated	= NULL;
				foreach( $job->children() as $nodeName => $node ){
					$value	= (string) $node;
					if( $nodeName == 'mode' )
						$value	= explode( ',' , $value );
					$jobObj->$nodeName	= $value;
				}
				if( $modes && !array_intersect( $jobObj->mode, $modes ) )
					continue;
				if( array_key_exists( $jobObj->id, $map->jobs ) )
					throw new \DomainException( 'Duplicate job ID "'.$jobObj->id.'"' );
				$map->jobs[$jobObj->id] = $jobObj;
			}
		}
		return $map;
	}
}
