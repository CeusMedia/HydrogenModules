<?php

use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\HydrogenFramework\Controller;

class Controller_Manage_Job_Schedule extends Controller
{
	protected HttpRequest $request;
	protected Logic_Job $logic;
	protected Model_Job_Schedule $modelSchedule;
	protected Model_Job_Definition $modelDefinition;
	protected Model_Job_Run $modelRun;
	protected array $allDefinitions				= [];

	public function add(): void
	{
		if( $this->request->getMethod()->isPost() ){
			$format	= $this->request->get( 'format' );
			$data	= [
				'jobDefinitionId'	=> $this->request->get( 'jobDefinitionId' ),
				'type'				=> Model_Job_Schedule::TYPE_UNKNOWN,
				'status'			=> $this->request->get( 'status' ),
				'title'				=> $this->request->get( 'title' ),
				'arguments'			=> $this->request->get( 'arguments' ),
				'reportMode'		=> $this->request->get( 'reportMode' ),
				'reportChannel'		=> $this->request->get( 'reportChannel' ),
				'reportReceivers'	=> $this->request->get( 'reportReceivers' ),
				'createdAt'			=> time(),
				'modifiedAt'		=> time(),
			];
			if( in_array( $format, ['cron-month', 'cron-week'] ) ){
				$data['type']		= Model_Job_Schedule::TYPE_CRON;
				$data['expression']	= $this->request->get( 'expressionCron' );
			}
			else if( in_array( $format, ['interval'] ) ){
				$data['type']		= Model_Job_Schedule::TYPE_INTERVAL;
				$data['expression']	= $this->request->get( 'expressionInterval' );
			}
			else if( in_array( $format, ['datetime'] ) ){
				$data['type']		= Model_Job_Schedule::TYPE_DATETIME;
				$data['expression']	= $this->request->get( 'expressionDatetime' );
			}
			$jobScheduleId	= $this->modelSchedule->add( $data );
			$this->env->getMessenger()->noteSuccess( 'Gespeichert.' );
			$this->restart( 'edit/'.$jobScheduleId, TRUE );
		}
	}

	public function edit( string $jobScheduleId ): void
	{
		if( !( $jobSchedule = $this->modelSchedule->get( $jobScheduleId ) ) ){
			$this->env->getMessenger()->noteError( 'Ungültige ID gegeben. Weiterleitung zur Liste.' );
			$this->restart( NULL, TRUE );
		}
		if( $this->request->getMethod()->isPost() ){
			$format	= $this->request->get( 'format' );
			$data	= [
				'jobDefinitionId'	=> $this->request->get( 'jobDefinitionId' ),
				'status'			=> $this->request->get( 'status' ),
				'title'				=> $this->request->get( 'title' ),
				'arguments'			=> $this->request->get( 'arguments' ),
				'reportMode'		=> $this->request->get( 'reportMode' ),
				'reportChannel'		=> $this->request->get( 'reportChannel' ),
				'reportReceivers'	=> $this->request->get( 'reportReceivers' ),
				'createdAt'			=> time(),
				'modifiedAt'		=> time(),
			];
			if( in_array( $format, ['cron-month', 'cron-week'] ) ){
				$data['type']		= Model_Job_Schedule::TYPE_CRON;
				$data['expression']	= $this->request->get( 'expressionCron' );
			}
			else if( in_array( $format, ['interval'] ) ){
				$data['type']		= Model_Job_Schedule::TYPE_INTERVAL;
				$data['expression']	= $this->request->get( 'expressionInterval' );
			}
			else if( in_array( $format, ['datetime'] ) ){
				$data['type']		= Model_Job_Schedule::TYPE_DATETIME;
				$data['expression']	= $this->request->get( 'expressionDatetime' );
			}
			$this->modelSchedule->edit( $jobScheduleId, $data );
			$this->env->getMessenger()->noteSuccess( 'Gespeichert.' );
			$this->restart( 'edit/'.$jobScheduleId, TRUE );
		}
		$this->addData( 'item', $jobSchedule );
	}

	public function index( $page = 0 ): void
	{
		$schedule		= $this->modelSchedule->getAll( [], [] );
		foreach( $schedule as $item ){
			$item->definition	= $this->allDefinitions[(int) $item->jobDefinitionId];
		}
//		$this->addData( 'allDefinedJobs', $this->allDefinitions );
		$this->addData( 'scheduledJobs', $schedule );
	}

	public function remove( string $jobScheduleId ): void
	{
	}

	public function setStatus( string $jobScheduleId, $status ): void
	{
		$from	= $this->request->get( 'from' );
		$this->modelSchedule->edit( $jobScheduleId, [
			'status'		=> $status,
			'modifiedAt'	=> time(),
		] );
		$this->restart( $from ? $from : NULL, !$from );
	}

	//  --  PROTECTED  --  //

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->request			= $this->env->getRequest();
		$this->modelDefinition	= new Model_Job_Definition( $this->env );
		$this->modelSchedule	= new Model_Job_Schedule( $this->env );
		$this->modelRun			= new Model_Job_Run( $this->env );
		$this->logic			= $this->env->getLogic()->get( 'Job' );

		$definitions	= $this->modelDefinition->getAll( [], ['identifier' => 'ASC'] );
		foreach( $definitions as $definition )
			$this->allDefinitions[(int) $definition->jobDefinitionId] = $definition;

		$this->addData( 'definitionMap', $this->allDefinitions );
		$this->addData( 'wordsGeneral', $this->env->getLanguage()->getWords( 'manage/job' ) );
	}
}
