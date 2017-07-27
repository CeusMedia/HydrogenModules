<?php
class Logic_Limiter{

	protected $env;

	protected $rules;

	static protected $instance;

	const OPERATION_BOOLEAN				= 0;
	const OPERATION_COMPARE_NUMBER		= 1;

	protected function __construct( CMF_Hydrogen_Environment $env ){
		$this->env			= $env;
		$this->moduleConfig	= $env->getConfig()->getAll( 'module.resource_limiter.', TRUE );
		$this->enabled		= $this->moduleConfig->get( 'enabled' );
		$this->rules		= new ADT_List_Dictionary();
		$this->__onInit();
	}

	protected function __clone(){}

	public function __onInit(){
//		$this->env->getCaptain()->callHook( 'Limiter', 'registerLimits', $this );
	}

	public function allows( $key, $value = NULL, $operation = self::OPERATION_COMPARE_NUMBER ){
		if( !$this->rules->has( $key ) )
			return TRUE;
		if( $value === NULL )
			$operation	= self::OPERATION_BOOLEAN;
		switch( $operation ){
			case self::OPERATION_BOOLEAN:
				return (bool) $this->rules->get( $key );
			case self::OPERATION_COMPARE_NUMBER:
				return $this->rules->get( $key ) >= $value;
		}
	}

	public function denies( $key, $value = NULL, $operation = self::OPERATION_COMPARE_NUMBER ){
		if( !$this->rules->has( $key ) )
			return FALSE;
		return !$this->allows( $key, $value, $operation );
	}

	static public function getInstance( $env ){
		if( !self::$instance )
			self::$instance		= new self( $env );
		return self::$instance;
	}

	public function get( $key ){
		return $this->rules->get( $key );
	}

	/**
	 *	Returns all rules of limiter as an array.
	 *	Using a filter prefix, all rules with keys starting with prefix are returned.
	 *	Attention: A given prefix will be cut from rule keys.
	 *	By default an array is returned. Alternatively another dictionary can be returned.
	 *	@access		public
	 *	@param		string		$prefix			Prefix to filter keys, e.g. "mail." for all rules starting with "mail."
	 *	@param		boolean		$asDictionary	Flag: return list as dictionary object instead of an array
	 *	@param		boolean		$caseSensitive	Flag: return list with lowercase rule keys or dictionary with no case sensitivy
	 *	@return		array|ADT_List_Dictionary	Map or dictionary object containing all or filtered rules
	 */
	public function getAll( $prefix = NULL, $asDictionary = FALSE, $caseSensitive = TRUE ){
		return $this->rules->getAll( $prefix, $asDictionary, $caseSensitive );
	}

	public function getRules(){
		return $this->rules;
	}

	public function has( $key ){
		return $this->rules->has( $key );
	}

	public function index(){
		return $this->rules->getAll();
	}

	public function set( $key, $value ){
		if( !$this->enabled )
			return NULL;
		return $this->rules->set( $key, $value );
	}
}
?>
