<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Logic;

class Logic_Form_Transfer_DataMapper extends Logic
{
	/**
	 *	Applies transfer rules on form data and returns resulting output data.
	 *
	 *	@access		public
	 *	@param		array		$formData		Map of form data
	 *	@param		object		$rules			Rule object to apply
	 *	@return		array
	 */
	public function applyRulesToFormData( array $formData, object $rules ): array
	{
		$input	= new Dictionary( $formData );
		$output	= new Dictionary();

		$this->applySets( $rules->set ?? [], $input, $output );
		$this->applyTranslation( (array) $rules->translate ?? [], $input, $output );
		$this->applyFilters( $rules->filter ?? [], $input, $output );
		$this->applyDatabaseSearches( $rules->db ?? [], $input, $output );

		$this->applyCreations( $rules->create ?? [], $input, $output );
		$this->applyCopies( $rules->copy ?? [], $input, $output );
		$this->applyMappings( $rules->map ?? [], $input, $output );

		return $output->getAll();
	}

	//  --  PROTECTED  --  //

	/**
	 *	...
	 *	@access		protected
	 *	@param		array			$creations		List of creation rules
	 *	@param		Dictionary		$input			Input data dictionary
	 *	@param		Dictionary		$output		Output data dictionary
	 */
	protected function applyCreations( array $creations, Dictionary $input, Dictionary $output )
	{
		foreach( $creations as $fieldName => $parameters ){
			$buffer	= '';
			foreach( $parameters->lines as $line ){
				$glue	= strlen( $buffer ) ? PHP_EOL : '';
				$buffer	.= $glue.$line;
			}
			$functions	= ['datetime', 'date', 'time'];
			foreach( $functions as $function ){
				$regex	= '/\[!'.preg_quote( $function, '/' ).'\]/';
				$value	= $this->resolveFunction( $function );
				$buffer	= preg_replace( $regex, $value, $buffer );
			}
			foreach( $input->getAll() as $key => $value ){
				$regex	= '/\[@'.preg_quote( $key, '/' ).'\]/';
				$buffer	= preg_replace( $regex, $value, $buffer );
			}
			$input->set( $fieldName, $buffer );
		}
	}

	/**
	 *	Applies filter rules.
	 *
	 *	@access		protected
	 *	@param		array			$filters	Map of filter rules
	 *	@param		Dictionary		$input		Input data dictionary
	 *	@param		Dictionary		$output		Output data dictionary
	 *	@return		void
	 */
	protected function applyFilters( array $filters, Dictionary $input, Dictionary $output )
	{
		foreach( $filters as $fieldName => $parameters ){
			if( !$input->has( $fieldName ) ){
				if( isset( $parameters->onEmpty ) && $parameters->onEmpty === "skip" )
					continue;
				if( isset( $parameters->onEmpty ) && $parameters->onEmpty === "set" )
					$input->set( $fieldName, '' );
				else
					throw new RuntimeException( 'Filter: No data available for "'.$fieldName.'"' );
			}
			$inputValue	= $input->get( $fieldName );
			if( empty( $parameters->condition ) || empty( $parameters->action ) ){
				continue;
			}
			$condition	= $parameters->condition;
			if( empty( $condition->operator ) || !isset( $condition->match ) ){
				continue;
			}
			$truth	= FALSE;
			$match	= $this->resolveValue( $condition->match, $input );
			switch( strtolower( $condition->operator ) ){
				case 'equals':
				case '==':
					$truth	= $inputValue == $condition->match;
					break;
				case 'startswith':
				case '[=':
					$cut	= substr( $inputValue, 0, strlen( $condition->match ) );
					$truth	= $cut === $condition->match;
					break;
				case 'endswith':
				case '=]':
					$cut	= substr( $inputValue, -1 * strlen( $condition->match ) );
					$truth	= $cut === $condition->match;
					break;
				case 'contains':
				case '~=':
					$truth	= strpos( $inputValue, $condition->match ) !== FALSE;
					break;
				case 'regex':
					$truth	= preg_match( $condition->match, $inputValue );
					break;
			}
			if( $truth ){
				if( !empty( $parameters->action ) ){
					$action		= $parameters->action;
					if( !empty( $action->operation ) ){
						$operation	= strtolower( $action->operation );
						$value		= $action->value ?? $input->get( $fieldName );
						$value		= $this->resolveOperation( $operation, $value, $input );
						$target		= $action->to ?? $fieldName;
						NULL === $value ? $input->remove( $target ) : $input->set( $target, $value );
					}
				}
			}
			else{
				if( !empty( $parameters->else ) ){
					$action		= $parameters->else;
					$operation	= strtolower( $action->operation ?? 'set' );
					$value		= $action->value ?? $input->get( $fieldName );
					$value		= $this->resolveOperation( $operation, $value, $input );
					$target		= $action->to ?? $fieldName;
					NULL === $value ? $input->remove( $target ) : $input->set( $target, $value );
				}
			}
		}
	}

