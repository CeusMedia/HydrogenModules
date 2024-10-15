<?php

declare(strict_types = 1);

interface Logic_Form_Transfer_Target_Interface
{
	/**
	 *	@param		int|string		$targetId
	 *	@param		object|array	$data
	 *	@return		Entity_Form_Fill_Transfer_Result
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function transfer( int|string $targetId, object|array $data ): Entity_Form_Fill_Transfer_Result;
}