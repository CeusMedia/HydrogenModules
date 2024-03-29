<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Controller;

class Controller_Manage_Form_Block extends Controller
{
	protected Model_Form $modelForm;
	protected Model_Form_Block $modelBlock;
	protected string $filterPrefix		= 'filter_manage_form_block_';
	protected array $filters			= [
		'blockId',
		'title',
		'identifier',
	];

	public function add(): void
	{
		if( $this->env->getRequest()->has( 'save' ) ){
			$data		= $this->env->getRequest()->getAll();
			$blockId	= $this->modelBlock->add( $data, FALSE );
			$this->restart( 'edit/'.$blockId, TRUE );
		}
	}

	public function edit( string $blockId ): void
	{
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

	public function filter( $reset = NULL ): void
	{
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

	public function index( $page = 0 ): void
	{
		$session		= $this->env->getSession();
		$filters		= new Dictionary( array_merge(
			array_combine( $this->filters, array_fill( 0, count( $this->filters ), NULL ) ),
			$session->getAll( $this->filterPrefix )
		) );
		$limit		= 15;
		$conditions	= [];

		if( (int) $filters->get( 'blockId' ) )
		 	$conditions['blockId']		= (int) $filters->get( 'blockId' );
		if( strlen( trim( $filters->get( 'title' ) ) ) )
		 	$conditions['title']		= '%'.$filters->get( 'title' ).'%';
		if( strlen( trim( $filters->get( 'identifier' ) ) ) )
		 	$conditions['identifier']	= '%'.$filters->get( 'identifier' ).'%';

		$orders		= ['title' => 'ASC'];
		$limits		= [$page * $limit, $limit];
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

	public function view( string $blockId ): void
	{
		$block	= $this->checkId( $blockId );
		$this->addData( 'block', $block );
	}

	public function remove( string $blockId ): void
	{
		$this->checkId( $blockId );
		$this->modelBlock->remove( $blockId );
		$this->restart( NULL, TRUE );
	}

	/*  --  PROTECTED  --  */
	protected function __onInit(): void
	{
		$this->modelForm	= new Model_Form( $this->env );
		$this->modelBlock	= new Model_Form_Block( $this->env );
	}

	protected function applyChangedIdentifier( string $oldIdentifier, string $newIdentifier ): array
	{
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

	protected function checkId( string $blockId, bool $strict = TRUE )
	{
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

	protected function checkIsPost(): void
	{
		if( !$this->env->getRequest()->getMethod()->isPost() )
			throw new RuntimeException( 'Access denied: POST requests, only' );
	}
}
