<?php

use CeusMedia\Mail\Message;

class Logic_Import_Connector_Mailbox extends Logic_Import_Connector_MailAbstract implements Logic_Import_Connector_Interface
{
	/**
	 *	@param		array		$conditions
	 *	@param		array		$orders
	 *	@param		array		$limit
	 *	@return		array<string,Message>
	 */
	public function find( array $conditions, array $orders = [], array $limit = [] ): array
	{
		$list		= [];
		$mailIds    = $this->mailbox->index( $conditions );
		$mailIds	= array_slice( $mailIds, $limit[0], $limit[1] );
		foreach( $mailIds as $mailId ){
			$message	= $this->mailbox->getMailAsMessage( $mailId );
			$list[$mailId]	= $message;
/*			if( !$message->hasAttachments() )
				continue;
			foreach( $message->getAttachments() as $part ){
				$list[$mailId]	= $part->getContent();
				break;
			}*/
		}
		return $list;
	}
}
