<?php

use CeusMedia\Common\Alg\Obj\Constant as ObjectConstants;

/**
 *	This job exists to training working with jobs, only.
 *	Nothing productive will be done.
 *	Since jobs can be configured by call using commands and parameters,
 *	you can use these jobs to learn to use these options.
 */
class Job_Job_Schedule extends Job_Abstract
{
//	protected $pathLocks	= 'config/locks/';
//	protected $pathJobs		= 'config/jobs/';
	protected Logic_Job $logic;

	/**
	 *	Archive old job runs.
	 *	Job runs to be archived can be filtered by minimum age and job identifier(s).
	 *	Supports dry mode.
	 *
	 *	Parameters:
	 *		--age=PERIOD
	 *			- minimum age of job runs to archive
	 *			- DateInterval period without starting P and without any time elements
	 *			- see: https://www.php.net/manual/en/dateinterval.construct.php
	 *			- example: 1Y (1 year), 2M (2 months), 3D (3 days)
	 *			- optional, default: 1M
	 *		--identifier=ID[,...]
	 *			- list of job definition identifiers to focus on
	 *			- optional, default: *
	 *		--status=STATUS[,...]
	 *			- list of job run statuses to focus on
	 *			- values: terminated, failed, aborted, prepared, done, success, *
	 *			- optional, default: done, success
	 *		--limit=NUMBER
	 *			- maximum number of job runs to work on
	 *			- optional, default: 1000
	 *		--offset=NUMBER
	 *			- offset if using limit
	 *			- optional, default: 0
	 *
	 *	@access		public
	 *	@return		int
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 *	@throws		DateInvalidOperationException
	 *	@throws		DateMalformedIntervalStringException
	 */
	public function archive(): int
	{
		$modelRun	= new Model_Job_Run( $this->env );
		$threshold	= $this->getAgeThreshold( '--age' );

		//  GET JOB RUNS
		$conditions		= [
			'archived'		=> Model_Job_Run::ARCHIVED_NO,
			'finishedAt'	=> '< '.$threshold->format( 'U' ),
		];

		$this->extendConditionsByJobDefinitionIdentifierForRequestParameter( $conditions );
		$this->extendConditionsByStatusesForRequestParameter( $conditions );

		//  PARAMETER: STATUS(ES)

		$orders		= ['jobRunId' => 'ASC'];
		$limits		= $this->getLimitsFromRequest();
		$runIds	= $modelRun->getAll( $conditions, $orders, $limits, ['jobRunId'] );
		$nrJobs	= count( $runIds );
		if( $nrJobs ){
			$this->showProgress( $counter = 0, $nrJobs );
			$database	= $this->env->getDatabase();
			$database->beginTransaction();
			foreach( $runIds as $runId ){
				if( !$this->dryMode )
					$this->logic->archiveJobRun( $runId );
				$this->showProgress( ++$counter, $nrJobs );
			}
			$database->commit();
		}
		$this->setResult( Entity_Job_Result::STATUS_SUCCESS, $nrJobs );
		$this->out( sprintf( 'Archived %d job runs.', $nrJobs ) );
		return $nrJobs ? 2 : 1;
	}

	/**
	 *	Remove old job runs.
	 *	Job runs to be removed can be filtered by minimum age and job identifier(s).
	 *	Supports dry mode.
	 *
	 *	Parameters:
	 *		--age=PERIOD
	 *			- minimum age of job runs to archive
	 *			- DateInterval period without starting P and without any time elements
	 *			- see: https://www.php.net/manual/en/dateinterval.construct.php
	 *			- example: 1Y (1 year), 2M (2 months), 3D (3 days)
	 *			- optional, default: 1M
	 *		--identifier=ID[,...]
	 *			- list of job definition identifiers to focus on
	 *			- optional, default: *
	 *		--status=STATUS[,...]
	 *			- list of job run statuses to focus on
	 *			- values: terminated, failed, aborted, prepared, done, success, *
	 *			- optional, default: done,success
	 *		--limit=NUMBER
	 *			- maximum number of job runs to work on
	 *			- optional, default: 1000
	 *		--offset=NUMBER
	 *			- offset if using limit
	 *			- optional, default: 0
	 *
	 *	@access		public
	 *	@return		int
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 *	@throws		DateInvalidOperationException
	 *	@throws		DateMalformedIntervalStringException
	 */
	public function remove(): int
	{
		$modelRun	= new Model_Job_Run( $this->env );
		$threshold	= $this->getAgeThreshold( '--age' );

		//  GET JOB RUNS
		$conditions		= [
//			'archived'		=> Model_Job_Run::ARCHIVED_NO,
			'finishedAt'	=> '< '.$threshold->format( 'U' ),
		];

		$this->extendConditionsByJobDefinitionIdentifierForRequestParameter( $conditions );
		$this->extendConditionsByStatusesForRequestParameter( $conditions );

		$orders	= ['jobRunId' => 'ASC'];
		$limits	= $this->getLimitsFromRequest();
		$runIds	= $modelRun->getAll( $conditions, $orders, $limits, ['jobRunId'] );
		$nrJobs	= count( $runIds );

		if( $this->dryMode )
			$this->out( sprintf( 'Dry Mode: Would remove %d job runs.', $nrJobs ) );
		if( $nrJobs ){
			$this->showProgress( $counter = 0, $nrJobs );
			$database	= $this->env->getDatabase();
			$database->beginTransaction();
			foreach( $runIds as $runId ){
				if( !$this->dryMode )
					$this->logic->removeJobRun( $runId );
				$this->showProgress( ++$counter, $nrJobs );
			}
			$database->commit();
		}
		$this->setResult( Entity_Job_Result::STATUS_SUCCESS, $nrJobs );
		$this->out( sprintf( 'Removed %d job runs.', $nrJobs ) );
		return $nrJobs ? 2 : 1;
	}

