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
	 *	@param		int|string		$articleId		ID of article
	 *	@param		integer			$change			Negative value on payed order, positive value on restock.
	 *	@return		integer							Article quantity in stock after change
	 *	@throws		InvalidArgumentException		if not found
	 */
	abstract public function changeQuantity( int|string $articleId, int $change ): int;

	/**
	 *	Checks existence of article and returns data object if found.
	 *	@access		public
	 *	@param		int|string		$articleId		ID of article
	 *	@param		boolean			$strict			Flag: throw exception if not existing, otherwise return FALSE
	 *	@return		object|FALSE					Bridged article data object if found, otherwise FALSE if strict mode is off
	 *	@throws		InvalidArgumentException		if not found
	 */
	abstract public function check( int|string $articleId, bool $strict = TRUE );

	/**
	 *	...
	 *	@access		public
	 *	@param		int|string		$articleId
	 *	@param		integer			$quantity
	 *	@return		object
	 */
	abstract public function get( int|string $articleId, int $quantity = 1 ): object;

	public function getBridgeClass(): string
	{
		return preg_replace( "/^Logic_ShopBridge_/", "", get_class( $this ) );
	}

	public function getBridgeId(): int|string
	{
		return $this->bridge->getBridgeId( $this );
	}

//	abstract public function getAll( $conditions = [], $orders = [], $limits = []);

	/**
	 *	Returns short description of article.
	 *	@access		public
	 *	@param		int|string		$articleId		ID of article
	 *	@return		string
	 */
	abstract public function getDescription( int|string $articleId ): string;

	/**
	 *	Returns link to article description.
	 *	@access		public
	 *	@param		int|string		$articleId		ID of article
	 *	@return		string
	 */
	abstract public function getLink( int|string $articleId ): string;

	/**
	 *	Returns URL of article picture, if existing.
	 *	@access		public
	 *	@param		int|string		$articleId		ID of article
	 *	@param		boolean			$absolute
	 *	@return		string
	 */
	abstract public function getPicture( int|string $articleId, bool $absolute = FALSE ): string;

	/**
	 *	Returns price of article (one or many).
	 *	@access		public
	 *	@param		int|string		$articleId		ID of article
	 *	@param		integer			$amount			Amount to articles to get price for
	 *	@return		float
	 */
	abstract public function getPrice( int|string $articleId, int $amount = 1 ): float;

	/**
	 *	Returns tax of article (one or many).
	 *	@access		public
	 *	@param		int|string		$articleId		ID of article
	 *	@param		integer			$amount			Amount to articles to get tax for
	 *	@return		float
	 */
	abstract public function getTax( int|string $articleId, int $amount = 1 ): float;

	/**
	 *	...
	 *	@access		public
	 *	@param		int|string		$articleId		Article ID
	 *	@return		string
	 */
	abstract public function getTitle( int|string $articleId ): string;

	/**
	 *	Returns weight (in g) of article (one or many).
	 *	@access		public
	 *	@param		int|string		$articleId		ID of article
	 *	@param		integer			$amount			Amount to articles to get weight for
	 *	@return		float
	 */
	abstract public function getWeight( int|string $articleId, int $amount = 1 ): float;

	abstract protected function __onInit(): void;
}
