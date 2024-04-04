<?php

use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger;

class Controller_Manage_IP_Lock_Reason extends Controller
{
	protected Logic_IP_Lock $logic;
	protected Messenger $messenger;
	protected Model_IP_Lock_Reason $model;

	public function activate( string $reasonId ): void
	{
		$this->model->edit( $reasonId, [
			'status' => Model_IP_Lock_Reason::STATUS_ENABLED
		] );
		$this->restart( NULL, TRUE );
	}

	public function add(): void
	{
		$request	= $this->env->getRequest();
		if( $request->has( 'save' ) ){
			$data		= $request->getAll();
			$data['createdAt']	= time();
			$reasonId	= $this->model->add( $data );
			$this->messenger->noteSuccess( 'Reason added.' );
			$this->restart( NULL, TRUE );
		}
		$this->setData( $request->getAll() );
	}

	public function deactivate( string $reasonId ): void
	{
		$this->model->edit( $reasonId, [
			'status' => Model_IP_Lock_Reason::STATUS_DISABLED
		] );
		$this->restart( NULL, TRUE );
	}

	public function edit( string $reasonId ): void
	{
		$request	= $this->env->getRequest();
		$reason		= $this->model->get( $reasonId );
		if( !$reason ){
			$this->messenger->noteError( 'Invalid reason ID.' );
			$this->restart();
		}
		if( $request->has( 'save' ) ){
			$data		= $request->getAll();
			$data['modifiedAt']	= time();
			$this->model->edit( $reasonId, $data );
			$this->messenger->noteSuccess( 'Reason saved.' );
			$this->restart( NULL, TRUE );
		}
		$reason->filters	= $this->logic->getFiltersOfReason( $reason->reasonId );
		$this->addData( 'reason', $reason );
	}

	public function index(): void
	{
		$conditions	= [];
		$orders		= [];
		$limits		= [];
		$reasons	= $this->model->getAll( $conditions, $orders, $limits );
//		$model		= new Model_IP_Lock_Filter( $this->env );
		foreach( $reasons as $reason ){
			$reason->filters	= $this->logic->getFiltersOfReason( $reason->reasonId );
		}
		$this->addData( 'reasons', $reasons );
	}

	public function remove( string $reasonId ): void
	{
//		$request	= $this->env->getRequest();
		$reason		= $this->model->get( $reasonId );
		if( !$reason ){
			$this->messenger->noteError( 'Invalid reason ID.' );
			$this->restart();
		}
		$this->model->remove( $reasonId );
		$this->messenger->noteSuccess( 'Reason removed.' );
		$this->restart( NULL, TRUE );
	}

	protected function __onInit(): void
	{
		/** @noinspection PhpFieldAssignmentTypeMismatchInspection */
		$this->logic		= Logic_IP_Lock::getInstance( $this->env );
		$this->messenger	= $this->env->getMessenger();
		$this->model		= new Model_IP_Lock_Reason( $this->env );
	}
}
