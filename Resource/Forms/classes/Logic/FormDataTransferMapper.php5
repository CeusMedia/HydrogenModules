<?php
class Logic_FormDataTransferMapper extends CMF_Hydrogen_Logic
{
	protected $env;
	protected $formData		= [];

	public function applyRulesToFormData( $formData, $rules ): array
	{
		$transferData	= [];
		if( !empty( $rules->db ) ){
			$dbc	= $this->env->getDatabase();
			foreach( $rules->db as $fieldName => $parameters ){
				$indices	= array( 1 );
				if( empty( $parameters->index ) )
					throw new RuntimeException( 'No index defined for target field "'.$fieldName.'"' );
				foreach( $parameters->index as $indexColumn => $sourceDataName ){
					if( empty( $formData[$sourceDataName] ) )
						throw new RuntimeException( 'No data available for index source "'.$sourceDataName.'" of target field "'.$fieldName.'"' );
					$indices[]	= $indexColumn.' = "'.$formData[$sourceDataName].'"';
				}
				$query	= 'SELECT '.$parameters->column.' AS value FROM '.$parameters->table.' WHERE '.join( ' AND ', $indices );
				$result	= $dbc->query( $query )->fetch( PDO::FETCH_OBJ );
				if( empty( $result ) )
					throw new RuntimeException( 'No table data found for index source of target field "'.$fieldName.'"' );
				$transferData[$fieldName]	= $result->value;
			}
		}
		if( !empty( $rules->copy ) ){
			foreach( $rules->copy as $fieldName ){
				$transferData[$fieldName]	= NULL;
				if( !empty( $formData[$fieldName] ) )
					$transferData[$fieldName]	= $formData[$fieldName];
			}
		}
		if( !empty( $rules->map ) ){
			foreach( $rules->map as $sourceFieldName => $targetFileName ){
				$transferData[$targetFileName]	= NULL;
				if( !empty( $formData[$sourceFieldName] ) )
					$transferData[$targetFileName]	= $formData[$sourceFieldName];
			}
		}
		return $transferData;
	}

}
