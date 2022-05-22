<?php

use CeusMedia\HydrogenFramework\Controller;

class Controller_Manage_Job_Schedule extends Controller
{
	protected $modelSchedule;
	protected $modelDefinition;
	protected $modelRun;
	protected $logic;

	public function add()
	{
		if( $this->request->getMethod()->isPost() ){
			$format	= $this->request->get( 'format' );
			$data	= array(
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
			);
			if( in_array( $format, array( 'cron-month', 'cron-week' ) ) ){
				$data['type']		= Model_Job_Schedule::TYPE_CRON;
				$data['expression']	= $this->request->get( 'expressionCron' );
			}
			else if( in_array( $format, array( 'interval' ) ) ){
				$data['type']		= Model_Job_Schedule::TYPE_INTERVAL;
				$data['expression']	= $this->request->get( 'expressionInterval' );
			}
			else if( in_array( $format, array( 'datetime' ) ) ){
				$data['type']		= Model_Job_Schedule::TYPE_DATETIME;
				$data['expression']	= $this->request->get( 'expressionDatetime' );
			}
			$jobScheduleId	= $this->modelSchedule->add( $data );
			$this->env->getMessenger()->noteSuccess( 'Gespeichert.' );
			$this->restart( 'edit/'.$jobScheduleId, TRUE );
		}
	}

	public function edit( $jobScheduleId )
	{
		if( !( $jobSchedule = $this->modelSchedule->get( $jobScheduleId ) ) ){
			$this->env->getMessenger()->noteError( 'Ungültige ID gegeben. Weiterleitung zur Liste.' );
			$this->restart( NULL, TRUE );
		}
		if( $this->request->getMethod()->isPost() ){
			$format	= $this->request->get( 'format' );
			$data	= array(
				'jobDefinitionId'	=> $this->request->get( 'jobDefinitionId' ),
				'status'			=> $this->request->get( 'status' ),
				'title'				=> $this->request->get( 'title' ),
				'arguments'			=> $this->request->get( 'arguments' ),
				'reportMode'		=> $this->request->get( 'reportMode' ),
				'reportChannel'		=> $this->request->get( 'reportChannel' ),
				'reportReceivers'	=> $this->request->get( 'reportReceivers' ),
				'createdAt'			=> time(),
				'modifiedAt'		=> time(),
			);
			if( in_array( $format, array( 'cron-month', 'cron-week' ) ) ){
				$data['type']		= Model_Job_Schedule::TYPE_CRON;
				$data['expression']	= $this->request->get( 'expressionCron' );
			}
			else if( in_array( $format, array( 'interval' ) ) ){
				$data['type']		= Model_Job_Schedule::TYPE_INTERVAL;
				$data['expression']	= $this->request->get( 'expressionInterval' );
			}
			else if( in_array( $format, array( 'datetime' ) ) ){
				$data['type']		= Model_Job_Schedule::TYPE_DATETIME;
				$data['expression']	= $this->request->get( 'expressionDatetime' );
			}
			$this->modelSchedule->edit( $jobScheduleId, $data );
			$this->env->getMessenger()->noteSuccess( 'Gespeichert.' );
			$this->restart( 'edit/'.$jobScheduleId, TRUE );
		}
		$this->addData( 'item', $jobSchedule );
	}

	public function index( $page = 0 )
	{
		$schedule		= $this->modelSchedule->getAll( array(), array() );
		foreach( $schedule as $item ){
			$item->definition	= $this->allDefinitions[(int) $item->jobDefinitionId];
		}
//		$this->addData( 'allDefinedJobs', $this->allDefinitions );
		$this->addData( 'scheduledJobs', $schedule );
	}

	public function remove( $jobScheduleId )
	{
	}

	public function setStatus( $jobScheduleId, $status )
	{
		$from	= $this->request->get( 'from' );
		$this->modelSchedule->edit( $jobScheduleId, array(
			'status'		=> $status,
			'modifiedAt'	=> time(),
		) );
		$this->restart( $from ? $from : NULL, !$from );
	}

	//  --  PROTECTED  --  //

	protected function __onInit()
	{
		$this->request			= $this->env->getRequest();
		$this->modelDefinition	= new Model_Job_Definition( $this->env );
		$this->modelSchedule	= new Model_Job_Schedule( $this->env );
		$this->modelRun			= new Model_Job_Run( $this->env );
		$this->logic			= $this->env->getLogic()->get( 'Job' );

		$this->allDefinitions	= [];
		$definitions	= $this->modelDefinition->getAll( array(), array( 'identifier' => 'ASC' ) );
		foreach( $definitions as $definition )
			$this->allDefinitions[(int) $definition->jobDefinitionId] = $definition;

		$this->addData( 'definitionMap', $this->allDefinitions );
		$this->addData( 'wordsGeneral', $this->env->getLanguage()->getWords( 'manage/job' ) );
	}
}
