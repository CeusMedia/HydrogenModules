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
	 *	@return		void
	 */
	protected function applyCreations( array $creations, Dictionary $input, Dictionary $output ): void
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
	 *	@param		array<object>	$filters	Map of filter rules
	 *	@param		Dictionary		$input		Input data dictionary
	 *	@param		Dictionary		$output		Output data dictionary
	 *	@return		void
	 */
	protected function applyFilters( array $filters, Dictionary $input, Dictionary $output ): void
	{
		/**
		 * @var string $fieldName
		 * @var object{condition: string, onEmpty: string, action: ?string, operation: ?string, else: ?string} $parameters
		 */
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
					$truth	= str_contains( $inputValue, (string) $condition->match );
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
	protected function applyDatabaseSearches( array $searches, Dictionary $input, Dictionary $output ): void
	{
		/**
		 * @var string $fieldName
		 * @var object $parameters
		 */
		foreach( $searches as $fieldName => $parameters ){
			if( empty( $parameters->table ) )
				continue;
			if( is_scalar( $parameters->table ) )
				$parameters->table	= [$parameters->table];
			$tables		= implode( ', ', $parameters->table );
			if( empty( $parameters->column ) )
				throw new RuntimeException( 'DB: No table column defined for target field "'.$fieldName.'"' );
			if( empty( $parameters->index ) )
				throw new RuntimeException( 'DB: No index defined for target field "'.$fieldName.'"' );

			$indices	= [];
			foreach( $parameters->index as $indexColumn => $indexSource ){
				$indexValue	= $this->resolveValue( $indexSource, $input );
				if( $indexValue === NULL ){
					if( !isset( $parameters->onEmpty ) ){
						$msg	= 'DB: No data available for "%s", used as index source for target field "%s" on table %s';
						throw new RuntimeException( sprintf( $msg, $indexSource, $fieldName, $tables ) );
					}
					$indexValue = $parameters->onEmpty;
				}
				$indices[]	= $indexColumn.' = "'.$indexValue.'"';
			}
			$query	= 'SELECT '.$parameters->column.' AS value FROM '.$tables.' WHERE '.join( ' AND ', $indices );
			$dbc	= $this->env->getDatabase();
			$result	= $dbc->query( $query )->fetch( PDO::FETCH_OBJ );
			if( empty( $result ) ){
				if( !isset( $parameters->onEmpty ) ){
					$message		= 'DB: Failed to get "%s" by looking for column "%s" in table "%s" having %s';
					$indexString	= join( ' and ', $indices );
					throw new RuntimeException( sprintf( $message, $fieldName, $parameters->column, $tables, $indexString ) );
				}
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
	protected function applyTranslation( array $translates, Dictionary $input, Dictionary $output ): void
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
	protected function applyCopies( array $copies, Dictionary $input, Dictionary $output ): void
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
	protected function applyMappings( array $map, Dictionary $input, Dictionary $output ): void
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
	protected function applySets( array $map, Dictionary $input, Dictionary $output ): void
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
		return match( $prefix ){
			'!'		=> $this->resolveFunction( substr( $value, 1 ) ),
			'@'		=> $input->get( substr( $value, 1 ) ),
			default	=> $value,
		};
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
		return match( $function ){
			'datetime'	=> date( 'Y-m-d H:i:s' ),
			'date'		=> date( 'Y-m-d' ),
			'time'		=> date( 'H:i:s' ),
			default		=> NULL,
		};
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
	protected function resolveOperation( string $operation, string $value, Dictionary $input ): string|int|NULL
	{
		return match( strtolower( $operation ) ){
			'set'						=> $this->resolveValue( $value, $input ),
			'inc', 'increment', '++'	=> ( (int) $value ) + 1,
			'dec', 'decrement', '--'	=> ( (int) $value ) - 1,
			default						=> NULL,
		};
	}
}
