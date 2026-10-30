<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Logic;

class Logic_Form_Transfer_DataProcessor extends Logic
{
	protected Dictionary $data;
	protected array $processedFields	= [];

	public function process( array $formData, array $script ): array
	{
		$this->data				= new Dictionary( $formData );
		$this->processedFields	= [];

		foreach( $script as $item ){
			foreach( $item as $command => $listOrObject ){
				$fields	= (array) $listOrObject;
				match( $command ){
					'keep'		=> $this->processKeep( $fields ),
					'set'		=> $this->processSet( $fields ),
					'copy'		=> $this->processCopy( $fields ),
					'operate'	=> $this->processOperate( $fields ),
					'move'		=> $this->processMove( $fields ),
					'translate'	=> $this->processTranslate( $fields ),
					'query'		=> $this->processQuery( $fields ),
					'remove'	=> $this->processRemove( $fields ),
					'create'	=> $this->processCreate( $fields ),
					default		=> throw new InvalidArgumentException( 'Invalid command: '.$command ),
				};
			}
		}
		return array_intersect_key( array_filter( $this->data->getAll() ), $this->processedFields );
	}


	//  --  PROTECTED  --  //


	protected function __onInit(): void
	{
		$this->data	= new Dictionary();
	}

	protected function processQuery( array $fields ): void
	{
		foreach( $fields as $fieldName => $query ){
			if( empty( $query->table ) )
				continue;
			if( is_scalar( $query->table ) )
				$query->table	= [$query->table];
			$tables		= implode( ', ', $query->table );
			if( empty( $query->column ) )
				throw new RuntimeException( 'DB: No table column defined for target field "'.$fieldName.'"' );
			if( empty( $query->index ) )
				throw new RuntimeException( 'DB: No index defined for target field "'.$fieldName.'"' );

			$indices	= [];
			foreach( $query->index as $indexColumn => $indexSource ){
				$indexValue	= $this->resolveValue( $indexSource );
				if( $indexValue === NULL ){
					if( !isset( $query->onEmpty ) ){
						$msg	= 'DB: No data available for "%s", used as index source for target field "%s" on table %s';
						throw new RuntimeException( sprintf( $msg, $indexSource, $fieldName, $tables ) );
					}
					$indexValue = $query->onEmpty;
				}
				$indices[]	= $indexColumn.' = "'.$indexValue.'"';
			}
			$sql	= 'SELECT '.$query->column.' AS value FROM '.$tables.' WHERE '.join( ' AND ', $indices );
			$dbc	= $this->env->getDatabase();
			$result	= $dbc->query( $sql )->fetch( PDO::FETCH_OBJ );
			if( empty( $result ) ){
				if( !isset( $query->onEmpty ) ){
					$message		= 'DB: Failed to get "%s" by looking for column "%s" in table "%s" having %s';
					$indexString	= join( ' and ', $indices );
					throw new RuntimeException( sprintf( $message, $fieldName, $query->column, $tables, $indexString ) );
				}
				$result = (object) [ 'value' => $query->onEmpty ];
			}
			$this->data->set( $fieldName, $result->value );
			$this->processedFields[$fieldName]	= 'query';
		}
	}

	protected function processKeep( array $fields ): void
	{
		foreach( $fields as $fieldName )
			$this->processedFields[$fieldName]	= 'keep';
	}

	protected function processSet( array $fields ): void
	{
		foreach( $fields as $fieldName => $value ){
			$this->data->set( $fieldName, $this->resolveValue( $value ) );
			$this->processedFields[$fieldName]	= 'set';
		}
	}

	protected function processCopy( array $fields ): void
	{
		foreach( $fields as $origin => $target ){
			$this->data->set( $target, $this->data->get( $origin ) );
			$this->processedFields[$target]	= 'copy';
		}
	}

	protected function processOperate( array $fields ): void
	{
		foreach( $fields as $fieldName => $parametersObjectOrList ){
			$this->processedFields[$fieldName]	= 'query';
			if( is_array( $parametersObjectOrList ) ){
				$listOfParameters	= $parametersObjectOrList;
				foreach( $listOfParameters as $parameters )
					/** @var object{condition: string, onEmpty: string, action: ?object{operation: ?string, to: ?string, value: int|string|NULL}, operation: ?string, else: ?string} $parameters */
					$this->applyFilterOnInputField( $fieldName, $parameters );
			}
			if( is_object( $parametersObjectOrList ) ){
				/** @var object{condition: string, onEmpty: string, action: ?object{operation: ?string, to: ?string, value: int|string|NULL}, operation: ?string, else: ?string} $parameters */
				$parameters	= $parametersObjectOrList;
				$this->applyFilterOnInputField( $fieldName, $parameters );
			}
		}
	}

	protected function processMove( array $fields ): void
	{
		foreach( $fields as $origin => $target ){
			$this->data->set( $target, $this->data->get( $origin ) );
			$this->data->remove( $origin );
			$this->processedFields[$target]	= 'move';
			unset( $this->processedFields[$origin] );
		}
	}

