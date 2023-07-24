<?php

use CeusMedia\HydrogenFramework\Environment;

abstract class Logic_ShopBridge_Abstract
{
	/**	@var	Logic_ShopBridge	$bridge		Shop bridge logic instance */
	protected Logic_ShopBridge $bridge;

	/**	@var	Environment			$env		Environment instance */
	protected Environment $env;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		Environment			$env
	 *	@param		Logic_ShopBridge	$bridge
	 *	@return		void
	 */
	public function __construct( Environment $env, Logic_ShopBridge $bridge )
	{
		$this->env		= $env;
		$this->bridge	= $bridge;
		$this->__onInit();
	}

	/**
	 *	Change stock quantity of article.
	 *	@access		public
	 *	@param		string		$articleId		ID of article
	 *	@param		integer		$change			Negative value on payed order, positive value on restock.
	 *	@return		integer						Article quantity in stock after change
	 *	@throws		InvalidArgumentException	if not found
	 */
	abstract public function changeQuantity( string $articleId, int $change ): int;

	/**
	 *	Checks existence of article and returns data object if found.
	 *	@access		public
	 *	@param		string		$articleId		ID of article
	 *	@param		boolean		$strict			Flag: throw exception if not existing, otherwise return FALSE
	 *	@return		object|FALSE				Bridged article data object if found, otherwise FALSE if strict mode is off
	 *	@throws		InvalidArgumentException	if not found
	 */
	abstract public function check( string $articleId, bool $strict = TRUE );

	/**
	 *	...
	 *	@access		public
	 *	@param		string		$articleId
	 *	@param		integer		$quantity
	 *	@return		object
	 */
	abstract public function get( string $articleId, int $quantity = 1 ): object;

	public function getBridgeClass(): string
	{
		return preg_replace( "/^Logic_ShopBridge_/", "", get_class( $this ) );
	}

	public function getBridgeId()
	{
		return $this->bridge->getBridgeId( $this );
	}

//	abstract public function getAll( $conditions = [], $orders = [], $limits = []);

	/**
	 *	Returns short description of article.
	 *	@access		public
	 *	@param		string		$articleId		ID of article
	 *	@return		string
	 */
	abstract public function getDescription( string $articleId ): string;

	/**
	 *	Returns link to article description.
	 *	@access		public
	 *	@param		string		$articleId		ID of article
	 *	@return		string
	 */
	abstract public function getLink( string $articleId ): string;

	/**
	 *	Returns URL of article picture, if existing.
	 *	@access		public
	 *	@param		string		$articleId		ID of article
	 *	@param		boolean		$absolute
	 *	@return		string
	 */
	abstract public function getPicture( string $articleId, bool $absolute = FALSE ): string;

	/**
	 *	Returns price of article (one or many).
	 *	@access		public
	 *	@param		string		$articleId		ID of article
	 *	@param		integer		$amount			Amount to articles to get price for
	 *	@return		float
	 */
	abstract public function getPrice( string $articleId, int $amount = 1 ): float;

	/**
	 *	Returns tax of article (one or many).
	 *	@access		public
	 *	@param		string		$articleId		ID of article
	 *	@param		integer		$amount			Amount to articles to get tax for
	 *	@return		float
	 */
	abstract public function getTax( string $articleId, int $amount = 1 ): float;

	/**
	 *	...
	 *	@access		public
	 *	@param		string		$articleId		Article ID
	 *	@return		string
	 */
	abstract public function getTitle( string $articleId ): string;

	/**
	 *	Returns weight (in g) of article (one or many).
	 *	@access		public
	 *	@param		string		$articleId		ID of article
	 *	@param		integer		$amount			Amount to articles to get weight for
	 *	@return		float
	 */
	abstract public function getWeight( string $articleId, int $amount = 1 ): float;

	abstract protected function __onInit(): void;
}
