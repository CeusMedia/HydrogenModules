<?php

use CeusMedia\Common\Alg\Text\CamelCase as TextCamelCase;
use CeusMedia\HydrogenFramework\Environment;

class Logic_ShopBridge
{
	/**	@var	Environment				$env			Environment instance */
	protected Environment $env;

	/**	@var	array					$bridges		Map of registered bridges by ID */
	protected array $bridges			= [];

	/**	@var	array					$bridgeClasses	Map of registered bridges by class name */
	protected array $bridgeClasses		= [];

	/**	@var	string					$pathToBridges */
	public static string $pathToBridges	= "classes/Logic/ShopBridge/";

	/**	@var	Model_Shop_Bridge		$model			Model of shop bridges */
	protected Model_Shop_Bridge $model;

	/**
	 *	Constructor.
	 *	Auto-detects available bridge classes.
	 *	@access		public
	 *	@param		Environment		$env	Environment
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function __construct( Environment $env )
	{
		$this->env		= $env;
		$this->model	= new Model_Shop_Bridge( $env );
		$this->readBridges();
	//	if( $this->discoverBridges( TRUE ) )
	//		$this->readBridges();
	}

	public function discoverBridges( bool $install = FALSE ): array
	{
		$list	= [];
		foreach( new DirectoryIterator( self::$pathToBridges ) as $entry ){							//  iterate list of classes in bridge class folder
			if( $entry->isDir() || $entry->isDot() )												//  exclude folders and folder links
				continue;
			$class	= pathinfo( $entry->getFilename(), PATHINFO_FILENAME );							//
			if( $class === "Abstract" )
				continue;
			if( !array_key_exists( $class, $this->bridgeClasses ) ){					//
				$name		= trim( TextCamelCase::decode( $class ) );
				$title		= str_replace( " ", ": ", ucwords( $name ) );
				$className	= str_replace( " ", "_",ucwords( $name ) );
				$pathName	= strtolower( str_replace( " ", "/", $name ) ).'/';
				$data		= [
					'class'					=> $class,
					'title'					=> $title,
					'frontendController'	=> $className,
					'frontendUriPath'		=> $pathName,
					'backendController'		=> 'Manage_'.$className,
					'backendUriPath'		=> 'manage/'.$pathName,
					'articleTableName'		=> '',
					'articleIdColumn'		=> '',
					'createdAt'				=> time(),
				];
				if( $install )
					$this->model->add( $data );
				$list[]	= (object) $data;
			}
		}
		return $list;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		mixed			$bridge			Bridge ID or class name
	 *	@param		int|string		$articleId		Article ID
	 *	@param		integer			$quantity
	 *	@return		object
	 */
	public function getArticle( $bridge, int|string $articleId, int $quantity = 1 ): object
	{
		return $this->bridge( $bridge )->get( $articleId, $quantity );
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		mixed		$bridge		Bridge ID or class name
	 *	@param		string		$articleId	Article ID
	 *	@return		string
	 */
	public function getArticleDescription( $bridge, string $articleId ): string
	{
		return $this->bridge( $bridge )->getDescription( $articleId );
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		mixed			$bridge		Bridge ID or class name
	 *	@param		int|string		$articleId	Article ID
	 *	@return		string
	 */
	public function getArticleLink( $bridge, int|string $articleId ): string
	{
		return $this->bridge( $bridge )->getLink( $articleId );
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		mixed			$bridge			Bridge ID or class name
	 *	@param		int|string		$articleId		Article ID
	 *	@param		bool			$absolute		...
	 *	@return		string
	 */
	public function getArticlePicture( $bridge, int|string $articleId, bool $absolute = FALSE ): string
	{
		return $this->bridge( $bridge )->getPicture( $articleId, $absolute );
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		mixed			$bridge			Bridge ID or class name
	 *	@param		int|string		$articleId		Article ID
	 *	@param		integer			$amount
	 *	@return		float
	 */
	public function getArticlePrice( $bridge, int|string $articleId, int $amount = 1 ): float
	{
		return $this->bridge( $bridge )->getPrice( $articleId, $amount );
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		mixed			$bridge			Bridge ID or class name
	 *	@param		int|string		$articleId		Article ID
	 *	@param		integer			$amount
	 *	@return		float
	 */
	public function getArticleTax( $bridge, int|string $articleId, int $amount = 1 ): float
	{
		return $this->bridge( $bridge )->getTax( $articleId, $amount );
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		mixed			$bridge			Bridge ID or class name
	 *	@param		int|string		$articleId		Article ID
	 *	@return		string
	 */
	public function getArticleTitle( $bridge, int|string $articleId ): string
	{
		return $this->bridge( $bridge )->getTitle( $articleId );
	}

	public function getBridge( object|int|string $bridgeIdOrClass )
	{
		if( is_int( $bridgeIdOrClass ) || (string)(int) $bridgeIdOrClass == $bridgeIdOrClass ){
			if( !array_key_exists( (int) $bridgeIdOrClass, $this->bridges ) )
				throw new RuntimeException( 'Given bridge ID '.$bridgeIdOrClass.' is not registered' );
			return $this->bridges[(int) $bridgeIdOrClass];
		}
		if( !is_string( $bridgeIdOrClass ) && !is_int( $bridgeIdOrClass ) )
			throw new InvalidArgumentException( 'Must be of string or integer' );
		if( !array_key_exists( $bridgeIdOrClass, $this->bridgeClasses ) )
			throw new InvalidArgumentException( 'Bridge class "'.$bridgeIdOrClass.'" is unknown' );
		return $this->bridgeClasses[$bridgeIdOrClass];
	}

	/**
	 *	Returns bridge class from bridge object.
	 *	@param		Logic_ShopBridge_Abstract|int|string $bridgeIdOrObject
	 *	@return		string
	 *	@throws		InvalidArgumentException		if a given object is not a bridge object
	 */
	public function getBridgeClass( Logic_ShopBridge_Abstract|int|string $bridgeIdOrObject ): string
	{
		if( is_object( $bridgeIdOrObject ) )
			return $bridgeIdOrObject->getBridgeClass();
		if( is_int( $bridgeIdOrObject ) || (string)(int) $bridgeIdOrObject === $bridgeIdOrObject ){
			if( !array_key_exists( (int) $bridgeIdOrObject, $this->bridges ) )
				throw new RuntimeException( 'Given bridge ID '.$bridgeIdOrObject.' is not registered' );
			return $this->bridges[(int) $bridgeIdOrObject]->data->class;
		}
		throw new InvalidArgumentException( 'Invalid bridge data type: Needs ID or object' );
	}

	public function getBridgeId( Logic_ShopBridge_Abstract|int|string $bridgeClassOrObject ): string
	{
		if( is_object( $bridgeClassOrObject ) )
			$bridgeClassOrObject	= $this->getBridgeClass( $bridgeClassOrObject );
		if( !is_string( $bridgeClassOrObject ) )
			throw new InvalidArgumentException( 'Must be of string' );
		if( !array_key_exists( $bridgeClassOrObject, $this->bridgeClasses ) )
			throw new InvalidArgumentException( 'Bridge class "'.$bridgeClassOrObject.'" is unknown' );
		return $this->bridgeClasses[$bridgeClassOrObject]->data->bridgeId;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		mixed		$bridgeIdOrClass	Bridge ID or class name
	 *	@return		Logic_ShopBridge_Abstract
	 */
	public function bridge( $bridgeIdOrClass ): object
	{
		return $this->getBridgeObject( $bridgeIdOrClass );
	}

	public function getBridgeObject( $bridgeIdOrClass ): object
	{
		if( is_int( $bridgeIdOrClass ) || (int) $bridgeIdOrClass == $bridgeIdOrClass ){
			if( !isset( $this->bridges[(int) $bridgeIdOrClass] ) )
				throw new RuntimeException( 'Bridge with ID '.$bridgeIdOrClass.' is not registered' );
			return $this->bridges[(int) $bridgeIdOrClass]->object;
		}
		if( !isset( $this->bridgeClasses[$bridgeIdOrClass] ) )
			throw new RuntimeException( 'Bridge of type '.$bridgeIdOrClass.' is not registered' );
		return $this->bridgeClasses[$bridgeIdOrClass]->object;
	}

	/**
	 *	Returns map of bridge classes and instances.
	 *	@access		public
	 *	@return		array
	 */
	public function getBridges(): array
	{
		return $this->bridges;
	}

	protected function readBridges(): void
	{
		$this->bridges			= [];
		$this->bridgeClasses	= [];
		foreach( $this->model->getAll() as $bridge ){
			$className	= "Logic_ShopBridge_".$bridge->class;
			$bridge		= (object) [
				'data'		=> $bridge,
				'status'	=> -1,
				'object'	=> NULL,
			];
			if( !class_exists( $className ) ){
				$this->env->getMessenger()->noteFailure( 'Shop bridge "'.$bridge->data->class.'" is not existing.' );
			}
			else{
				$bridge->object		= new $className( $this->env, $this );
				$bridge->status		= 1;
			}
			$this->bridges[$bridge->data->bridgeId] = $bridge;
			$this->bridgeClasses[$bridge->data->class]	= $this->bridges[$bridge->data->bridgeId];
		}
	}
}
