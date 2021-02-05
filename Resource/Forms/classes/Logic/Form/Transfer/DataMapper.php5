<?php
class Logic_Form_Transfer_DataMapper extends CMF_Hydrogen_Logic
{
	protected $env;

	public function applyRulesToFormData( $formData, $rules ): array
	{
		$input	= new ADT_List_Dictionary( $formData );
		$output	= new ADT_List_Dictionary();

		$this->applyTranslation( $rules->translate ?? [], $input, $output );
		$this->applyFilters( $rules->filter ?? [], $input, $output );
		$this->applyDatabaseSearches( $rules->db ?? [], $input, $output );

		$this->applySets( $rules->set ?? [], $input, $output );
		$this->applyCopies( $rules->copy ?? [], $input, $output );
		$this->applyMappings( $rules->map ?? [], $input, $output );

		return $output->getAll();
	}

	//  --  PROTECTED  --  //

	/**
	 *	Applies filter rules.
	 *	@access		protected
	 *	@param		array				$filters	Map of filter rules
	 *	@param		ADT_List_Dictionary	$input		Input data dictionary
	 *	@param		ADT_List_Dictionary	$output		Output data dictionary
	 *	@return		void
	 */
	protected function applyFilters( $filters, $input, $output )
	{
		foreach( $filters as $fieldName => $parameters ){
			if( !$input->has( $fieldName ) ){
				if( isset( $parameters->onEmpty ) && $parameters->onEmpty === "skip" )
					continue;
				throw new RuntimeException( 'Filter: No data available for "'.$fieldName.'"' );
			}
			$inputValue	= $input->get( $fieldName );
			if( empty( $parameters->condition ) || empty( $parameters->action ) ){
				continue;
			}
			$condition	= $parameters->condition;
			if( empty( $condition->operator ) || empty( $condition->match ) ){
				continue;
			}
			$truth	= FALSE;
			$match	= $condition->match;
			if( substr( $condition->match, 0, 1 ) === '@' ){
				$matchField	= substr( $condition->match, 1 );
				if( $input->has( $matchField ) )
					$match = $input->get( $matchField );
			}
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
			if( !$truth )
				continue;
			$action	= $parameters->action;
			if( !empty( $action->operation ) ){
				switch( strtolower( $action->operation ) ){
					case 'set':
						$toField	= $fieldName;
						if( !empty( $action->to ) )
							$toField	= $action->to;
						if( !empty( $action->value ) ){
							$value	= $action->value;
							if( substr( $action->value, 0, 1 ) === '@' ){
								$valueField	= substr( $action->value, 1 );
								if( $input->has( $valueField ) )
									$value = $input->get( $valueField );
							}
							$input->set( $toField, $value );
						}
						break;
					case 'inc':
					case 'increment':
					case '++':
						$value	= (int) $input->get( $fieldName ) + 1;
						$input->set( $fieldName, (string) $value );
						break;
					case 'dec':
					case 'decrement':
					case '--':
						$value	= (int) $input->get( $fieldName ) - 1;
						$input->set( $fieldName, (string) $value );
						break;
					case 'remove':
					case 'delete':
						$input->remove( $fieldName );
						break;
				}
			}
		}
	}

	/**
	 *	Applies filter rules.
	 *	@access		protected
	 *	@param		array				$filters	Map of filter rules
	 *	@param		ADT_List_Dictionary	$input		Input data dictionary
	 *	@param		ADT_List_Dictionary	$output		Output data dictionary
	 *	@return		void
	 */
	protected function applyDatabaseSearches( $searches, $input, $output )
	{
		foreach( $searches as $fieldName => $parameters ){
			if( empty( $parameters->table ) )
				continue;
			if( is_scalar( $parameters->table ) )
				$parameters->table	= [$parameters->table];
			$tables		= implode( ', ', $parameters->table );
			if( empty( $parameters->index ) )
				throw new RuntimeException( 'DB: No index defined for target field "'.$fieldName.'"' );

			$indices	= array( 1 );
			foreach( $parameters->index as $indexColumn => $indexSource ){
				$indexValue	= $indexSource;
				if( substr( $indexSource, 0, 1 ) === '@' ){
					$indexSourceName	= substr( $indexSource, 1 );
					if( !$input->has( $indexSourceName ) )
						throw new RuntimeException( 'DB: No data available for "'.$indexSourceName.'", uses as index source for target field "'.$fieldName.'"' );
					$indexValue	= $input->get( $indexSourceName );
				}
				$indices[]	= $indexColumn.' = "'.$indexValue.'"';
			}
			$query	= 'SELECT '.$parameters->column.' AS value FROM '.$tables.' WHERE '.join( ' AND ', $indices );
			$dbc	= $this->env->getDatabase();
			$result	= $dbc->query( $query )->fetch( PDO::FETCH_OBJ );
			if( empty( $result ) )
				throw new RuntimeException( 'DB: No table data found for index source of target field "'.$fieldName.'"' );
			if( !empty( $parameters->to ) && $parameters->to === 'request')
				$input->set( $fieldName, $result->value );
			else
				$output->set( $fieldName, $result->value );
		}
	}

	/**
	 *	Applies filter rules.
	 *	@access		protected
	 *	@param		array				$filters	Map of filter rules
	 *	@param		ADT_List_Dictionary	$input		Input data dictionary
	 *	@param		ADT_List_Dictionary	$output		Output data dictionary
	 *	@return		void
	 */
	protected function applyTranslation( $translates, $input, $output )
	{
		foreach( $translates as $fieldName => $map ){
			if( $input->has( $fieldName ) ){
				$value		= $input->get( $fieldName );
				$translate	= new ADT_List_Dictionary( $map );
				if( $translate->has( $value ) ){
					$input->set( $fieldName, $translate->get( $value ) );
					continue;
				}
			}
		}
	}

	/**
	 *	Applies filter rules.
	 *	@access		protected
	 *	@param		array				$filters	Map of filter rules
	 *	@param		ADT_List_Dictionary	$input		Input data dictionary
	 *	@param		ADT_List_Dictionary	$output		Output data dictionary
	 *	@return		void
	 */
	protected function applyCopies( $copies, $input, $output )
	{
		foreach( $copies as $fieldName )
			if( $input->has( $fieldName ) )
				$output->set( $fieldName, $input->get( $fieldName ) );
	}

	/**
	 *	Applies mappings by copying input values to output fields.
	 *	@access		protected
	 *	@param		array				$map		Map of input to output field names
	 *	@param		ADT_List_Dictionary	$input		Input data dictionary
	 *	@param		ADT_List_Dictionary	$output		Output data dictionary
	 *	@return		void
	 */
	protected function applyMappings( $map, $input, $output )
	{
		foreach( $map as $inputFieldName => $outputFieldName )
			if( $input->has( $inputFieldName ) )
				$output->set( $outputFieldName, $input->get( $inputFieldName ) );
	}

	/**
	 *	Applies map of output values to set directly.
	 *	@access		protected
	 *	@param		array				$map		Map of output values to set
	 *	@param		ADT_List_Dictionary	$input		Input data dictionary
	 *	@param		ADT_List_Dictionary	$output		Output data dictionary
	 *	@return		void
	 */
	protected function applySets( $map, $input, $output )
	{
		foreach( $map as $name => $value )
			$output->set( $name, $value );
	}
}
