<?php
/**
 *	Job scheduler.
 *	@category		cmApps
 *	@package		Chat.Server
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010 Ceus Media
 */

use CeusMedia\Common\FS\File\Reader as FileReader;
use CeusMedia\Common\FS\File\Writer as FileWriter;
use CeusMedia\HydrogenFramework\Application\Console as ConsoleApplication;
use CeusMedia\HydrogenFramework\Environment;

/**
 *	Job scheduler.
 *	@category		cmApps
 *	@package		Chat.Server
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010 Ceus Media
 */
class Scheduler extends ConsoleApplication
{
	protected $intervals	= array(
		'sec'	=> [],
		'min'	=> [],
		'min5'	=> [],
		'min10'	=> [],
		'min15'	=> [],
		'min30'	=> [],
		'hour'	=> [],
		'day'	=> [],
		'week'	=> [],
		'mon'	=> [],
		'mon3'	=> [],
		'mon6'	=> [],
		'year'	=> [],
	);
	protected $jobber;
	protected $jobs		= [];

	public static function readJobXmlFile( $modes = [] )
	{
		$map			= new stdClass();
		$map->jobs		= [];
		$map->intervals	= [];
		$index			= new \FS_File_RegexFilter( 'config/jobs/', '/\.xml$/i' );
		foreach( $index as $file ){
			$xml	= \XML_ElementReader::readFile( $file->getPathname() );
			foreach( $xml->job as $job ){
				$jobObj = new stdClass();
				$jobObj->id			= $job->getAttribute( 'id' );
				$jobObj->class		= (string) $job->class;
				$jobObj->method		= (string) $job->method;
				$jobObj->mode		= (string) $job->mode;
				$jobObj->interval	= (string) $job->interval;
				$jobObj->data		= [];
				if( !strlen( $jobObj->interval ) )
					continue;
				if( $modes && !in_array( $job->mode, $modes ) )
					continue;
				if( array_key_exists( $jobObj->id, $map->jobs ) )
					throw new \DomainException( 'Duplicate job ID "'.$jobObj->id.'"' );
#				foreach( $job->data as $date )
#					$jobObj->data[$date->getAttribute( 'key' )]	= (string) $job;
				$map->jobs[$jobObj->id] = $jobObj;
				if( !array_key_exists( $jobObj->interval, $map->intervals ) )
					$map->intervals[$jobObj->interval]	= [];
				$map->intervals[$jobObj->interval][]	= $jobObj;
			}
		}
		return $map;
	}

	public function __construct( Environment $env = NULL )
	{
		parent::__construct( $env );
		$this->moduleConfig		= $this->env->getConfig()->getAll( 'module.server_scheduler.', TRUE );
	}

	public function loadJobs( $mode = NULL )
	{
		if( $mode === NULL )
			$mode	= $this->moduleConfig->get( 'mode' );
		$map	= self::readJobXmlFile( [$mode] );
		$this->jobs	= $map->jobs;
//		remark( 'Mode: '.$mode );
//		print_m( array_keys( $this->jobs ) );
//		print_m( $map->intervals );
//		die;
		foreach( $map->intervals as $interval => $jobs )
			if( $interval )
				$this->intervals[$interval]	= $jobs;
		$this->jobber	= new \Jobber( $this->env );
		$this->jobber->loadJobs( [$mode] );
	}

	public function run( $loop = FALSE, $verbose = FALSE )
	{
		$sleep		= $this->moduleConfig->get( 'console.sleep' );
		$loop		= $loop	&& $sleep > 0;
//		$logFile	= $this->moduleConfig->get( 'log.error' );

		do {
			$fileName	= 'config/scheduler.last';
			if( !file_exists( $fileName ) )
				FileWriter::save( $fileName, (string) time() );
			$last	= FileReader::load( $fileName );

			$a	= $this->getChanges( $last, time() );
			foreach( $a as $type => $changed ){
				if( $changed ){
					foreach( $this->intervals[$type] as $job ){
						$this->jobber->runJob( $job->id );
					}
				}
			}
			FileWriter::save( $fileName, (string) time() );
			if( $loop && $sleep )
				sleep( $sleep );
		}
		while( $loop );
	}

	protected function getChanges( $last, $now )
	{
		$minute1	= date( 'i', $last );
		$minute2	= date( 'i', $now );
		$hour1		= date( 'h', $last );
		$hour2		= date( 'h', $now );
		$day1		= date( 'd', $last );
		$day2		= date( 'd', $now );
		$month1		= date( 'm', $last );
		$month2		= date( 'm', $now );
		$changes	= array(
			'sec'	=> $last != $now,
			'min'	=> $minute1 != $minute2,
			'min5'	=> floor( $minute1 / 5 ) != floor( $minute1 / 5 ),
			'min10'	=> floor( $minute1 / 10 ) != floor( $minute1 / 10 ),
			'min15'	=> floor( $minute1 / 15 ) != floor( $minute1 / 15 ),
			'min30'	=> floor( $minute1 / 30 ) != floor( $minute1 / 30 ),
			'hour'	=> $hour1 != $hour2,
			'day'	=> $day1 != $day2,
			'week'	=> date( "w", $last ) != date( "w", $now ),
			'mon'	=> $month1 != $month2,
			'mon3'	=> FALSE,
			'mon6'	=> FALSE,
			'year'	=> date( "y", $last ) != date( "y", $now ),
		);
		return $changes;
	}

	protected function out( $message ){
		print( $message."\n" );
	}
}
