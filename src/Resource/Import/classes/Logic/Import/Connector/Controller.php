<?php

class Logic_Import_Connector_Controller extends Logic_Import_Connector_MailAbstract implements Logic_Import_Connector_Interface
{
	/**
	 *	@param		array		$conditions
	 *	@param		array		$orders
	 *	@param		array		$limit
	 *	@return		array
	 *	@throws		JsonException
	 */
	public function find( array $conditions, array $orders = [], array $limit = [] ): array
	{
		$rawData	= $this->env->getRequest()->getRawPostData();
		return json_decode( $rawData, TRUE, 512, JSON_THROW_ON_ERROR );
	}
}