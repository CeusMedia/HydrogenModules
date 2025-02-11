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

	/**
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function add(): void
	{
		if( $this->env->getRequest()->has( 'save' ) ){
			$data		= $this->env->getRequest()->getAll();
			$blockId	= $this->modelBlock->add( $data, FALSE );
			$this->restart( 'edit/'.$blockId, TRUE );
		}
	}

	/**
	 *	@param		string		$blockId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
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
			['content'	=> '%[block_'.$block->identifier.']%'] ,
			['title'		=> 'ASC']
		) );
		$this->addData( 'withinBlocks', $this->modelBlock->getAll(
			['content'	=> '%[block_'.$block->identifier.']%'] ,
			['title'		=> 'ASC']
		) );
	}

	/**
	 *	@param		$reset
	 *	@return		void
	 */
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

	/**
	 *	@param		integer		$page
	 *	@return		void
	 */
	public function index( int $page = 0 ): void
	{
		$session		= $this->env->getSession();
		$filters		= new Dictionary( array_merge(
			array_combine( $this->filters, array_fill( 0, count( $this->filters ), '' ) ),
			$session->getAll( $this->filterPrefix )
		) );
		$limit		= 15;
		$conditions	= [];

		if( (int) $filters->get( 'blockId' ) )
		 	$conditions['blockId']		= (int) $filters->get( 'blockId' );
		if( 0 !== strlen( trim( $filters->get( 'title', '' ) ) ) )
		 	$conditions['title']		= '%'.$filters->get( 'title' ).'%';
		if( 0 !== strlen( trim( $filters->get( 'identifier', '' ) ) ) )
		 	$conditions['identifier']	= '%'.$filters->get( 'identifier' ).'%';

		$orders		= ['title' => 'ASC'];
		$limits		= [$page * $limit, $limit];
		$total		= $this->modelBlock->count();
		$count		= $this->modelBlock->count( $conditions );
		/** @var Entity_Form_Block[] $blocks */
		$blocks		= $this->modelBlock->getAll( $conditions, $orders, $limits );
		$this->addData( 'blocks', $blocks );
		$this->addData( 'page', $page );
		$this->addData( 'pages', ceil( $count / $limit ) );
		$this->addData( 'count', $count );
		$this->addData( 'total', $total );

		$this->addData( 'filters', $filters );

		$identifiers	= $this->modelBlock->getAll(
			[],
			['identifier' => 'ASC'],
			[],
			['identifier']
		);
		$this->addData( 'identifiers', $identifiers );
	}

	/**
	 *	@param		string		$blockId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function view( string $blockId ): void
	{
		$block	= $this->checkId( $blockId );
		$this->addData( 'block', $block );
		$this->addData( 'blocks', $this->modelBlock->getAll( [], ['title' => 'ASC'] ) );
	}

	/**
	 *	@param		string		$blockId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function remove( string $blockId ): void
	{
		$this->checkId( $blockId );
		$this->modelBlock->remove( $blockId );
		$this->restart( NULL, TRUE );
	}

	/*  --  PROTECTED  --  */

	/**
	 *	@return		void
	 */
	protected function __onInit(): void
	{
		$this->modelForm	= new Model_Form( $this->env );
		$this->modelBlock	= new Model_Form_Block( $this->env );
	}

	/**
	 *	@param		string		$oldIdentifier
	 *	@param		string		$newIdentifier
	 *	@return		array
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function applyChangedIdentifier( string $oldIdentifier, string $newIdentifier ): array
	{
		$forms	= $this->modelForm->getAll( ['content'	=> '%[block_'.$oldIdentifier.']%'] );
		$blocks	= $this->modelBlock->getAll( ['content'	=> '%[block_'.$oldIdentifier.']%'] );
		foreach( $forms as $form ){
			$this->modelForm->edit( $form->formId, [
				'content'	=> preg_replace(
					'/\[block_'.preg_quote( $oldIdentifier, '/' ).'\]/',
					'[block_'.$newIdentifier.']',
					$form->content
				),
			] );
		}
		foreach( $blocks as $block ){
			$this->modelBlock->edit( $block->blockId, [
				'content'	=> preg_replace(
					'/\[block_'.preg_quote( $oldIdentifier, '/' ).'\]/',
					'[block_'.$newIdentifier.']',
					$block->content
				),
			] );
		}
		return [
			'forms'		=> count( $forms ),
			'blocks'	=> count( $blocks ),
			'total'		=> count( $forms ) + count( $blocks ),
		];
	}

	/**
	 *	@param		int|string		$blockId
	 *	@param		bool			$strict
	 *	@return		Entity_Form_Block|FALSE
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function checkId( int|string $blockId, bool $strict = TRUE ): Entity_Form_Block|FALSE
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
}
