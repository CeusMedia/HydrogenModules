<?php

use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger;

class Controller_Manage_IP_Lock_Filter extends Controller
{
	protected Messenger $messenger;
	protected Model_IP_Lock_Filter $model;
	protected Logic_IP_Lock $logic;

	public function activate( string $filterId ): void
	{
		$this->model->edit( $filterId, [
			'status'	=> Model_IP_Lock_Filter::STATUS_ENABLED,
		] );
		$this->restart( NULL, TRUE );
	}

	public function add(): void
	{
		$request	= $this->env->getRequest();
		if( $request->has( 'save' ) ){
			$data		= $request->getAll();
			$data['createdAt']	= time();
			$filterId	= $this->model->add( $data );
			$this->messenger->noteSuccess( 'Filter added.' );
			$this->restart( NULL, TRUE );
		}
		$this->setData( $request->getAll() );
		$model		= new Model_IP_Lock_Reason( $this->env );
		$this->addData( 'reasons', $model->getAll() );
	}

	public function deactivate( string $filterId ): void
	{
		$this->model->edit( $filterId, [
			'status'	=> Model_IP_Lock_Filter::STATUS_DISABLED,
		] );
		$this->restart( NULL, TRUE );
	}

	public function edit( string $filterId ): void
	{
		$request	= $this->env->getRequest();
		$filter		= $this->model->get( $filterId );
		if( !$filter ){
			$this->messenger->noteError( 'Invalid filter ID.' );
			$this->restart();
		}
		if( $request->has( 'save' ) ){
			$data		= $request->getAll();
			$data['modifiedAt']	= time();
			$this->model->edit( $filterId, $data );
			$this->messenger->noteSuccess( 'Filter saved.' );
			$this->restart( NULL, TRUE );
		}
		$this->addData( 'filter', $filter );
		$model		= new Model_IP_Lock_Reason( $this->env );
		$this->addData( 'reasons', $model->getAll() );
	}

	public function index(): void
	{
		$conditions	= [];
		$orders		= [];
		$limits		= [];
		$model		= new Model_IP_Lock_Reason( $this->env );
		$filters	= $this->model->getAll( $conditions, $orders, $limits );
		foreach( $filters as $filter ){
			$filter->reason	= $model->get( $filter->reasonId );
		}
		$this->addData( 'filters', $filters );
	}

	public function remove( string $filterId ): void
	{
//		$request	= $this->env->getRequest();
		$filter		= $this->model->get( $filterId );
		if( !$filter ){
			$this->messenger->noteError( 'Invalid filter ID.' );
			$this->restart();
		}
		$locks		= $this->logic->getAll( ['filterId' => $filterId] );
		foreach( $locks as $lock )
			$this->logic->remove( $lock->ipLockId );
		$this->model->remove( $filterId );
		$this->messenger->noteSuccess( 'Filter and related locks removed.' );
		$this->restart( NULL, TRUE );
	}

	protected function __onInit(): void
	{
		/** @noinspection PhpFieldAssignmentTypeMismatchInspection */
		$this->logic		= Logic_IP_Lock::getInstance( $this->env );
		$this->messenger	= $this->env->getMessenger();
		$this->model		= new Model_IP_Lock_Filter( $this->env );
	}
}
