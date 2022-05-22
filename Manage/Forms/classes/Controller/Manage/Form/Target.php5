<?php

use CeusMedia\HydrogenFramework\Controller;

class Controller_Manage_Form_Target extends Controller
{
	public function add()
	{
		$request	= $this->env->getRequest();
		if( $request->getMethod()->isPost() ){
			$data		= [
				'title'			=> $request->get( 'title' ),
				'className'		=> $request->get( 'className' ),
				'baseUrl'		=> $request->get( 'baseUrl' ),
				'apiKey'		=> $request->get( 'apiKey' ),
				'status'		=> $request->get( 'status' ),
				'createdAt'		=> time(),
				'modifiedAt'	=> time(),
			];
			$targetId	= $this->modelTarget->add( $data );
//			$this->restart( 'edit/'.$targetId, TRUE );
			$this->restart( NULL, TRUE );
		}
	}

	public function edit( $targetId )
	{
		$request	= $this->env->getRequest();
		if( $request->getMethod()->isPost() ){
			$data		= [
				'title'			=> $request->get( 'title' ),
				'className'		=> $request->get( 'className' ),
				'baseUrl'		=> $request->get( 'baseUrl' ),
				'status'		=> $request->get( 'status' ),
				'modifiedAt'	=> time(),
			];
			if( strlen( trim( $request->get( 'apiKey' ) ) ) )
				$data['apiKey']	= $request->get( 'apiKey' );
			$this->modelTarget->edit( $targetId, $data );
//			$this->restart( 'edit/'.$targetId, TRUE );
			$this->restart( NULL, TRUE );
		}
		$this->addData( 'target', $this->modelTarget->get( $targetId ) );
	}

	public function index()
	{
		$targets	= $this->modelTarget->getAll( array(), array( 'title' => 'ASC' ) );
		foreach( $targets as $target ){
			$target->usedAt		= $this->modelTransfer->getByIndex( 'formTransferTargetId', $target->formTransferTargetId, array(), array( 'createdAt' ) );
			$target->transfers	= $this->modelTransfer->countByIndex( 'formTransferTargetId', $target->formTransferTargetId );
		}
		$this->addData( 'targets', $targets );
	}

	public function remove( $targetId )
	{
		$this->modelTarget->remove( $targetId );
		$this->restart( NULL, TRUE );
	}

	protected function __onInit()
	{
		$this->modelTarget		= new Model_Form_Transfer_Target( $this->env );
		$this->modelTransfer	= new Model_Form_Fill_Transfer( $this->env );
	}
}
