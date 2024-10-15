<?php

declare(strict_types=1);

/**
 * Data exchange object for storing transfer results provided by transfer target implementation.
 * This entity will not be stored in database directly, but interpreted by transfer invocation.
 */
class Entity_Form_Fill_Transfer_Result
{
	public int $status		= Model_Form_Fill_Transfer::STATUS_UNKNOWN;

	public array $errors	= [];

	public ?string $trace	= NULL;

	/**
	 *	@param		int|NULL		$status
	 *	@param		array			$errors
	 *	@param		string|NULL		$trace
	 */
	public function __construct( int $status = NULL, array $errors = [], string $trace = NULL )
	{
		if( NULL !== $status ){
			if( !in_array( $status, Model_Form_Fill_Transfer::STATUSES ) )
				throw new RangeException( 'Invalid status: '.$status );
			$this->status		= $status;
		}
		if( [] !== $errors )
			$this->errors		= $errors;
		if( NULL !== $trace )
			$this->trace		= $trace;
	}
}