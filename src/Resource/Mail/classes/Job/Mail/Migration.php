<?php
/** @noinspection PhpUndefinedClassInspection */
/** @noinspection PhpUndefinedNamespaceInspection */
/** @noinspection PhpUndefinedClassInspection */

use CeusMedia\Common\Alg\Obj\Factory as ObjectFactory;
use CeusMedia\Common\FS\File\Reader as FileReader;
use CeusMedia\Common\FS\File\RecursiveRegexFilter as RecursiveRegexFileIndex;
use CeusMedia\Mail\Address as MailAddressV2;
use CeusMedia\Mail\Parser as MailMessageParserV1;
use CeusMedia\Mail\Participant as MailAddressV1;
use CeusMedia\Mail\Renderer as MailMessageRendererV1;
use CeusMedia\Mail\Message as MailMessageV2;
use CeusMedia\Mail\Message\Parser as MailMessageParserV2;
use CeusMedia\Mail\Message\Renderer as MailMessageRendererV2;

class Job_Mail_Migration extends Job_Abstract
{
	protected Logic_Mail $logicMail;

	protected Model_Mail $model;

	/** @var array<int> $statusesHandledMails */
	protected array $statusesHandledMails	= [
		Model_Mail::STATUS_ABORTED,																//  status: -3
		Model_Mail::STATUS_FAILED,																//  status: -2
		Model_Mail::STATUS_SENT,																//  status: 2
		Model_Mail::STATUS_RECEIVED,															//  status: 3
		Model_Mail::STATUS_OPENED,																//  status: 4
		Model_Mail::STATUS_REPLIED,																//  status: 5
	];