	/**
	 *	Applies filter rules.
	 *
	 *	@access		protected
	 *	@param		array			$searches	Map of database searches
	 *	@param		Dictionary		$input		Input data dictionary
	 *	@param		Dictionary		$output		Output data dictionary
	 *	@return		void
	 */
	protected function applyDatabaseSearches( array $searches, Dictionary $input, Dictionary $output )
	{
		foreach( $searches as $fieldName => $parameters ){
			if( empty( $parameters->table ) )
				continue;
			if( is_scalar( $parameters->table ) )
				$parameters->table	= [$parameters->table];
			$tables		= implode( ', ', $parameters->table );
			if( empty( $parameters->index ) )
				throw new RuntimeException( 'DB: No index defined for target field "'.$fieldName.'"' );

			$indices	= [1];
			foreach( $parameters->index as $indexColumn => $indexSource ){
				$indexValue	= $this->resolveValue( $indexSource, $input );
				if( $indexValue === NULL ){
					if( !isset( $parameters->onEmpty ) )
						throw new RuntimeException( 'DB: No data available for "'.$indexSource.'", used as index source for target field "'.$fieldName.'" on table(s) '.$tables );
					$indexValue = $parameters->onEmpty;
				}
				$indices[]	= $indexColumn.' = "'.$indexValue.'"';
			}
			$query	= 'SELECT '.$parameters->column.' AS value FROM '.$tables.' WHERE '.join( ' AND ', $indices );
			$dbc	= $this->env->getDatabase();
			$result	= $dbc->query( $query )->fetch( PDO::FETCH_OBJ );
			if( empty( $result ) ){
				if( !isset( $parameters->onEmpty ) )
					throw new RuntimeException( 'DB: No table data found for index source of target field "'.$fieldName.'" from table(s) '.$tables );
				$result = (object) [ 'value' => $parameters->onEmpty ];
			}
			if( !empty( $parameters->to ) && in_array( $parameters->to, ['input', 'request'], TRUE ) )
				$input->set( $fieldName, $result->value );
			else
				$output->set( $fieldName, $result->value );
		}
	}

	/**
	 *	Applies translator rules.
	 *
	 *	@access		protected
	 *	@param		array			$translates	Map of translate rules
	 *	@param		Dictionary		$input		Input data dictionary
	 *	@param		Dictionary		$output		Output data dictionary
	 *	@return		void
	 */
	protected function applyTranslation( array $translates, Dictionary $input, Dictionary $output )
	{
		foreach( $translates as $fieldName => $map ){
			if( $input->has( $fieldName ) ){
				$value		= $input->get( $fieldName );
				$translate	= new Dictionary( (array) $map );
				if( $translate->has( $value ) ){
					$input->set( $fieldName, $translate->get( $value ) );
				}
			}
		}
	}

	/**
	 *	Applies filter rules.
	 *
	 *	@access		protected
	 *	@param		array			$copies		Map of filter rules
	 *	@param		Dictionary		$input		Input data dictionary
	 *	@param		Dictionary		$output		Output data dictionary
	 *	@return		void
	 */
	protected function applyCopies( array $copies, Dictionary $input, Dictionary $output )
	{
		foreach( $copies as $fieldName )
			if( $input->has( $fieldName ) )
				$output->set( $fieldName, $input->get( $fieldName ) );
	}

	/**
	 *	Applies mappings by copying input values to output fields.
	 *
	 *	@access		protected
	 *	@param		array			$map		Map of input to output field names
	 *	@param		Dictionary		$input		Input data dictionary
	 *	@param		Dictionary		$output		Output data dictionary
	 *	@return		void
	 */
	protected function applyMappings( array $map, Dictionary $input, Dictionary $output )
	{
		foreach( $map as $inputFieldName => $outputFieldName )
			if( $input->has( $inputFieldName ) )
				$output->set( $outputFieldName, $input->get( $inputFieldName ) );
	}

	/**
	 *	Applies map of output values to set directly.
	 *
	 *	@access		protected
	 *	@param		array			$map		Map of output values to set
	 *	@param		Dictionary		$input		Input data dictionary
	 *	@param		Dictionary		$output		Output data dictionary
	 *	@return		void
	 */
	protected function applySets( array $map, Dictionary $input, Dictionary $output )
	{
		foreach( $map as $name => $value ){
			$output->set( $name, $this->resolveValue( $value, $input ) );
		}
	}

	/**
	 *	Resolves value field.
	 *	Looks for prefixes:
	 *	- ! => execute a function
	 *	- @ => get from input data by name
	 *	No prefix found, return given value.
	 *
	 *	@access		public
	 *	@param		string			$value
	 *	@param		Dictionary		$input		Input data dictionary
	 *	@return		string|NULL
	 */
	protected function resolveValue( string $value, Dictionary $input ): ?string
	{
		$prefix	= substr( $value, 0, 1 );
		switch( $prefix ){
			case '!':
				return $this->resolveFunction( substr( $value, 1 ) );
			case '@':
				return $input->get( substr( $value, 1 ) );
			default:
				return $value;
		}
	}

	/**
	 *	Resolves function and returns created value.
	 *	Work in progress. Needs to be extendable.
	 *
	 *	@access		protected
	 *	@param		string		$function		Function to execute
	 *	@param		mixed		$arguments		Function arguments if configured
	 *	@return		string|NULL
	 */
	protected function resolveFunction( string $function, $arguments = '' ): ?string
	{
		switch( $function ){
			case 'datetime':
				return date( 'Y-m-d H:i:s' );
			case 'date':
				return date( 'Y-m-d' );
			case 'time':
				return date( 'H:i:s' );
			default:
				return NULL;
		}
	}

	/**
	 *	Resolves configured operation and returns operated value.
	 *
	 *	@access		protected
	 *	@param		string		$operation		...
	 *	@param		string		$value			Input value, if needed
	 *	@param		Dictionary	$input			Map of form input data
	 *	@return		string|integer|NULL
	 */
	protected function resolveOperation( string $operation, string $value, Dictionary $input )
	{
		switch( strtolower( $operation ) ){
			case 'set':
				return $this->resolveValue( $value, $input );
			case 'inc':
			case 'increment':
			case '++':
				return ( (int) $value ) + 1;
			case 'dec':
			case 'decrement':
			case '--':
				return ( (int) $value ) - 1;
			case 'remove':
			case 'delete':
			default:
				return NULL;
		}
	}
}
