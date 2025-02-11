<?php

declare(strict_types = 1);

interface Logic_Form_Transfer_Target_Interface
{
	/**
	 *	@param		int|string						$targetId
	 *	@param		Entity_Form_Transfer_Quest		$data
	 *	@return		Entity_Form_Transfer_Result
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function transfer( int|string $targetId, Entity_Form_Transfer_Quest $data ): Entity_Form_Transfer_Result;
}