<?php

use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use CeusMedia\HydrogenFramework\View\Helper\Abstraction;

class View_Helper_ModalRegistry extends Abstraction
{
	/** @var object[] $modals */
	protected array $modals	= [];

	/**
	 *	@param		WebEnvironment		$env
	 */
	public function __construct( WebEnvironment $env )
	{
		$this->env	= $env;
	}

	/**
	 *	@param		string		$key
	 *	@param		object		$modal
	 *	@return		void
	 */
	public function register( string $key, object $modal ): void
	{
		if( array_key_exists( $key, $this->modals ) )
			throw new RangeException( 'Modal with key "'.$key.'" already registered' );
		$this->modals[$key]	= $modal;
	}

	/**
	 *	@return		string
	 */
	public function render(): string
	{
		$list	= [];
		foreach( $this->modals as $modal )
			$list[]	= $modal->render();
		return join( $list );
	}
}
