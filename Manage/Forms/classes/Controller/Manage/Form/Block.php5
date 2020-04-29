<?php
class Controller_Manage_Form_Block extends CMF_Hydrogen_Controller{

	protected $modelForm;
	protected $modelBlock;
	protected $filterPrefix		= 'filter_manage_form_block_';
	protected $filters			= array(
		'blockId',
		'title',
		'identifier',
	);

	public function __onInit(){
		$this->modelForm	= new Model_Form( $this->env );
		$this->modelBlock	= new Model_Form_Block( $this->env );
	}

	protected function checkId( $blockId, $strict = TRUE ){
		if( !$blockId )
			throw new RuntimeException( 'No block ID given' );
		if( $block = $this->modelBlock->get( $blockId ) )
			return $block;
//		if( $block = $this->modelBlock->getByIndex( 'identifier', $blockId ) )
//			return $block;
		if( $strict )
			throw new DomainException( 'Invalid block ID given' );
		return FALSE;
	}

	protected function checkIsPost(){
		if( !$this->env->getRequest()->isMethod( 'POST' ) )
			throw new RuntimeException( 'Access denied: POST requests, only' );
	}

	public function add(){
		if( $this->env->getRequest()->has( 'save' ) ){
			$data		= $this->env->getRequest()->getAll();
			$blockId	= $this->modelBlock->add( $data, FALSE );
			$this->restart( 'edit/'.$blockId, TRUE );
		}
	}

	public function edit( $blockId ){
		$block	= $this->checkId( $blockId );

		if( $this->env->getRequest()->has( 'save' ) ){
			$data		= $this->env->getRequest()->getAll( NULL, TRUE );
			$identifier	= trim( $data->get( 'identifier' ) );
			if( strlen( $identifier ) && $identifier !== $block->identifier )
				$this->applyChangedIdentifier( $block->identifier, $identifier );
			$this->modelBlock->edit( $blockId, $data->getAll(), FALSE );
			$this->restart( 'edit/'.$blockId, TRUE );
		}
		$this->addData( 'block', $block );

		$this->addData( 'withinForms', $this->modelForm->getAll(
			array( 'content'	=> '%[block_'.$block->identifier.']%' ) ,
			array( 'title'		=> 'ASC' )
		) );
		$this->addData( 'withinBlocks', $this->modelBlock->getAll(
			array( 'content'	=> '%[block_'.$block->identifier.']%' ) ,
			array( 'title'		=> 'ASC' )
		) );
	}

	public function filter( $reset = NULL ){
		$request	= $this->env->getRequest();
		$session	= $this->env->getSession();
		if( $reset ){
			foreach( $this->filters as $filter )
				$session->remove( $this->filterPrefix.$filter );
		}
		foreach( $this->filters as $filter ){
			if( $request->has( $filter ) ){
				$value	= $request->get( $filter );
				$session->set( $this->filterPrefix.$filter, $value );
			}
		}
		$this->restart( NULL, TRUE );
	}

	public function index( $page = 0 ){
		$session		= $this->env->getSession();
		$filters		= new ADT_List_Dictionary( array_merge(
			array_combine( $this->filters, array_fill( 0, count( $this->filters ), NULL ) ),
			$session->getAll( $this->filterPrefix )
		) );
		$limit		= 15;
		$conditions	= array();

		if( (int) $filters->get( 'blockId' ) )
		 	$conditions['blockId']		= (int) $filters->get( 'blockId' );
		if( strlen( trim( $filters->get( 'title' ) ) ) )
		 	$conditions['title']		= '%'.$filters->get( 'title' ).'%';
		if( strlen( trim( $filters->get( 'identifier' ) ) ) )
		 	$conditions['identifier']	= '%'.$filters->get( 'identifier' ).'%';

		$orders		= array( 'title' => 'ASC' );
		$limits		= array( $page * $limit, $limit );
		$total		= $this->modelBlock->count();
		$count		= $this->modelBlock->count( $conditions );
		$blocks		= $this->modelBlock->getAll( $conditions, $orders, $limits );
		$this->addData( 'blocks', $blocks );
		$this->addData( 'page', $page );
		$this->addData( 'pages', ceil( $count / $limit ) );
		$this->addData( 'count', $count );
		$this->addData( 'total', $total );

		$this->addData( 'filters', $filters );

		$identifiers	= $this->modelBlock->getAll(
			array(),
			array( 'identifier' => 'ASC' ),
			array(),
			array( 'identifier' )
		);
		$this->addData( 'identifiers', $identifiers );
	}

	public function view( $blockId ){
		$block	= $this->checkId( $blockId );
		$this->addData( 'block', $block );
	}

	public function remove( $blockId ){
		$this->checkId( $blockId );
		$this->modelBlock->remove( $blockId );
		$this->restart( NULL, TRUE );
	}

	/*  --  PROTECTED  --  */
	protected function applyChangedIdentifier( $oldIdentifier, $newIdentifier ){
		$forms	= $this->modelForm->getAll(
			array( 'content'	=> '%[block_'.$oldIdentifier.']%' )
		);
		$blocks	= $this->modelBlock->getAll(
			array( 'content'	=> '%[block_'.$oldIdentifier.']%' )
		);
		foreach( $forms as $form ){
			$this->modelForm->edit( $form->formId, array(
				'content'	=> preg_replace(
					'/\[block_'.preg_quote( $oldIdentifier, '/' ).'\]/',
					'[block_'.$newIdentifier.']',
					$form->content
				),
			) );
		}
		foreach( $blocks as $block ){
			$this->modelBlock->edit( $block->blockId, array(
				'content'	=> preg_replace(
					'/\[block_'.preg_quote( $oldIdentifier, '/' ).'\]/',
					'[block_'.$newIdentifier.']',
					$block->content
				),
			) );
		}
		return array(
			'forms'		=> count( $forms ),
			'blocks'	=> count( $blocks ),
			'total'		=> count( $forms ) + count( $blocks ),
		);
	}
}
