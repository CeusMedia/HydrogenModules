<?php

use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger;

class Controller_Manage_IP_Lock_Reason extends Controller
{
	protected Logic_IP_Lock $logic;
	protected Messenger $messenger;
	protected Model_IP_Lock_Reason $model;

	/**
	 *	@param		string		$reasonId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function activate( string $reasonId ): void
	{
		$this->model->edit( $reasonId, [
			'status' => Model_IP_Lock_Reason::STATUS_ENABLED
		] );
		$this->restart( NULL, TRUE );
	}

	/**
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
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

	/**
	 *	@param		string		$reasonId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function deactivate( string $reasonId ): void
	{
		$this->model->edit( $reasonId, [
			'status' => Model_IP_Lock_Reason::STATUS_DISABLED
		] );
		$this->restart( NULL, TRUE );
	}

	/**
	 *	@param		string		$reasonId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function edit( string $reasonId ): void
	{
		$request	= $this->env->getRequest();
		/** @var ?Entity_IP_Lock_Reason $reason */
		$reason		= $this->model->get( $reasonId );
		if( NULL ===$reason ){
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
		$reason->filters	= $this->logic->getFiltersOfReason( $reason );
		$this->addData( 'reason', $reason );
	}

	/**
	 *	@return		void
	 */
	public function index(): void
	{
		$conditions	= [];
		$orders		= [];
		$limits		= [];
		/** @var array<Entity_IP_Lock_Reason> $reasons */
		$reasons	= $this->model->getAll( $conditions, $orders, $limits );
//		$model		= new Model_IP_Lock_Filter( $this->env );
		foreach( $reasons as $reason ){
			$reason->filters	= $this->logic->getFiltersOfReason( $reason );
		}
		$this->addData( 'reasons', $reasons );
	}

	/**
	 *	@param		string		$reasonId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function remove( string $reasonId ): void
	{
//		$request	= $this->env->getRequest();
		/** @var ?Entity_IP_Lock_Reason $reason */
		$reason		= $this->model->get( $reasonId );
		if( NULL === $reason ){
			$this->messenger->noteError( 'Invalid reason ID.' );
			$this->restart();
		}
		$this->model->remove( $reasonId );
		$this->messenger->noteSuccess( 'Reason removed.' );
		$this->restart( NULL, TRUE );
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->logic		= Logic_IP_Lock::getInstance( $this->env );
		$this->messenger	= $this->env->getMessenger();
		$this->model		= new Model_IP_Lock_Reason( $this->env );
	}
}
