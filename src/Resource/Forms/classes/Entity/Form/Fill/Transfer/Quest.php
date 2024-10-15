<?php
declare(strict_types=1);

use Entity_Form_Fill_Transfer_Result as Result;

/**
 * Data exchange object for storing transfer requests towards transfer target implementation.
 * This entity will not be stored in database directly, but interpreted by transfer invocation.
 */
class Entity_Form_Fill_Transfer_Quest
{
/*	const STATUS_TRANSFERRED	= 3;
	const STATUS_APPLIED		= 2;
	const STATUS_PARSED			= 1;
	const STATUS_NONE			= 0;
	const STATUS_ERROR			= -1;
	const STATUS_EXCEPTION		= -2;*/

	public string $status		= 'none';
	public object $rule;
	public object $target;
	public array $formData;
	public ?array $data			= NULL;
	public ?string $error		= NULL;
	public ?Result $result		= NULL;

	/**
	 *	@param		object		$target
	 *	@param		object		$rule
	 *	@param		array		$formData
	 */
	public function __construct( object $target, object $rule, array $formData = [] )
	{
		$this->target		= $target;
		$this->rule			= $rule;
		$this->formData		= $formData;
	}
}