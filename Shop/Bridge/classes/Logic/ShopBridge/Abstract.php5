<?php
abstract class Logic_ShopBridge_Abstract{

	/**	@var	Logic_ShopBridge			$bridge		Shop bridge logic instance */
	protected $bridge;
	/**	@var	CMF_Hydrogen_Environment	$env		Environment instance */
	protected $env;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment	$env
	 *	@return		void
	 */
	public function __construct( CMF_Hydrogen_Environment $env, Logic_ShopBridge $bridge ){
		$this->env		= $env;
		$this->bridge	= $bridge;
		$this->__onInit();
	}

	abstract public function changeQuantity( $articleId, $change );

	/**
	 *	Checks existance of article and returns data object if found.
	 *	@access		public
	 *	@param		integer		$articleId		ID of article
	 *	@param		boolean		$strict			Flag: throw exception if not existing, otherwise return FALSE
	 *	@return		object|FALSE				Bridged article data object if found, otherwise FALSE if strict mode is off
	 *	@throws		InvalidArgumentException	if not found
	 */
	abstract public function check( $articleId, $strict = TRUE );

	/**
	 *	...
	 *	@access		public
	 *	@param		integer		$articleId
	 *	@return		string
	 */
	abstract public function get( $articleId, $quantity = 1 );

	public function getBridgeClass(){
		return preg_replace( "/^Logic_ShopBridge_/", "", get_class( $this ) );
	}

	public function getBridgeId(){
		return $this->bridge->getBridgeId( $this );
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		integer		$articleId		Article ID
	 *	@return		string
	 */
	abstract public function getDescription( $articleId );

	/**
	 *	...
	 *	@access		public
	 *	@param		integer		$articleId		Article ID
	 *	@return		string
	 */
	abstract public function getLink( $articleId );

	/**
	 *	...
	 *	@access		public
	 *	@param		integer		$articleId		Article ID
	 *	@param		boolean		$absolute
	 *	@return		string
	 */
	abstract public function getPicture( $articleId, $absolute = FALSE );

	/**
	 *	...
	 *	@access		public
	 *	@param		integer		$articleId		Article ID
	 *	@param		integer		$amount
	 *	@return		float
	 */
	abstract public function getPrice( $articleId, $amount = 1 );

	/**
	 *	...
	 *	@access		public
	 *	@param		integer		$articleId		Article ID
	 *	@param		integer		$amount
	 *	@return		float
	 */
	abstract public function getTax( $articleId, $amount = 1 );

	/**
	 *	...
	 *	@access		public
	 *	@param		integer		$articleId		Article ID
	 *	@return		string
	 */
	abstract public function getTitle( $articleId );

	/**
	 *	...
	 *	@access		public
	 *	@param		integer		$articleId		Article ID
	 *	@return		string
	 */
	abstract public function getWeight( $articleId );
}
?>
