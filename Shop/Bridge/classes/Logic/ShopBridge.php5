<?php
class Logic_ShopBridge{

	/**	@var	CMF_Hydrogen_Environment_Abstract	$env			Environment instance */
	protected $env;

	/**	@var	array								$bridges		Map of registered bridges classes */
	protected $bridges								= array();

	/**	@var	array								$sources		Map of registered bridge objects */
	protected $sources								= array();

	/**	@var	string								$pathToBidges */
	static public $pathToBidges						= "classes/Logic/ShopBridge/";

	/**
	 *	Constructor.
	 *	Autodetects available bridge classes.
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment_Abstract	$env	Environment
	 *	@return		void
	 */
	public function __construct( CMF_Hydrogen_Environment_Abstract $env ){
		$this->env	= $env;
		$model		= new Model_Shop_Bridge( $env );
		foreach( $model->getAll() as $bridge ){
			$this->bridges[$bridge->bridgeId] = $bridge->class;
			$this->sources[$bridge->class]	= NULL;
		}
		foreach( new DirectoryIterator( self::$pathToBidges ) as $entry ){							//  iterate list of classes in bridge class folder
			if( $entry->isDir() || $entry->isDot() )												//  exclude folders and folder links
				continue;
			$class	= pathinfo( $entry->getFilename(), PATHINFO_FILENAME );							// 
			if( $class === "Abstract" )
				continue;
			if( !in_array( $class, array_keys( $this->sources ) ) ){								//  
				$bridgeId	= $model->add( array( 'class' => $class, 'createdAt' => time() ) );		//  
				$this->bridges[$bridgeId]	= $class;												//  
				$this->sources[$class]	= NULL;														//  note bridge class key but do not create bridge object yes
			}
		}
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		integer		$articleId
	 *	@return		string
	 */
	public function getArticlePicture( $source, $articleId ){
		return $this->getSource( $source )->getThumbnailUri( $articleId );
	}
	
	/**
	 *	...
	 *	@access		public
	 *	@param		integer		$articleId
	 *	@param		integer		$amount
	 *	@return		float
	 */
	public function getArticlePrice( $source, $articleId, $amount = 1 ){
		return $this->getSource( $source )->getPrice( $articleId, $amount );
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		integer		$articleId
	 *	@param		integer		$amount
	 *	@return		float
	 */
	public function getArticleTax( $source, $articleId, $amount = 1 ){
		return $this->getSource( $source )->getTax( $articleId, $amount );
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		integer		$articleId
	 *	@return		string
	 */
	public function getArticleTitle( $source, $articleId ){
		return $this->getSource( $source )->getTitle( $articleId );
	}

	public function getBridgeClass( $bridge ){
		if( is_object( $bridge ) )
			if( $bridge instanceof Logic_ShopBridge_Abstract )
				return $bridge->getBridgeClass();
		throw new InvalidArgumentException( 'Given source object is not a valid bridge object (must extend Logic_ShopBridge_Abstract)' );
	}

	public function getBridgeId( $bridgeClassOrObject ){
		if( is_object( $bridgeClassOrObject ) )
			$bridgeClassOrObject	= $this->getBridgeClass( $bridgeClassOrObject );
		if( !is_string( $bridgeClassOrObject ) )
			throw new InvalidArgumentException( 'Must be of string' );
		if( !in_array( $bridgeClassOrObject, $this->bridges ) )
			throw new InvalidArgumentException( 'Bridge class "'.$bridgeClassOrObject.'" is unknown' );
		$bridges	= array_flip( $this->bridges );
		return $bridges[$bridgeClassOrObject];
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		string		$source
	 *	@return		Logic_ShopBridge_Abstract
	 */
	public function getSource( $bridgeIdOrClass ){
		if( is_int( $bridgeIdOrClass ) )
			return $this->getSourceFromBridge( $bridgeIdOrClass );	//  
		if( !in_array( $bridgeIdOrClass, $this->bridges ) )
			throw new InvalidArgumentException( 'Invalid source "'.$source.'"' );
		if( !is_object( $this->sources[$bridgeIdOrClass] ) ){
			$className	= "Logic_ShopBridge_".$bridgeIdOrClass;
			if( !class_exists( $className ) )
				throw new RuntimeException( 'Bridge class "'.$className.'" is not existing' );
			$this->sources[$bridgeIdOrClass]	= new $className( $this->env, $this );
		}
		return $this->sources[$bridgeIdOrClass];
	}

	public function getSourceFromBridge( $bridgeId ){
		if( !in_array( (int)$bridgeId, array_keys( $this->bridges ) ) )
			throw new InvalidArgumentException( 'Bridge with ID '.$bridgeId.' is not exising' );
		return $this->getSource( $this->bridges[(int)$bridgeId] );
	}

	public function getSourceBridges(){
		return $this->bridges;
	}

	public function getSources(){
		return $this->sources;
	}
}
?>
