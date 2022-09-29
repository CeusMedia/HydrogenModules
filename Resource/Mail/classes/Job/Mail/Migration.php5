<?php
use CeusMedia\Common\FS\File\Reader as FileReader;
use CeusMedia\Common\FS\File\RecursiveRegexFilter as RecursiveRegexFileIndex;

class Job_Mail_Migration extends Job_Abstract
{
	protected $model;

	protected $libraries;

	protected $statusesHandledMails	= array(
		Model_Mail::STATUS_ABORTED,																//  status: -3
		Model_Mail::STATUS_FAILED,																//  status: -2
		Model_Mail::STATUS_SENT,																//  status: 2
		Model_Mail::STATUS_RECEIVED,															//  status: 3
		Model_Mail::STATUS_OPENED,																//  status: 4
		Model_Mail::STATUS_REPLIED,																//  status: 5
	);

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
	 */
	public function migrate()
	{
		$conditions	= ['status' > $this->statusesHandledMails];
		$orders		= ['mailId' => 'ASC'];
		$limits		= array(
			max( 0, (int) $this->parameters->get( '--offset', '0' ) ),
			max( 1, (int) $this->parameters->get( '--limit', '1000' ) ),
		);
		$count		= 0;
		$fails		= [];
		$mailIds	= $this->model->getAll( $conditions, $orders, $limits, ['mailId'] );
		foreach( $mailIds as $mailId ){
			$mail		= $this->model->get( $mailId );
			$mailClone	= clone( $mail );
			try{
				$this->logicMail->detectUsedMailCompression( $mailClone );
				$this->_detectMailClass( $mailClone );
				$this->_migrateMailClass( $mailClone );
				$this->_migrateMailObject( $mailClone );
				$this->_saveRaw( $mailClone );
				if( is_object( $mailClone->object ) )
					$mailClone->object	= $mailClone->object->raw;
				if( is_object( $mailClone->raw ) )
					$mailClone->raw	= $mailClone->raw->raw;

				$changes	= [];
				foreach( $mailClone as $key => $value ){
					if( in_array( $key, $this->model->getColumns() ) ){
						$newColumn = !isset( $mail->$key ) && $value;
						$changedColumn = trim( $mail->$key ) != trim( $value );
//						print( "Col: ".$key.' '.($newColumn?'N':'').($changedColumn?'C':'').PHP_EOL );
						if( $newColumn || $changedColumn ){
							$changes[$key]	= (object) array(
								'old'	=> $mail->$key,
								'new'	=> $value,
							);
						}
					}
				}
				if( $changes ){
//					print_m($mailClone->raw);die;
					$changeList	= [];
					$changeMap	= [];
					foreach( $changes as $key => $values ){
						$changeMap[$key]	= $values->new;
						$changeList[]		= vsprintf( '%s: %s => %s', array(
							$key,
							substr( $values->old, 0, 20 ),
							substr( $values->new, 0, 20 )
						) );
					}
					$this->logMigration( $mail, 'Changed: '.implode( ', ', $changeList ) );
					if( !$this->dryMode )
						$this->model->edit( $mailId, $changeMap, FALSE );
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
	 *	@return		void
	 */
	public function regenerate()
	{
		$conditions	= ['status' > $this->statusesHandledMails];
		$orders		= ['mailId' => 'ASC'];
		$limits		= array(
			max( 0, (int) $this->parameters->get( '--offset', '0' ) ),
			max( 1, (int) $this->parameters->get( '--limit', '1000' ) ),
		);
		$parser		= new \CeusMedia\Mail\Message\Parser();
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
				$object			= Alg_Object_Factory::createObject( $mailClass, NULL, FALSE );
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
	}

	//  --  PROTECTED  --  //

	protected function __onInit()
	{
		$this->model		= new Model_Mail( $this->env );
		$this->logicMail	= $this->env->getLogic()->get( 'Mail' );
		$this->libraries	= $this->logicMail->detectAvailableMailLibraries();
		$this->_loadMailClasses();
	}

	protected function logMigration( $mail, $message )
	{
		$fileName	= 'job.resource_mail.archive.migration.log';
		$filePath	= $this->env->getConfig()->get( 'path.logs' ).$fileName;
		$message	= date( 'Y-m-d H:i:s' ).' #'.$mail->mailId.' '.$message;
		error_log( $message.PHP_EOL, 3, $filePath );
	}

	//  --  PRIVATE  --  //

	private function _detectMailClass( $mail )
	{
		$this->logicMail->decompressMailObject( $mail, FALSE );
		$serial			= $mail->object->serial;
		$serialStart	= substr( $serial, 0, 80 );
		$mailClass		= preg_replace( '/^O:[0-9]+:"([^"]+)":.+$/U', '\\1', $serialStart );
		if( $mail->mailClass == $mailClass )
			return FALSE;
		$mail->mailClass = $mailClass;
		return TRUE;
	}

	private function _loadMailClasses()
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

	private function _migrateMailClass( $mail )
	{
		$classMigrations	= array(
			'Mail_Auth_Password'		=> 'Mail_Auth_Local_Password',
			'Mail_Auth_Register'		=> 'Mail_Auth_Local_Register',
			'Mail_Shop_Order_Customer'	=> 'Mail_Shop_Customer_Ordered',
			'Mail_Shop_Order_Manager'	=> 'Mail_Shop_Manager_Ordered',
		);

		if( !array_key_exists( $mail->mailClass, $classMigrations ) )
			return FALSE;

		$this->logicMail->decompressMailObject( $mail, FALSE, FALSE );
		$newerClass	= $classMigrations[$mail->mailClass];
		$find		= 'O:'.strlen( $mail->mailClass ).':"'.$mail->mailClass.'":';
		$replace	= 'O:'.strlen( $newerClass ).':"'.$newerClass.'":';
		$serial		= str_replace( $find, $replace, $mail->object->serial );
		$this->logicMail->compressMailObject( $mail );
		$mail->mailClass	= $newerClass;
		$this->logMigration( $mail, 'Migrated mail class names' );
		return TRUE;
	}

	private function _migrateMailObject( $mail )
	{
		$this->logicMail->decompressMailObject( $mail );
		$usedLibrary	= $this->logicMail->detectMailLibraryFromMail( $mail );

		//  currently using CeusMedia/Mail version 2
		//  nothing to do here, placed for later
		if( $usedLibrary === Logic_Mail::LIBRARY_MAIL_V2 ){
			return FALSE;
		}

		//  currently using CeusMedia/Mail version 1
		else if( $usedLibrary === Logic_Mail::LIBRARY_MAIL_V1 ){
			if( $this->libraries & Logic_Mail::LIBRARY_MAIL_V2 ){
				if( in_array( 'raw', $this->model->getColumns() ) ){
					if( !empty( $mail->raw ) && is_string( $mail->raw ) && strlen( $mail->raw ) ){
						$raw	= $this->logicMail->decompressString( $mail->raw, $mail->compression );
						$parser	= new \CeusMedia\Mail\Message\Parser();
						$mail->object->instance->mail	= $parser->parse( $raw );
						$this->logicMail->compressMailObject( $mail, TRUE, TRUE );
						$this->logMigration( $mail, 'Migrated mail object from CeusMedia/Mail v1 to v2' );
						return TRUE;
					}
				}
			}
		}
		//  currently using Net_Mail from CeusMedia/Common
		else if( $usedLibrary === Logic_Mail::LIBRARY_COMMON ){
			if( $this->libraries & ( Logic_Mail::LIBRARY_MAIL_V1 | Logic_Mail::LIBRARY_MAIL_V2 ) ){	// @todo finish support for v2, see todo below
				$oldInstance	= $mail->object->instance;
				$newInstance	= new \CeusMedia\Mail\Message();
				$newInstance->setSubject( $oldInstance->mail->getSubject() );
				$sender	= $mail->senderAddress;
				if( $oldInstance->mail->getSender() )
					$sender	= $oldInstance->mail->getSender();
				$newInstance->setSender( $sender );

				if( $this->libraries & Logic_Mail::LIBRARY_MAIL_V1 ){
					$receiver	= new \CeusMedia\Mail\Participant();
 					$receiver->setAddress( $mail->receiverAddress );
					if( $mail->receiverName )
						$receiver->setName( $mail->receiverName );
					$newInstance->addRecipient( $receiver );
					$parts	= \CeusMedia\Mail\Parser::parseBody( $oldInstance->mail->getBody() );
					foreach( $parts as $part )
						$newInstance->addPart( $part );
					$this->logMigration( $mail, 'Migrated mail object from CeusMedia/Common::Net_Mail to CeusMedia/Mail v1' );
				}
				if( $this->libraries & Logic_Mail::LIBRARY_MAIL_V2 ){
					$receiver	= new \CeusMedia\Mail\Address();
 					$receiver->set( $mail->receiverAddress );
					if( $mail->receiverName )
						$receiver->setName( $mail->receiverName );
					$newInstance->addRecipient( $receiver );
// @todo find a way to get Net_Mail::parts and import in new CeusMedia\Mail\Message instance
//					$newInstance->...
					$this->logMigration( $mail, 'Migrated mail object from CeusMedia/Common::Net_Mail to CeusMedia/Mail v2' );
				}
				$mail->object->instance->mail	= $newInstance;
				$this->logicMail->compressMailObject( $mail, TRUE, TRUE );
				return TRUE;
			}
		}
		return FALSE;
	}

	private function _migrateMailTemplates()
	{
		$model		= new Model_Mail_Template( $this->env );
		foreach( $model->getAll() as $template ){
			if( strlen( trim( $template->styles ) ) ){
				if( substr( $template->styles, 0, 2 ) !== '["' ){
					$list	= [$template->styles];
					if( strpos( $template->styles, ',' ) )
						$list	= explode( ',', $template->styles );
					$model->edit( $template->mailTemplateId, ['styles' => json_encode( $list )] );
				}
			}
			if( strlen( trim( $template->images ) ) ){
				if( substr( $template->images, 0, 2 ) !== '["' ){
					$list	= [$template->images];
					if( strpos( $template->images, ',' ) )
						$list	= explode( ',', $template->images );
					$model->edit( $template->mailTemplateId, ['images' => json_encode( $list )] );
				}
			}
		}
	}

	private function _saveRaw( $mail, $force = FALSE )
	{

		if( !in_array( 'raw', $this->model->getColumns() ) )
			return;
		if( !empty( $mail->raw ) && !$force )
			return;
		$this->logicMail->decompressMailObject( $mail );
		$libraryObject	= $mail->object->instance->mail;
		if( $this->libraries & Logic_Mail::LIBRARY_MAIL_V2 ){
			$raw = \CeusMedia\Mail\Message\Renderer::render( $libraryObject );
			$mail->raw	= $this->logicMail->compressString( $raw, $mail->compression );
			$this->logMigration( $mail, 'Saved raw using CeusMedia/Mail v2' );
		}
		else if( $this->libraries & Logic_Mail::LIBRARY_MAIL_V1 ){
			$raw = \CeusMedia\Mail\Renderer::render( $libraryObject );
			$mail->raw	= $this->logicMail->compressString( $raw, $mail->compression );
			$this->logMigration( $mail, 'Saved raw using CeusMedia/Mail v1' );
		}
		else if( $this->libraries & Logic_Mail::LIBRARY_COMMON ){
			$rawLines	= [];
			foreach( $libraryObject->getHeaders()->getFields() as $header )
				$rawLines[]	= $header->toString();
			$rawLines[]	= '';
			$rawLines[]	= $libraryObject->getBody();
			$raw		= implode( Net_Mail::$delimiter, $rawLines );
			$mail->raw	= $this->logicMail->compressString( $raw, $mail->compression );
			$this->logMigration( $mail, 'Saved raw using CeusMedia/Common::Net_Mail' );
		}
	}
}
