<?php
use CeusMedia\Mail\Mailbox;
use CeusMedia\Mail\Mailbox\Mail;
use CeusMedia\Mail\Mailbox\Search;
use CeusMedia\Mail\Message;

class Logic_Import_Connector_MailAttachmentCsv extends Logic_Import_Connector_MailAbstract implements Logic_Import_Connector_Interface
{
	public function find( $conditions, $orders = array(), $limit = array() ): array
	{
		$list		= [];
		$mailIds    = $this->mailbox->index( $conditions );
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
				try{
					$tempFile	= tempnam( sys_get_temp_dir(), 'import' );
					file_put_contents( $tempFile, $part->getContent() );
					$useHeaders	= isset( $this->options->headers ) && $this->options->headers;
					$reader		= new FS_File_CSV_Reader( $tempFile, $useHeaders, ';' );
					$item->data[$fileName]	= $reader->toArray();

					if( isset( $this->options->encoding ) && $this->options->encoding !== 'UTF-8' ){
						foreach( $item->data as $fileName => $dataSet ){
							foreach( $dataSet as $dataSetId => $data ){
								$data2	= [];
								foreach( $data as $key => $value ){
									$key 	= iconv( $this->options->encoding, 'UTF-8', $key );
									$value	= iconv( $this->options->encoding, 'UTF-8', $value );
									$data2[$key]	= $value;
								}
								$item->data[$fileName][$dataSetId]	= $data2;
							}
						}
					}
				}
				catch( Exception $e ){
					$this->env->getLog()->logException( $e );
				}
				@unlink( $tempFile );
			}
			if( count( $item->data ) )
				$list[]	= $item;
		}
		return $list;
	}


	public function renameTo( $id, $newName ): bool
	{
		return FALSE;
	}

	public function moveTo( $id, $target ): bool
	{
		return $this->mailbox->moveMail( $id, $target, TRUE );
	}
}
