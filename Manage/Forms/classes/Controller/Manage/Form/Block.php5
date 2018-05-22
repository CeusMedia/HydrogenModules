<?php
class Controller_Manage_Form_Block extends CMF_Hydrogen_Controller{

	protected $modelForm;
	protected $modelBlock;

	public function __onInit(){
		$this->modelForm	= new Model_Form( $this->env );
		$this->modelBlock	= new Model_Form_Block( $this->env );
	}

	protected function checkId( $blockId ){
		if( !$blockId )
			throw new RuntimeException( 'No block ID given' );
		if( !( $block = $this->modelBlock->get( $blockId ) ) )
			throw new DomainException( 'Invalid block ID given' );
		return $block;
	}

	protected function checkIsPost(){
		if( !$this->env->getRequest()->isMethod( 'POST' ) )
			throw new RuntimeException( 'Access denied: POST requests, only' );
	}

	public function add(){
		$this->checkIsPost();
		$data		= $this->env->getRequest()->getAll();
		$blockId	= $this->modelBlock->add( $data, FALSE );
		$this->restart( '?action=block_edit&id='.$blockId );
	}

	public function edit( $blockId ){
		$this->checkIsPost();
		$this->checkId( $blockId );
		$data	= $this->env->getRequest()->getAll();
		$this->modelBlock->edit( $blockId, $data, FALSE );
		$this->restart( 'edit/'.$blockId, TRUE );
	}

	public function remove( $blockI ){
		$this->checkId( $blockId );
		$this->modelBlock->remove( $blockId );
		$this->restart( NULL, TRUE );
	}
}

