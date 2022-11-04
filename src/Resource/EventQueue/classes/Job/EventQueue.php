<?php
class Job_EventQueue extends Job_Abstract
{
	protected $logic;
	protected $model;
	protected $options;

	public function count()
	{
		$conditions	= [
			'status'	=> Model_Event::STATUS_NEW,
		];
		$count	= $this->model->count( $conditions );
		if( $count ){
			$this->out( 'Found '.$count.' new (unhandled) events:' );
			$events	= $this->model->getAll( $conditions, ['createdAt' => 'ASC'] );
			foreach( $events as $event ){
				$this->out( '- '.date( 'Y-m-d H:i', $event->createdAt ).': '.$event->identifier );
			}
		}
		$this->out( 'No unhandled events found' );
	}

	public function handle()
	{
		$conditions	= ['status' => Model_Event::STATUS_NEW];
		$captain	= $this->env->getCaptain();
		$events		= $this->model->getAll( $conditions, ['createdAt' => 'ASC'] );
		foreach( $events as $event ){
			$this->out( '- '.date( 'Y-m-d H:i', $event->createdAt ).': '.$event->identifier );
			$this->model->edit( $event->eventId, [
				'status'		=> Model_Event::STATUS_RUNNING,
				'modifiedAt'	=> time(),
			] );
			$results	= (object) [
				'nrSucceeded'	=> 0,
				'nrFailed'		=> 0,
				'nrIgnored'		=> 0,
				'succeeded'		=> [],
				'failed'		=> [],
				'ignored'		=> [],
			];
			try{
				$data	= [
					'status'		=> Model_Event::STATUS_IGNORED,
					'modifiedAt'	=> time(),
				];
				$result = $captain->callHook( 'Events', 'handle', $this, [
					'identifier'	=> $event->identifier,
					'data'			=> json_decode( $event->data )
				] );
				if( $result === NULL ){
					$results->nrIgnored++;
					$results->ignored[]	= $event->eventId.':'.$event->identifier;
				}
				else{
					$data	= array_merge( $data, [
						'status'		=> Model_Event::STATUS_SUCCEEDED,
						'result'		=> json_encode( $result ),
					] );
					$results->nrSucceeded++;
					$results->succeeded[]	= $event->eventId.':'.$event->identifier;
				}
			}
			catch( Exception $e ){
				$data	= array_merge( $data, [
					'status'		=> Model_Event::STATUS_FAILED,
					'result'		=> json_encode( [
						'message'	=> $e->getMessage(),
						'code'		=> $e->getCode(),
						'file'		=> $e->getFile(),
						'line'		=> $e->getLine(),
					] ),
				] );
				$results->nrFailed++;
				$results->failed[]	= $event->eventId.':'.$event->identifier;
			}
			$this->model->edit( $event->eventId, $data );
		}
		$this->results	= $results;
	}

	protected function __onInit(): void
	{
		$this->options	= $this->env->getConfig()->getAll( 'module.resource_eventqueue.', TRUE );
		$this->logic	= $this->env->getLogic()->get( 'Job' );
		$this->model	= new Model_Event( $this->env );
	}
}
