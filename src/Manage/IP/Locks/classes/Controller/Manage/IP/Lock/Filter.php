<?php

use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger;

class Controller_Manage_IP_Lock_Filter extends Controller
{
	protected Messenger $messenger;
	protected object $words;
	protected Model_IP_Lock_Filter $model;
	protected Logic_IP_Lock $logic;

	/**
	 *	@param		string		$filterId
	 *	@return		void
	 */
	public function activate( string $filterId ): void
	{
		$this->model->edit( $filterId, [
			'status'	=> Model_IP_Lock_Filter::STATUS_ENABLED,
		] );
		$this->restart( NULL, TRUE );
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function add(): void
	{
		$request	= $this->env->getRequest();
		if( $request->has( 'save' ) ){
			$data	= Entity_IP_Lock_Filter::fromArray( $request->getAll() );
			$data->createdAt	= time();
			$filterId	= $this->model->add( $data );
			$this->messenger->noteSuccess( $this->words->successAdded );
			$this->restart( NULL, TRUE );
		}
		$this->setData( $request->getAll() );
		$model		= new Model_IP_Lock_Reason( $this->env );
		$this->addData( 'reasons', $model->getAll() );
	}

	/**
	 *	@param		string		$filterId
	 *	@return		void
	 */
	public function deactivate( string $filterId ): void
	{
		$this->model->edit( $filterId, [
			'status'	=> Model_IP_Lock_Filter::STATUS_DISABLED,
		] );
		$this->restart( NULL, TRUE );
	}

	/**
	 *	@param		string		$filterId
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function edit( string $filterId ): void
	{
		$request	= $this->env->getRequest();
		/** @var ?Entity_IP_Lock_Filter $filter */
		$filter		= $this->model->get( $filterId );
		if( NULL === $filter ){
			$this->messenger->noteError( $this->words->errorInvalidId );
			$this->restart();
		}
		if( $request->has( 'save' ) ){
			$data		= $request->getAll();
			$data['modifiedAt']	= time();
			$this->model->edit( $filterId, $data );
			$this->messenger->noteSuccess( $this->words->successSaved );
			$this->restart( NULL, TRUE );
		}
		$this->addData( 'filter', $filter );
		$model		= new Model_IP_Lock_Reason( $this->env );
		$this->addData( 'reasons', $model->getAll() );
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function index(): void
	{
		$conditions	= [];
		$orders		= [];
		$limits		= [];
		$model		= new Model_IP_Lock_Reason( $this->env );
		$filters	= $this->model->getAll( $conditions, $orders, $limits );
		foreach( $filters as $filter )
			$filter->reason	= $model->get( $filter->reasonId );
		$this->addData( 'filters', $filters );
	}

	/**
	 *	@param		string		$filterId
	 *	@return		void
	 */
	public function remove( string $filterId ): void
	{
//		$request	= $this->env->getRequest();
		/** @var ?Entity_IP_Lock_Filter $filter */
		$filter		= $this->model->get( $filterId );
		if( NULL === $filter ){
			$this->messenger->noteError( $this->words->errorInvalidId );
			$this->restart();
		}
		$locks		= $this->logic->getAll( ['filterId' => $filterId] );
		foreach( $locks as $lock )
			$this->logic->remove( $lock );
		$this->model->remove( $filterId );
		$this->messenger->noteSuccess( $this->words->successRemoved );
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
		$this->words		= (object) $this->getWords( 'msg' );
		$this->model		= new Model_IP_Lock_Filter( $this->env );

		$acl	= $this->env->getAcl();
		$this->addData( 'canAdd', $acl->has( 'manage/ip/lock/filter', 'add' ) );
		$this->addData( 'canEdit', $acl->has( 'manage/ip/lock/filter', 'edit' ) );
		$this->addData( 'canActivate', $acl->has( 'manage/ip/lock/filter', 'activate' ) );
		$this->addData( 'canDeactivate', $acl->has( 'manage/ip/lock/filter', 'deactivate' ) );
		$this->addData( 'canRemove', $acl->has( 'manage/ip/lock/filter', 'remove' ) );
	}
}
