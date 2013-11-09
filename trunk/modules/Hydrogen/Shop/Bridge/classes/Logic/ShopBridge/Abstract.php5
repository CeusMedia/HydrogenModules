<?php
abstract class Logic_ShopBridge_Abstract{

	/**	@var	Logic_ShopBridge					$bridge		Shop bridge logic instance */
	protected $bridge;
	/**	@var	CMF_Hydrogen_Environment_Abstract	$env		Environment instance */
	protected $env;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment_Abstract	$env
	 *	@return		void
	 */
	public function __construct( CMF_Hydrogen_Environment_Abstract $env, Logic_ShopBridge $bridge ){
		$this->env		= $env;
		$this->bridge	= $bridge;
		$this->__onInit();
	}

	public function getBridgeClass(){
		return preg_replace( "/^Logic_ShopBridge_/", "", get_class( $this ) );
	}

	public function getBridgeId(){
		return $this->bridge->getBridgeId( $this );
	}
	
	/**
	 *	...
	 *	@access		public
	 *	@param		integer		$articleId
	 *	@return		string
	 */
	abstract public function getPicture( $articleId, $absolute = FALSE );

	/**
	 *	...
	 *	@access		public
	 *	@param		integer		$articleId
	 *	@param		integer		$amount
	 *	@return		float
	 */
	abstract public function getPrice( $articleId, $amount = 1 );

	/**
	 *	...
	 *	@access		public
	 *	@param		integer		$articleId
	 *	@param		integer		$amount
	 *	@return		float
	 */
	abstract public function getTax( $articleId, $amount = 1 );

	/**
	 *	...
	 *	@access		public
	 *	@param		integer		$articleId
	 *	@return		string
	 */
	abstract public function getTitle( $articleId );
}
?>