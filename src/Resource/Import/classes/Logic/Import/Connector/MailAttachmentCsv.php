<?php
use CeusMedia\Common\FS\File\CSV\Reader as CsvFileReader;
use CeusMedia\Mail\Message\Part\Attachment as MessagePartAttachment;

class Logic_Import_Connector_MailAttachmentCsv extends Logic_Import_Connector_MailAbstract implements Logic_Import_Connector_Interface
{
	/**
	 *	@param		array		$conditions
	 *	@param		array		$orders
	 *	@param		array		$limit
	 *	@return		array
	 *	@throws		ReflectionException
	 */
	public function find( array $conditions, array $orders = [], array $limit = [] ): array
	{
		$list		= [];
		$mailIds	= $this->mailbox->index( $conditions );
		$mailIds	= array_slice( $mailIds, $limit[0], $limit[1] );
		foreach( $mailIds as $mailId ){
			$message	= $this->mailbox->getMailAsMessage( $mailId );
			if( !$message->hasAttachments() )
				continue;
			$item	= $this->getEmptySourceItem( $mailId, 'mail', $conditions, $orders, $limit );
			foreach( $message->getAttachments() as $part ){
				$fileName	= $part->getFileName();
				$ext		= pathinfo( $fileName, PATHINFO_EXTENSION );
				if( strtolower( $ext ) !== 'csv' )
					continue;
				$item->data[$fileName]	= $this->readAttachmentFromMessagePart( $part );
			}
			if( count( $item->data ) ){
				$item	= $this->fixEncodingOnSourceItemWithDataFiles( $item );
				$list[]	= $item;
			}
		}
		return $list;
	}

	/**
	 * @param object $item
	 * @return object
	 */
	protected function fixEncodingOnSourceItemWithDataFiles( object $item ): object
	{
		$clone	= clone $item;
		if (isset($this->options->encoding) && $this->options->encoding !== 'UTF-8') {
			foreach ($clone->data as $fileName => $dataSet) {
				foreach ($dataSet as $dataSetId => $data) {
					$data2 = [];
					foreach ($data as $key => $value) {
						$key = iconv($this->options->encoding, 'UTF-8', $key);
						$value = iconv($this->options->encoding, 'UTF-8', $value);
						$data2[$key] = $value;
					}
					$clone->data[$fileName][$dataSetId] = $data2;
				}
			}
		}
		return $clone;
	}

	/**
	 *	@param		MessagePartAttachment		$part
	 *	@return		array|NULL
	 *	@throws		ReflectionException
	 */
	protected function readAttachmentFromMessagePart( MessagePartAttachment $part ): ?array
	{
		$tempFile	= tempnam( sys_get_temp_dir(), 'import' );
		file_put_contents( $tempFile, $part->getContent() );
		$useHeaders	= isset( $this->options->headers ) && $this->options->headers;

		$data	= NULL;
		try{
			$reader		= new CsvFileReader( $tempFile, $useHeaders, ';' );
			$data		= $reader->toArray();
		}
		catch( Exception $e ){
			$this->env->getLog()->logException( $e );
		}
		@unlink( $tempFile );
		return $data;
	}
}