	/**
	 *	Applies several migration processes.
	 *	Supports dry mode.
	 *
	 *	Parameters:
	 *		--limit=NUMBER
	 *			- maximum number of mails to work on
	 *			- optional, default: 1000
	 *		--offset=NUMBER
	 *			- offset if using limit
	 *			- optional, default: 0
	 *
	 *	@access		public
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function migrate(): void
	{
		$conditions	= ['status' > $this->statusesHandledMails];
		$orders		= ['mailId' => 'ASC'];
		$limits		= [
			max( 0, (int) $this->parameters->get( '--offset', '0' ) ),
			max( 1, (int) $this->parameters->get( '--limit', '1000' ) ),
		];
		$count		= 0;
		$fails		= [];
		$mailIds	= $this->model->getAll( $conditions, $orders, $limits, ['mailId'] );
		foreach( $mailIds as $mailId ){
			/** @var Entity_Mail $mail */
			$mail		= $this->model->get( $mailId );
			$mailClone	= clone( $mail );
			try{
				$this->_migrateCompression( $mailClone );
				$this->logicMail->detectUsedMailCompression( $mailClone );
				$this->_detectMailClass( $mailClone );
				$this->_migrateMailClass( $mailClone );
				$this->_migrateSenderAddress( $mailClone );
				$this->_saveRaw( $mailClone );
				$changes	= $this->collectChangesMadeToMailObject( $mail, $mailClone );
				if( $changes ){
					$this->logChangesMadeToMailObject( $mail, $changes );
					if( !$this->dryMode ){
						$changeMap	= [];
						foreach( $changes as $key => $values )
							$changeMap[$key]	= $values->new;
						$this->model->edit( $mailId, $changeMap, FALSE );
					}
					$this->showProgress( ++$count, count( $mailIds ), '+' );
				}
				else{
					$this->showProgress( ++$count, count( $mailIds ), '-' );
				}
			}
			catch( Exception $e ){
				$fails[$mailId]	= $e->getMessage().PHP_EOL.$e->getTraceAsString();
				$this->showProgress( ++$count, count( $mailIds ), 'E' );
			}
		}
		$this->out();
		$this->showErrors( 'migrate', $fails );
		$this->_migrateMailTemplates();
	}

	/**
	 *	Recreates mail objects from raw mails.
	 *	Supports dry mode.
	 *
	 *	Parameters:
	 *		--limit=NUMBER
	 *			- maximum number of mails to work on
	 *			- optional, default: 1000
	 *		--offset=NUMBER
	 *			- offset if using limit
	 *			- optional, default: 0
	 *
	 *	@access		public
	 *	@return		array		List of errors, mail ID => error message + stack trace
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function regenerate(): array
	{
		$conditions	= ['status' > $this->statusesHandledMails];
		$orders		= ['mailId' => 'ASC'];
		$limits		= [
			max( 0, (int) $this->parameters->get( '--offset', '0' ) ),
			max( 1, (int) $this->parameters->get( '--limit', '1000' ) ),
		];
		$parser		= new MailMessageParserV2();
		$regex		= '/^O:[0-9]+:"([^"]+)":.+$/U';
		$logic		= $this->logicMail;
		$count		= 0;
		$fails		= [];
		$mailIds	= $this->model->getAll( $conditions, $orders, $limits, ['mailId'] );
		foreach( $mailIds as $mailId ){
			$mail			= $this->model->get( $mailId );
			$compression	= $mail->compression;
			try{
				$objectString	= $logic->decompressString( $mail->object, $compression );
				$messageRaw		= $logic->decompressString( $mail->raw, $compression );
				$mailClass		= preg_replace( $regex, '\\1', substr( $objectString, 0, 60 ) );
				$object			= ObjectFactory::createObject( $mailClass );
				$object->mail	= $parser->parse( $messageRaw );
				$mail->object	= $logic->compressString( serialize( $object ), $compression );
				if( !$this->dryMode )
					$this->model->edit( $mailId, ['object' => $mail->object], FALSE );
				$this->showProgress( ++$count, count( $mailIds ), '+' );
			}
			catch( Exception $e ){
				$fails[$mailId]	= $e->getMessage().PHP_EOL.$e->getTraceAsString();
				$this->showProgress( ++$count, count( $mailIds ), 'E' );
			}
		}
		return $fails;
	}

	//  --  PROTECTED  --  //

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->model		= new Model_Mail( $this->env );
		/** @noinspection PhpFieldAssignmentTypeMismatchInspection */
		$this->logicMail	= $this->env->getLogic()->get( 'Mail' );
		$this->_loadMailClasses();
	}

	protected function logMigration( Entity_Mail $mail, string $message ): void
	{
		$fileName	= 'job.resource_mail.archive.migration.log';
		$filePath	= $this->env->getConfig()->get( 'path.logs' ).$fileName;
		$message	= date( 'Y-m-d H:i:s' ).' #'.$mail->mailId.' '.$message;
		error_log( $message.PHP_EOL, 3, $filePath );
	}

	//  --  PRIVATE  --  //

	private function collectChangesMadeToMailObject( Entity_Mail $original, Entity_Mail $clone ): array
	{
		$changes	= [];
		foreach( $clone as $key => $value ){
			if( in_array( $key, $this->model->getColumns() ) ){
				$newColumn = !isset( $original->$key ) && $value;
				$changedColumn = trim( $original->$key ) != trim( $value );
//						print( "Col: ".$key.' '.($newColumn?'N':'').($changedColumn?'C':'').PHP_EOL );
				if( $newColumn || $changedColumn ){
					$changes[$key]	= (object) [
						'old'	=> $original->$key,
						'new'	=> $value,
					];
				}
			}
		}
		return $changes;
	}

	/**
	 *	@param		Entity_Mail		$mail		Database row object with populated mail object
	 *	@return		bool
	 */
	private function _detectMailClass( Entity_Mail $mail ): bool
	{
		$this->logicMail->decompressMailObject( $mail, FALSE );
		$serial			= $mail->objectSerial;
		$serialStart	= substr( $serial, 0, 80 );
		$mailClass		= preg_replace( '/^O:[0-9]+:"([^"]+)":.+$/U', '\\1', $serialStart );
		if( $mail->mailClass == $mailClass )
			return FALSE;
		$mail->mailClass = $mailClass;
		return TRUE;
	}

	/**
	 *	Loads PHP class files directly from mail class folder to allow deserialization.
	 *	@return        void
	 *	@throws		ReflectionException
	 */
	private function _loadMailClasses(): void
	{
		$loadedClasses	= [];
		$mailClassPaths	= ['./', 'admin/'];
		if( $this->env->getModules()->has( 'Resource_Frontend' ) )
			$mailClassPaths[]	= Logic_Frontend::getInstance( $this->env )->getPath();
		foreach( array_unique( $mailClassPaths ) as $mailClassPath ){
			if( !is_dir( $mailClassPath ) )
				continue;
			$path	= rtrim( trim( $mailClassPath ), '/' ).'/classes/Mail/';
			foreach( new RecursiveRegexFileIndex( $path, '/\.php5?/' ) as $entry ){
				$content	= FileReader::load( $entry->getPathname() );
				$className	= preg_replace( '/^.*class ([A-Z][A-Za-z0-9_]+).*$/s', '\\1', $content, 1 );
				if( $className && !in_array( $className, $loadedClasses ) ){
					include_once( $entry->getPathname() );
					$loadedClasses[]	= $className;
				}
			}
		}
	}

	private function logChangesMadeToMailObject( Entity_Mail $mail, array $changes ): void
	{
		$changeList	= [];
		foreach( $changes as $key => $values ){
			$changeList[]		= vsprintf( '%s: %s => %s', [
				$key,
				substr( $values->old, 0, 20 ),
				substr( $values->new, 0, 20 )
			] );
		}
		$this->logMigration( $mail, 'Changed: '.implode( ', ', $changeList ) );
	}

	/**
	 *	@param		object		$mail
	 *	@return		bool
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	private function _migrateCompression( object $mail ): bool
	{
		if( !isset( $mail->compression ) )
			return FALSE;
		if( 0 !== (int) $mail->compression )
			return FALSE;
//		$this->logicMail->decompressMailObject( $mail, FALSE, FALSE );
		$prefix = substr( $mail->object, 0, 2 );
		if( $prefix == "BZ" )
			$this->model->edit( $mail->mailId, ['compression' => Model_Mail::COMPRESSION_BZIP] );
		else if( !preg_match( '/^[a-z0-9]{20}/i', $mail->object ) )
			$this->model->edit( $mail->mailId, ['compression' => Model_Mail::COMPRESSION_GZIP] );
		return TRUE;
	}

	/**
	 *	@param		Entity_Mail		$mail		Database row object with populated mail object
	 *	@return		bool
	 */
	private function _migrateMailClass( Entity_Mail $mail ): bool
	{
		$classMigrations	= [
			'Mail_Auth_Password'		=> 'Mail_Auth_Local_Password',
			'Mail_Auth_Register'		=> 'Mail_Auth_Local_Register',
			'Mail_Shop_Order_Customer'	=> 'Mail_Shop_Customer_Ordered',
			'Mail_Shop_Order_Manager'	=> 'Mail_Shop_Manager_Ordered',
		];

		if( !array_key_exists( $mail->mailClass, $classMigrations ) )
			return FALSE;

		$this->logicMail->decompressMailObject( $mail, FALSE, FALSE );
		$newerClass	= $classMigrations[$mail->mailClass];
		$find		= 'O:'.strlen( $mail->mailClass ).':"'.$mail->mailClass.'":';
		$replace	= 'O:'.strlen( $newerClass ).':"'.$newerClass.'":';
		$serial		= str_replace( $find, $replace, $mail->objectSerial );
		$this->logicMail->compressMailObject( $mail );
		$mail->mailClass	= $newerClass;
		$this->logMigration( $mail, 'Migrated mail class names' );
		return TRUE;
	}

	/**
	 *	Detects mail sender from mail object to note to database.
	 *	@param		Entity_Mail		$mail		Mail object
	 *	@return		bool
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	private function _migrateSenderAddress( Entity_Mail $mail ): bool
	{
		if( 0 !== strlen( trim( $mail->senderAddress ?? '' ) ) )
			return FALSE;
		$this->logicMail->decompressMailObject( $mail );
		$mailInstance	= $mail->objectInstance->mail;
		if( !method_exists( $mailInstance, 'getSender' ) )
			return FALSE;
		$this->model->edit( $mail->mailId, [
			'senderAddress'	=> $mailInstance->getSender()->getAddress(),
		] );
		return TRUE;
	}

	/**
	 *	Converts old style and image lists to new JSON
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	private function _migrateMailTemplates(): void
	{
		$model		= new Model_Mail_Template( $this->env );
		foreach( $model->getAll() as $template ){
			if( strlen( trim( $template->styles ) ) ){
				if( !str_starts_with( $template->styles, '["' ) ){
					$list	= [$template->styles];
					if( strpos( $template->styles, ',' ) )
						$list	= explode( ',', $template->styles );
					$model->edit( $template->mailTemplateId, ['styles' => json_encode( $list )] );
				}
			}
			if( strlen( trim( $template->images ) ) ){
				if( !str_starts_with( $template->images, '["' ) ){
					$list	= [$template->images];
					if( strpos( $template->images, ',' ) )
						$list	= explode( ',', $template->images );
					$model->edit( $template->mailTemplateId, ['images' => json_encode( $list )] );
				}
			}
		}
	}

	/**
	 *	@param		Entity_Mail		$mail
	 *	@param		bool			$force
	 *	@return		void
	 */
	private function _saveRaw( Entity_Mail $mail, bool $force = FALSE ): void
	{
		if( !in_array( 'raw', $this->model->getColumns() ) )
			return;
		if( !empty( $mail->raw ) && !$force )
			return;
		$this->logicMail->decompressMailObject( $mail );
		$raw = MailMessageRendererV2::render( $mail->objectInstance->mail );
		$mail->raw	= $this->logicMail->compressString( $raw, $mail->compression );
		$this->logMigration( $mail, 'Saved raw using CeusMedia/Mail v2' );
	}
}
