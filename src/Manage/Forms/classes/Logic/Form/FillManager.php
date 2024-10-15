<?php

class Logic_Form_FillManager extends Logic_Form_Fill
{
	/**
	 *	Returns one fill or many fills by form ID as CSV.
	 *	@access		public
	 *	@param		string		$type		Type of ID (form|fill)
	 *	@param		array		$ids		ID list of fill or form of fills
	 *	@param		int|NULL	$status		...
	 *	@return		string
	 *	@throws		DomainException			if type is not (form|fill)
	 *	@throws		JsonException			if decoding JSON of fill data failed
	 */
	public function renderToCsv( string $type, array $ids, int $status = NULL ): string
	{
		$types	= ['fill', 'form'];
		if( !in_array( $type, $types, TRUE ) )
			throw new DomainException( 'Invalid type given' );

		$data	= [];
		$keys	= ['dateCreated', 'dateConfirmed', 'formId'];
		$indices	= [$type.'Id' => $ids];
		if( !is_null( $status ) )
			$indices['status']	= $status;
		$fills	= $this->modelFill->getAllByIndices( $indices );
		foreach( $fills as $fill ){
			$fill->data	= json_decode( $fill->data, FALSE, 512, JSON_THROW_ON_ERROR );
			$row		= [
				'dateCreated'	=> date( 'Y-m-d H:i:s', $fill->createdAt ),
				'dateConfirmed'	=> $fill->modifiedAt ? date( 'Y-m-d H:i:s', $fill->modifiedAt ) : '',
				'formId'		=> $fill->formId,
			];
			foreach( $fill->data as $item ){
				$row[$item->name]	= $item->value;
				if( !empty( $item->valueLabel ) )
					$row[$item->name]	= $item->valueLabel;
				if( !in_array( $item->name, $keys ) )
					$keys[]	= $item->name;
			}
			$data[]	= $row;
		}
		$lines	= [join( ';', $keys )];
		foreach( $data as $line ){
			$row	= [];
			foreach( $keys as $key ){
				$value	= $line[$key] ?? '';
				$row[]	= '"'.addslashes( $value ).'"';
			}
			$lines[]	= join( ';', $row );
		}
		return join( "\r\n", $lines );
	}
}
