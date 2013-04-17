<?php
/**
 *	Chat maintainer.
 *	@category		cmApps
 *	@package		Chat.Server
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010 Ceus Media
 *	@version		$Id: Maintainer.php5 3022 2012-06-26 20:08:10Z christian.wuerker $
 */
/**
 *	Chat maintainer.
 *	@category		cmApps
 *	@package		Chat.Server
 *	@extends		CMF_Hydrogen_Application_Abstract
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010 Ceus Media
 *	@version		$Id: Maintainer.php5 3022 2012-06-26 20:08:10Z christian.wuerker $
 */
class Jobber extends CMF_Hydrogen_Application_Abstract {

	protected $jobs	= array();

	public function loadJobs( $modes ){
		$map	= self::readJobXmlFile( $modes );
		$this->jobs	= $map->jobs;
	}

	protected function out( $message ){
		print( $message."\n" );
	}
	
	public static function readJobXmlFile( $modes = array() ){
		$map			= new stdClass();
		$map->jobs		= array();
		$map->intervals	= array();
		$index			= new File_RegexFilter( 'config/jobs/', '/\.xml$/i' );
		foreach( $index as $file ){
			$xml	= XML_ElementReader::readFile( $file->getPathname() );
			foreach( $xml->job as $job ){
				$jobObj = new stdClass();
				$jobObj->id			= $job->getAttribute( 'id' );
				foreach( $job->children() as $nodeName => $node )
					$jobObj->$nodeName	= (string) $node;
				if( $modes && !in_array( $job->mode, $modes ) )
					continue;
				if( array_key_exists( $jobObj->id, $map->jobs ) )
					throw new DomainException( 'Duplicate job ID "'.$jobObj->id.'"' );
				$map->jobs[$jobObj->id] = $jobObj;
			}
		}
		return $map;
	}

	public function run( $verbose = FALSE ) {
		
		$request	= new Console_RequestReceiver();
		$parameters	= $request->getAll();
		array_shift( $parameters );
		
		if( count( $parameters ) < 1 )
			die( "Job ID needed." );
		$jobId	= array_shift( array_keys( $parameters ) );
		array_shift( $parameters );

		if( !array_key_exists( $jobId, $this->jobs ) )
			die( 'Job with ID "'.$jobId.'" is not existing.' );
		$job	= $this->jobs[$jobId];
		
#		$this->out( 'JobID: '.$jobId );
#		print_m( $job );
		
		$classArgs	= array( $this->env );
		$arguments	= array_keys( $parameters );

		$className	= 'Job_'.$job->class;
		if( !class_exists( $className ) )
			die( 'Job class "'.$className.'" is not existing.' );
		$result		= Alg_Object_MethodFactory::call( $className, $job->method, $arguments, $classArgs );
	}
}
?>