	/**
	 *	@return		int
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function run(): int
	{
		$preparedJobs	= $this->logic->prepareScheduledJobs();
		$numberFound	= count( $preparedJobs );
		$numberRan		= 0;
		$numberDone		= 0;
		foreach( $preparedJobs as $preparedJobRun ){
			try{
				$args					= $preparedJobRun->arguments;
				$commands				= [];
				$parameters				= [];
				$fallBackOnEmptyPair	= FALSE;
				if( strlen( trim( $preparedJobRun->arguments ) ) ){
					$args	= preg_split( '/ +/', trim( $preparedJobRun->arguments ) );
					foreach( $args as $argument ){
						if( substr_count( $argument, '=' ) || $fallBackOnEmptyPair ){
							$parts	= explode( '=', $argument, 2 );
							$key	= array_shift( $parts );
							$value	= $parts ? $parts[0] : NULL;
							$parameters[$key]	= $value;
						}
						else
							$commands[]	= $argument;
					}
				}
				$result		= $this->logic->startJobRun( $preparedJobRun, $commands, $parameters );
				$numberRan++;
				if( $result === 1 )
					$numberDone++;
			}
			catch( Exception ){
			}
		}
		$status	= Entity_Job_Result::STATUS_SUCCESS;
		if( $numberDone !== $numberFound )
			$status	= Entity_Job_Result::STATUS_PARTIAL;

		$this->setResult( $status, $numberDone, [
			'numberFound'	=> $numberFound,
			'numberRan'		=> $numberRan,
			'numberDone'	=> $numberDone,
		] );
		return 1;
	}

	//  --  PROTECTED  --  //

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->logic	= Logic_Job::getInstance( $this->env );
/*		$this->skipJobs	= array(
			$this->logic->getDefinitionByIdentifier( 'Job.Lock.clear' )->jobDefinitionId,
			$this->logic->getDefinitionByIdentifier( 'Job.Lock.list' )->jobDefinitionId,
		);*/
	}

	/**
	 *	@param		array		$conditions
	 *	@param		string		$default
	 *	@return		void
	 */
	protected function extendConditionsByJobDefinitionIdentifierForRequestParameter( array & $conditions, string $default = '*' ): void
	{
		$identifierParam	= $this->getParameterFromRequest( '--identifier', $default );
		if( '*' === $identifierParam )
			return;
		/** @var array<int|string> $jobDefinitionIds */
		$jobDefinitionIds	= [];
		/** @var array<string,int|string> $jobDefinitionMap */
		$jobDefinitionMap	= [];
		foreach( $this->logic->getDefinitions() as $definition )
			$jobDefinitionMap[$definition->identifier]	= $definition->jobDefinitionId;
		foreach( explode( ',', $identifierParam ) as $identifier ){
			if( !array_key_exists( $identifier, $jobDefinitionMap ) )
				throw new InvalidArgumentException( 'Invalid job identifier: '.$identifier );
			$jobDefinitionIds[]	= $jobDefinitionMap[$identifier];
		}
		$conditions['jobDefinitionId']	= $jobDefinitionIds;
	}

	/**
	 *	@param		array		$conditions
	 *	@param		string		$default
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function extendConditionsByStatusesForRequestParameter( array & $conditions, string $default = 'done,success' ): void
	{
		$statusParam	= $this->getParameterFromRequest( '--status', $default );
		if( '*' === $statusParam )
			return;
		$statuses	= [];
		$statusMap	= ObjectConstants::staticGetAll( 'Model_Job_Run', 'STATUS_' );
		foreach( explode( ',', strtoupper( $statusParam ) ) as $statusKey ){
			if( !array_key_exists( $statusKey, $statusMap ) )
				throw new InvalidArgumentException( 'Invalid job run status: '.$statusKey );
			$statuses[]	= $statusMap[$statusKey];
		}
		$conditions['status']	= $statuses;
	}
}
