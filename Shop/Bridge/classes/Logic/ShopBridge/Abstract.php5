<?php
abstract class Logic_ShopBridge_Abstract
{
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
	public function __construct( CMF_Hydrogen_Environment $env, Logic_ShopBridge $bridge )
	{
		$this->env		= $env;
		$this->bridge	= $bridge;
		$this->__onInit();
	}

	/**
	 *	Change stock quantity of article.
	 *	@access		public
	 *	@param		integer		$articleId		ID of article
	 *	@param		integer		$change			Negative value on payed order, positive value on restock.
	 *	@return		integer						Article quantity in stock after change
	 *	@throws		InvalidArgumentException	if not found
	 */
	abstract public function changeQuantity( $articleId, int $change );

	/**
	 *	Checks existance of article and returns data object if found.
	 *	@access		public
	 *	@param		integer		$articleId		ID of article
	 *	@param		boolean		$strict			Flag: throw exception if not existing, otherwise return FALSE
	 *	@return		object|FALSE				Bridged article data object if found, otherwise FALSE if strict mode is off
	 *	@throws		InvalidArgumentException	if not found
	 */
	abstract public function check( $articleId, bool $strict = TRUE );

	/**
	 *	...
	 *	@access		public
	 *	@param		integer		$articleId
	 *	@return		string
	 */
	abstract public function get( $articleId, int $quantity = 1 );

	public function getBridgeClass()
	{
		return preg_replace( "/^Logic_ShopBridge_/", "", get_class( $this ) );
	}

	public function getBridgeId()
	{
		return $this->bridge->getBridgeId( $this );
	}

//	abstract public function getAll( $conditions = array(), $orders = array(), $limits = array());

	/**
	 *	Returns short description of article.
	 *	@access		public
	 *	@param		integer		$articleId		ID of article
	 *	@return		string
	 */
	abstract public function getDescription( $articleId );

	/**
	 *	Returns link to article description.
	 *	@access		public
	 *	@param		integer		$articleId		ID of article
	 *	@return		string
	 */
	abstract public function getLink( $articleId );

	/**
	 *	Returns URL of article picture, if existing.
	 *	@access		public
	 *	@param		integer		$articleId		ID of article
	 *	@param		boolean		$absolute
	 *	@return		string
	 */
	abstract public function getPicture( $articleId, bool $absolute = FALSE );

	/**
	 *	Returns price of article (one or many).
	 *	@access		public
	 *	@param		integer		$articleId		ID of article
	 *	@param		integer		$amount			Amount to articles to get price for
	 *	@return		float
	 */
	abstract public function getPrice( $articleId, int $amount = 1 );

	/**
	 *	Returns tax of article (one or many).
	 *	@access		public
	 *	@param		integer		$articleId		ID of article
	 *	@param		integer		$amount			Amount to articles to get tax for
	 *	@return		float
	 */
	abstract public function getTax( $articleId, int $amount = 1 );

	/**
	 *	...
	 *	@access		public
	 *	@param		integer		$articleId		Article ID
	 *	@return		string
	 */
	abstract public function getTitle( $articleId );

	/**
	 *	Returns weight of article (one or many).
	 *	@access		public
	 *	@param		integer		$articleId		ID of article
	 *	@param		integer		$amount			Amount to articles to get weight for
	 *	@return		integer
	 */
	abstract public function getWeight( $articleId, int $amount = 1 );
}
