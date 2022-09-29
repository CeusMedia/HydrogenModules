<?php
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
//	protected $logic;

	/**
	 *	Archive old job runs.
	 *	Job runs to be archived can be filtered by minimum age and job idenfier(s).
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
	 *	@return		void
	 */
	public function archive()
	{
		$modelRun			= new Model_Job_Run( $this->env );

		$age		= $this->parameters->get( '--age', '1M' );
		$age		= $age ? strtoupper( $age ) : '1M';
		$threshold	= date_create()->sub( new DateInterval( 'P'.$age ) );

		//  GET JOB RUNS
		$conditions		= array(
			'archived'		=> Model_Job_Run::ARCHIVED_NO,
			'finishedAt'	=> '< '.$threshold->format( 'U' ),
		);

		//  PARAMETER: IDENTIFIER(S)
		$identifierParam	= $this->parameters->get( '--identifier', '*' );
		$identifierParam	= preg_replace( '/\s/', '', $identifierParam );
		if( $identifierParam !== '*' ){
			$jobDefinitionIds	= [];
			$jobDefinitionMap	= [];
			foreach( $this->logic->getDefinitions() as $definition )
				$jobDefinitionMap[$definition->identifier]	= $definition->jobDefinitionId;
			foreach( explode( ',', $identifierParam ) as $identifier ){
				if( !array_key_exists( $identifier, $jobDefinitionMap ) )
					throw new \InvalidArgumentException( 'Invalid job identifier: '.$identifier );
				$jobDefinitionIds[]	= $jobDefinitionMap[$identifier];
			}
			$conditions['jobDefinitionId']	= $jobDefinitionIds;
		}

		//  PARAMETER: STATUS(ES)
		$statusParam	= strtoupper( $this->parameters->get( '--status', 'done,success' ) );
		$statusParam	= preg_replace( '/\s/', '', $statusParam );
		if( $statusParam !== '*' ){
			$statuses	= [];
			$statusMap	= Alg_Object_Constant::staticGetAll( 'Model_Job_Run', 'STATUS_' );
			foreach( explode( ',', $statusParam ) as $statusKey ){
				if( !array_key_exists( $statusKey, $statusMap ) )
					throw new \InvalidArgumentException( 'Invalid job run status: '.$statusKey );
				$statuses[]	= $statusMap[$statusKey];
			}
			$conditions['status']	= $statuses;
		}

		$orders		= ['jobRunId' => 'ASC'];
		$limits		= array(
			max( 0, (int) $this->parameters->get( '--offset', '0' ) ),
			max( 1, (int) $this->parameters->get( '--limit', '1000' ) ),
		);
		$runIds	= $modelRun->getAll( $conditions, $orders, $limits, ['jobRunId'] );
		$nrJobs	= count( $runIds );
		if( $nrJobs ){
			$this->showProgress( $counter = 0, $nrJobs );
			$database	= $this->env->getDatabase();
			$database->beginTransaction();
			foreach( $runIds as $nr => $runId ){
				if( !$this->dryMode )
					$this->logic->archiveJobRun( $runId );
				$this->showProgress( ++$counter, $nrJobs );
			}
			$database->commit();
		}
		$this->results	= ['count' => $nrJobs];
		$this->out( sprintf( 'Archived %d job runs.', $nrJobs ) );
		return $nrJobs ? 2 : 1;
	}

	/**
	 *	Remove old job runs.
	 *	Job runs to be removed can be filtered by minimum age and job idenfier(s).
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
	 *	@return		void
	 */
	public function remove()
	{
		$modelRun	= new Model_Job_Run( $this->env );

		$age		= $this->parameters->get( '--age', '1M' );
		$age		= $age ? strtoupper( $age ) : '1M';
		$threshold	= date_create()->sub( new DateInterval( 'P'.$age ) );

		//  GET JOB RUNS
		$conditions		= array(
//			'archived'		=> Model_Job_Run::ARCHIVED_NO,
			'finishedAt'	=> '< '.$threshold->format( 'U' ),
		);

		//  PARAMETER: IDENTIFIER(S)
		$identifierParam	= $this->parameters->get( '--identifier', '*' );
		$identifierParam	= preg_replace( '/\s/', '', $identifierParam );
		if( $identifierParam !== '*' ){
			$jobDefinitionIds	= [];
			$jobDefinitionMap	= [];
			foreach( $this->logic->getDefinitions() as $definition )
				$jobDefinitionMap[$definition->identifier]	= $definition->jobDefinitionId;
			foreach( explode( ',', $identifierParam ) as $identifier ){
				if( !array_key_exists( $identifier, $jobDefinitionMap ) )
					throw new \InvalidArgumentException( 'Invalid job identifier: '.$identifier );
				$jobDefinitionIds[]	= $jobDefinitionMap[$identifier];
			}
			$conditions['jobDefinitionId']	= $jobDefinitionIds;
		}

		//  PARAMETER: STATUS(ES)
		$statusParam	= strtoupper( $this->parameters->get( '--status', 'done,success' ) );
		$statusParam	= preg_replace( '/\s/', '', $statusParam );
		if( $statusParam !== '*' ){
			$statuses	= [];
			$statusMap	= Alg_Object_Constant::staticGetAll( 'Model_Job_Run', 'STATUS_' );
			foreach( explode( ',', $statusParam ) as $statusKey ){
				if( !array_key_exists( $statusKey, $statusMap ) )
					throw new \InvalidArgumentException( 'Invalid job run status: '.$statusKey );
				$statuses[]	= $statusMap[$statusKey];
			}
			$conditions['status']	= $statuses;
		}

		$orders		= ['jobRunId' => 'ASC'];
		$limits		= array(
			max( 0, (int) $this->parameters->get( '--offset', '0' ) ),
			max( 1, (int) $this->parameters->get( '--limit', '1000' ) ),
		);
		$runIds	= $modelRun->getAll( $conditions, $orders, $limits, ['jobRunId'] );
		$nrJobs	= count( $runIds );
		if( $nrJobs ){
			$this->showProgress( $counter = 0, $nrJobs );
			$database	= $this->env->getDatabase();
			$database->beginTransaction();
			foreach( $runIds as $nr => $runId ){
				if( !$this->dryMode )
					$this->logic->removeJobRun( $runId );
				$this->showProgress( ++$counter, $nrJobs );
			}
			$database->commit();
		}
		$this->results	= ['count' => $nrJobs];
		$this->out( sprintf( 'Removed %d job runs.', $nrJobs ) );
		return $nrJobs ? 2 : 1;
	}

	public function run()
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
			catch( \Exception $e ){
			}
		}
		$this->results	= array(
			'numberFound'	=> $numberFound,
			'numberRan'		=> $numberRan,
			'numberdone'	=> $numberDone,
		);
		return 1;
	}

	//  --  PROTECTED  --  //

	protected function __onInit()
	{
		$this->logic	= $this->env->getLogic()->get( 'Job' );
/*		$this->skipJobs	= array(
			$this->logic->getDefinitionByIdentifier( 'Job.Lock.clear' )->jobDefinitionId,
			$this->logic->getDefinitionByIdentifier( 'Job.Lock.list' )->jobDefinitionId,
		);*/
	}
}