	protected function processTranslate( array $fields ): void
	{
		foreach( $fields as $fieldName => $replacements ){
			foreach( get_object_vars( $replacements ) as $origin => $target ){
				if( (string) $this->data->get( $fieldName ) === (string) $origin )
					$this->data->set( $fieldName, $this->resolveValue( $target ) );
			}
			$this->processedFields[$fieldName]	= 'translate';
		}
	}

	protected function processRemove( array $fieldNames ): void
	{
		foreach( $fieldNames as $fieldName ){
			$this->data->remove( $fieldName );
			unset( $this->processedFields[$fieldName] );
		}
	}

	protected function processCreate( array $fields ): void
	{
		foreach( $fields as $fieldName => $parameters ){
			$buffer	= '';
			foreach( $parameters->lines as $line ){
				$glue	= strlen( $buffer ) ? PHP_EOL : '';
				$buffer	.= $glue.$line;
			}
			$functions	= ['datetime', 'date', 'time'];
			foreach( $functions as $function ){
				$regex	= '/\[!'.preg_quote( $function, '/' ).']/';
				$value	= $this->resolveFunction( $function );
				$buffer	= preg_replace( $regex, $value, $buffer );
			}
			foreach( $this->data->getAll() as $key => $value ){
				$regex	= '/\[@'.preg_quote( $key, '/' ).']/';
				$buffer	= preg_replace( $regex, $value, $buffer );
			}
			$this->data->set( $fieldName, $buffer );
		}
	}

	protected function applyFilterOnInputField( string $fieldName, object $parameters ): void
	{
		if( !$this->data->has( $fieldName ) ){
			if( isset( $parameters->onEmpty ) && $parameters->onEmpty === "skip" )
				return;
			if( isset( $parameters->onEmpty ) && $parameters->onEmpty === "set" )
				$this->data->set( $fieldName, '' );
			else
				throw new RuntimeException( 'Filter: No data available for "'.$fieldName.'"' );
		}
		if( empty( $parameters->condition ) || empty( $parameters->action ) )
			return;

		$condition	= $parameters->condition;
		if( empty( $condition->operator ) || !isset( $condition->match ) )
			return;
		$truth	= $this->evaluateCondition( $condition, $fieldName );

		if( $truth ){
			$action		= $parameters->action;
			if( !empty( $action->operation ) ){
				$operation	= strtolower( $action->operation );
				$value		= $action->value ?? $this->data->get( $fieldName );
				$value		= $this->resolveOperation( $operation, $value );
				$target		= $action->to ?? $fieldName;
				NULL === $value ? $this->data->remove( $target ) : $this->data->set( $target, $value );
			}
		}
		else{
			if( !empty( $parameters->else ) ){
				$action		= $parameters->else;
				$operation	= strtolower( $action->operation ?? 'set' );
				$value		= $action->value ?? $this->data->get( $fieldName );
				$value		= $this->resolveOperation( $operation, $value );
				$target		= $action->to ?? $fieldName;
				NULL === $value ? $this->data->remove( $target ) : $this->data->set( $target, $value );
			}
		}
	}

	protected function evaluateCondition( object $condition, string $fieldName ): bool
	{
		$truth		= FALSE;
		$inputValue	= $this->data->get( $fieldName );
		$match		= $this->resolveValue( $condition->match );
		switch( strtolower( $condition->operator ) ){
			case 'same':
			case '==':
				$truth	= $inputValue == $match;
				break;
			case 'equals':
			case '===':
				$truth	= $inputValue === $match;
				break;
			case 'startswith':
			case '[=':
				$cut	= substr( $inputValue, 0, strlen( $match ) );
				$truth	= $cut === $match;
				break;
			case 'endswith':
			case '=]':
				$cut	= substr( $inputValue, -1 * strlen( $match ) );
				$truth	= $cut === $match;
				break;
			case 'contains':
			case '~=':
				$truth	= str_contains( $inputValue, (string) $match );
				break;
			case 'regex':
				$truth	= preg_match( $match, $inputValue );
				break;
		}
		return $truth;
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
	 *	@return		string|NULL
	 */
	protected function resolveValue( string $value ): ?string
	{
		$prefix	= substr( $value, 0, 1 );
		return match( $prefix ){
			'!'		=> $this->resolveFunction( substr( $value, 1 ) ),
			'@'		=> $this->data->get( substr( $value, 1 ) ),
			default	=> $value,
		};
	}

	/**
	 *	Resolves function and returns created value.
	 *	Work in progress. Needs to be extendable.
	 *
	 *	@access		protected
	 *	@param		string		$function		Function to execute
	 *	@return		string|NULL
	 */
	protected function resolveFunction( string $function ): ?string
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
	 *	@return		string|integer|NULL
	 */
	protected function resolveOperation( string $operation, string $value ): string|int|NULL
	{
		return match( strtolower( $operation ) ){
			'set'						=> $this->resolveValue( $value ),
			'inc', 'increment', '++'	=> ( (int) $value ) + 1,
			'dec', 'decrement', '--'	=> ( (int) $value ) - 1,
			default						=> NULL,
		};
	}
}
