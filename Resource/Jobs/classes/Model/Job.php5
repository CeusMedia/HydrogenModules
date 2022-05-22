<?php

use CeusMedia\HydrogenFramework\Environment;

class Model_Job
{
	const FORMAT_AUTO		= 0;
	const FORMAT_XML		= 1;
	const FORMAT_JSON		= 2;
	const FORMAT_MODULE		= 3;

	protected $pathJobs		= 'config/jobs/';
	protected $jobs			= [];
	protected $format		= 0;

	public function __construct( Environment $env )
	{
		$this->env	= $env;
	}

	public function get( string $jobId ): object
	{
		if( $this->has( $jobId ) )
			return $this->jobs[$jobId];
		throw new RangeException( 'Job with ID "'.$jobId.'" is not existing' );
	}

	public function getAll( $conditions = [] ): array
	{
		$list	= [];
		foreach( $this->jobs as $jobId => $job ){
			foreach( $conditions as $key => $value ){
				if( is_array( $value ) ){
					if( is_array( $job->$key ) ){
						if( !array_intersect( $value, $job->$key ) )
							continue 2;
					}
					else if( !in_array( $job->$key, $value ) )
						continue 2;
				}
				else if( is_bool( $value ) ){
					if( $value !== (bool) $job->$key )
						continue 2;
				}
				else {
					if( is_array( $job->$key ) ){
						if( !in_array( $value, $job->$key ) )
							continue 2;
					}
					else if( $value != $job->$key )
						continue 2;
				}
			}
			$list[$jobId]	= $job;
		}
		return $list;
	}

	public function getByInterval( $interval = NULL ): array
	{
		$intervals	= [];
		foreach( $this->jobs as $jobId => $job ){
			if( !$job->interval )
				continue;
			if( !in_array( $job->interval, $intervals ) )
				$intervals[$job->interval]	= [];
			$intervals[$job->interval][$jobId]	= $job;
		}
		if( $interval ){
			if( array_key_exists( $interval, $intervals ) )
				return $intervals[$interval];
			return array();
		}
		return $intervals;
	}

	public function has( string $jobId ): bool
	{
		return array_key_exists( $jobId, $this->jobs );
	}

	public function load( array $modes, bool $strict = TRUE ): self
	{
		$this->jobs	= [];
		if( $this->format === static::FORMAT_XML ){
			foreach( self::readJobsFromXmlFiles( $this->pathJobs, $modes ) as $jobId => $job ){
				if( $strict && array_key_exists( $jobId, $this->jobs ) )
					throw new \DomainException( 'Duplicate job ID "'.$jobId.'"' );
				$this->jobs[$jobId]	= $job;
			}
		}
		else if( $this->format === static::FORMAT_JSON ){
			foreach( self::readJobsFromJsonFiles( $this->pathJobs, $modes ) as $jobId => $job ){
				if( $strict && array_key_exists( $jobId, $this->jobs ) )
					throw new \DomainException( 'Duplicate job ID "'.$jobId.'"' );
				$this->jobs[$jobId]	= $job;
			}
		}
		else if( $this->format === static::FORMAT_AUTO ){
			foreach( self::readJobsFromXmlFiles( $this->pathJobs, $modes ) as $jobId => $job )
				$this->jobs[$jobId]	= $job;
			foreach( self::readJobsFromJsonFiles( $modes ) as $jobId => $job )
				$this->jobs[$jobId]	= $job;
		}
		else if( $this->format === static::FORMAT_MODULE ){
			foreach( self::readJobsFromModules( $modes ) as $jobId => $job )
				$this->jobs[$jobId]	= $job;
//			foreach( self::readJobsFromJsonFiles( $modes ) as $jobId => $job )
//				$this->jobs[$jobId]	= $job;
		}
		ksort( $this->jobs/*, SORT_NATURAL | SORT_FLAG_CASE*/ );
		return $this;
	}

	public function readJobsFromJsonFile( string $pathName, $modes = [] ): array
	{
		$jobs			= [];
		$json	= \FS_File_JSON_Reader::load( $pathName, FALSE );
		foreach( $json as $jobId => $job ){
			$job->id		= $jobId;
			$job->source	= 'json';
			$job->mode		= is_string( $job->mode ) ? explode( ",", $job->mode ) : $job->mode;
			$job->multiple	= isset( $job->multiple ) ? $job->multiple : FALSE;
			$job->interval	= isset( $job->interval ) ? $job->inteval : NULL;
			if( $modes && !array_intersect( $job->mode, $modes ) )
				continue;
			if( array_key_exists( $jobId, $jobs ) )
				throw new \DomainException( 'Duplicate job ID: '.$jobId );
			$jobs[$jobId]	= $job;
		}
		return $jobs;
	}

	public function readJobsFromXmlFile( string $pathName, $modes = [] ): array
	{
		$jobs	= [];
		$xml	= \XML_ElementReader::readFile( $pathName );
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
			if( array_key_exists( $jobObj->id, $jobs ) )
				throw new \DomainException( 'Duplicate job ID: '.$jobObj->id );
			$jobs[$jobObj->id] = $jobObj;
		}
		return $jobs;
	}

	public function setFormat( string $format ): self
	{
		$validFormats	= Alg_Object_Constant::staticGetAll( 'Model_Job', 'FORMAT_' );
		if( !in_array( $format, $validFormats ) )
			throw new RangeException( 'Invalid format' );
		$this->format	= $format;
		return $this;
	}

	/*  --  PROTECTED  --  */

	protected function readJobsFromJsonFiles( $modes = [] ): array
	{
		$jobs		= [];
		$index			= new \FS_File_RegexFilter( $this->pathJobs, '/\.json$/i' );
		foreach( $index as $file ){
			$fileJobs	= $this->readJobsFromJsonFile( $file->getPathname(), $modes );
			foreach( $fileJobs as $jobId => $job ){
				$job->pathName	= $file->getPathname();
				$job->fileName	= $file->getFilename();
				if( array_key_exists( $jobId, $jobs ) )
					throw new \DomainException( 'Duplicate job ID: '.$jobId );
				$jobs[$jobId]	= $job;
			}
		}
		return $jobs;
	}

	protected function readJobsFromModules( $modes = [] ): array
	{
		$jobs	= [];
		foreach( $this->env->getModules()->getAll() as $moduleId => $module )
			foreach( $module->jobs as $job )
				$jobs[$job->id]	= $job;
		return $jobs;
	}

	protected function readJobsFromXmlFiles( $modes = [] ): array
	{
		$jobs			= [];
		$index			= new \FS_File_RegexFilter( $this->pathJobs, '/\.xml$/i' );
		foreach( $index as $file ){
			$fileJobs	= $this->readJobsFromXmlFile( $file->getPathname(), $modes );
			foreach( $fileJobs as $jobId => $job ){
				$job->pathName	= $file->getPathname();
				$job->fileName	= $file->getFilename();
				if( array_key_exists( $jobId, $jobs ) )
					throw new \DomainException( 'Duplicate job ID: '.$jobId );
				$jobs[$jobId]	= $job;
			}
		}
		return $jobs;
	}
}
