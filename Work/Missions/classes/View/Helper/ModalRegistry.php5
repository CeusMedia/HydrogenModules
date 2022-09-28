<?php

use CeusMedia\HydrogenFramework\View\Helper\Abstraction;

class View_Helper_ModalRegistry extends Abstraction
{
	protected $modals	= [];

	public function __construct( $env ){
		$this->env	= $env;
	}

	public function register( $key, $modal ){
		if( array_key_exists( $key, $this->modals ) )
			throw new RangeException( 'Modal with key "'.$key.'" already registered' );
		$this->modals[$key]	= $modal;
	}

	public function render(){
		$list	= [];
		foreach( $this->modals as $modal )
			$list[]	= $modal->render();
		return join( $list );
	}
}
