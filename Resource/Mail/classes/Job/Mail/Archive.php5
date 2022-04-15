<?php
class Job_Mail_Archive extends Job_Abstract
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

	public function __onInit()
	{
		$this->model		= new Model_Mail( $this->env );
		$this->logicMail	= $this->env->getLogic()->get( 'Mail' );
		$this->libraries	= $this->logicMail->detectAvailableMailLibraries();
		$this->_loadMailClasses();
	}

	/**
	 *	Work in progress!
	 *	Store raw mails in shard folders.
	 */
	public function shard()
	{
		$path		= 'contents/mails/';
		$indexFile	= $path.'index.json';
		if( !file_exists( $path ) )
			FS_Folder_Editor::createFolder( $path );
		if( !file_exists( $indexFile ) )
			FS_File_Writer::save( $indexFile, '[]' );
		$index		= json_decode( FS_File_Reader::load( $indexFile ), TRUE );

		$conditions	= array( 'status' => $this->statusesHandledMails );
		$orders		= array( 'mailId' => 'ASC' );
		$limits		= array(
			max( 0, (int) $this->parameters->get( '--offset', '0' ) ),
			max( 1, (int) $this->parameters->get( '--limit', '1000' ) ),
		);
		$count		= 0;
		$fails		= array();
		$mailIds	= $this->model->getAll( $conditions, $orders, $limits, array( 'mailId' ) );
		foreach( $mailIds as $mailId ){
			$count++;
			$mailId		= (string) $mailId;
			if( array_key_exists( $mailId, $index ) ){
				$fails[]	= 'Mail #'.$mailId.': Skipped.';
				$this->showProgress( $count, count( $mailIds ), '.' );
				continue;
			}
			$mail	= $this->model->get( $mailId );
			$this->logicMail->decompressMailObject( $mail );
			$usedLibrary	= $this->logicMail->detectMailLibraryFromMail( $mail );
			if( !( $this->libraries & $usedLibrary ) ){
				$fails[]	= 'Mail #'.$mailId.': Mail library mismatch: '.$usedLibrary;
				$this->showProgress( $count, count( $mailIds ), 'E' );
				continue;
			}
			$uuid		= Alg_ID::uuid();
			$shard		= $uuid[0].'/'.$uuid[1].'/'.$uuid[2].'/';
			if( !empty( $mail->raw ) ){
				if( !file_exists( $path.$shard ) )
					FS_Folder_Editor::createFolder( $path.$shard );
				FS_File_Writer::save( $path.$shard.$uuid.'.raw', $mail->raw );
				FS_File_Writer::save( $path.$shard.$uuid.'.raw.bz2', bzcompress( $mail->raw ) );
				$index[$mailId]	= array( 'uuid' => $uuid, 'shard' => $shard, 'format' => 'bzip' );
				$this->showProgress( $count, count( $mailIds ), '+' );
			}
			else{
				$object	= $mail->object->instance;
				$class	= get_class( $object->mail );
				if( $class !== 'CeusMedia\\Mail\\Message' ){
					$fails[]	= 'Mail #'.$mailId.': Unsupported mail class: '.$class;
					$this->showProgress( $count, count( $mailIds ), 'E' );
					continue;
				}
				try{
					if( !count( $object->mail->getParts( FALSE ) ) ){
						if( ( $page = $object->getPage() ) )
						$object->mail->addHtml( $page->build() );
					}
					if( $this->libraries & Logic_Mail::LIBRARY_MAIL_V1 ){
						$raw		= CeusMedia\Mail\Renderer::render( $object->mail );
					}
					else if( $this->libraries & Logic_Mail::LIBRARY_MAIL_V2 ){
						$raw		= CeusMedia\Mail\Message\Renderer::render( $object->mail );
					}
					if( !file_exists( $path.$shard ) )
						FS_Folder_Editor::createFolder( $path.$shard );
					FS_File_Writer::save( $path.$shard.$uuid.'.raw', $raw );
					FS_File_Writer::save( $path.$shard.$uuid.'.raw.bz2', bzcompress( $raw ) );
					$index[$mailId]	= array( 'uuid' => $uuid, 'shard' => $shard, 'format' => 'bzip' );
					$this->showProgress( $count, count( $mailIds ), '+' );
				}
				catch( Exception $e ){
					$fails[]	= 'Mail #'.$mailId.': Exception: '.$e->getMessage();
					$this->showProgress( $count, count( $mailIds ), 'E' );
					continue;
				}
			}
		}
		FS_File_Writer::save( $indexFile, json_encode( $index ) );
		$this->out();
		$this->showErrors( 'shard', $fails );
	}

	/**
	 *	Removes old mails from database table.
	 *	Mails to be removed can be filtered by minimum age and mail class(es).
	 *	Supports dry mode.
	 *
	 *	Parameters:
	 *		--age=PERIOD
	 *			- minimum age of mail to delete
	 *			- DateInterval period without starting P and without any time elements
	 *			- see: https://www.php.net/manual/en/dateinterval.construct.php
	 *			- example: 1Y (1 year), 2M (2 months), 3D (3 days)
	 *			- optional, default: 1Y
	 *		--class=CLASSNAME[,CLASSNAME]
	 *			- name of mail class to focus on
	 *			- without prefix 'Mail_'
	 *			- can be several, separated by comma
	 *			- example: Newsletter (for class Mail_Newsletter)
	 *			- example: Newsletter,Form_Manager_Filled
	 *			- default: empty, meaning all mail classes
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
	public function clean()
	{
		$age		= $this->parameters->get( '--age', '1Y' );
		$age		= $age ? strtoupper( $age ) : '1Y';
		$limits		= array(
			max( 0, (int) $this->parameters->get( '--offset', '0' ) ),
			max( 1, (int) $this->parameters->get( '--limit', '1000' ) ),
		);
		$threshold	= date_create()->sub( new DateInterval( 'P'.$age ) );

		$class		= $this->parameters->get( '--class', NULL );
		if( $class !== NULL ){
			$class	= preg_split( '/\s*,\s*/', $class );
			foreach( $class as $nr => $mailClassName )
				if( !preg_match( '/\\\/', $mailClassName ) )
					$class[$nr]	= 'Mail_'.$mailClassName;
		}
		$conditions	= array(
			'status'		=> $this->statusesHandledMails,
			'mailClass'		=> $class,
			'enqueuedAt' 	=> '< '.$threshold->format( 'U' ),
		);
		$orders		= array( 'mailId' => 'ASC' );
		$mailIds	= $this->model->getAll( $conditions, $orders, $limits, array( 'mailId' ) );
		$nrMails	= count( $mailIds );
		if( $this->dryMode ){
			$this->out( 'DRY RUN - no changes will be made.' );
			$this->out( 'Would remove '.$nrMails.' old mails.' );
			return;
		}
		$database	= $this->env->getDatabase();
		$database->beginTransaction();
		foreach( $mailIds as $nr => $mailId ){
			$this->model->remove( $mailId );
			$this->showProgress( $nr + 1, $nrMails );
		}
		$database->commit();
		$this->out( 'Removed '.$nrMails.' old mails.' );
	}

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
		$conditions	= array( 'status' > $this->statusesHandledMails );
		$orders		= array( 'mailId' => 'ASC' );
		$limits		= array(
			max( 0, (int) $this->parameters->get( '--offset', '0' ) ),
			max( 1, (int) $this->parameters->get( '--limit', '1000' ) ),
		);
		$count		= 0;
		$fails		= array();
		$mailIds	= $this->model->getAll( $conditions, $orders, $limits, array( 'mailId' ) );
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

				$changes	= array();
				foreach( $mailClone as $key => $value )
					if( in_array( $key, $this->model->getColumns() ) )
						if( ( !isset( $mail->$key ) && $value ) || trim( $mail->$key ) != trim( $value ) )
							$changes[$key]	= (object) array(
								'old'	=> $mail->$key,
								'new'	=> $value,
							);
				if( $changes ){
					$changeList	= array();
					$changeMap	= array();
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
					$this->showProgress( ++$count, count( $mailIds ) );
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
	 *	Re-generates mail object from raw mail.
	 *	This can be necessary after updating the used mail library.
	 *	Apply this after upgrading CeusMedia\Mail to v2.5 or higher.
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
		$conditions	= array( 'status' > $this->statusesHandledMails );
		$orders		= array( 'mailId' => 'ASC' );
		$limits		= array(
			max( 0, (int) $this->parameters->get( '--offset', '0' ) ),
			max( 1, (int) $this->parameters->get( '--limit', '1000' ) ),
		);
		$count		= 0;
		$fails		= array();
		$mailIds	= $this->model->getAll( $conditions, $orders, $limits, array( 'mailId' ) );
		foreach( $mailIds as $mailId ){
			$mail		= $this->model->get( $mailId );
			$mailClone	= clone( $mail );
			try{
				$compression	= (int) $mail->compression;
				$objectSerial	= $this->logicMail->decompressString( $mail->object, $compression );
				$raw			= $this->logicMail->decompressString( $mail->raw, $compression );
				$object			= unserialize( $objectSerial );
				$object->mail	= CeusMedia\Mail\Message\Parser::getInstance()->parse( $raw );
				$newObject		= $this->logicMail->compressString( serialize( $object ), $compression );
				$this->model->edit( $mailId, ['object' => $newObject], FALSE );
				$this->showProgress( ++$count, count( $mailIds ) );
			}
			catch( Exception $e ){
				$fails[$mailId]	= $e->getMessage().PHP_EOL.$e->getTraceAsString();
				$this->showProgress( ++$count, count( $mailIds ), 'E' );
			}
		}
		$this->out();
		$this->showErrors( 'regenerate', $fails );
	}

	/**
	 *	Remove attachments from mails in database table.
	 *	Mails to be removed can be filtered by minimum age and mail class(es).
	 *	Supports dry mode.
	 *	Supports CeusMedia/Mail v1 and v2, but not CeusMedia/Common:Net_Nail.
	 *
	 *	Parameters:
	 *		--age=PERIOD
	 *			- minimum age of mail to delete
	 *			- DateInterval period without starting P and without any time elements
	 *			- see: https://www.php.net/manual/en/dateinterval.construct.php
	 *			- example: 1Y (1 year), 2M (2 months), 3D (3 days)
	 *			- optional, default: 1Y
	 *		--class=CLASSNAME[,CLASSNAME]
	 *			- name of mail class to focus on
	 *			- without prefix 'Mail_'
	 *			- can be several, separated by comma
	 *			- example: Newsletter (for class Mail_Newsletter)
	 *			- example: Newsletter,Form_Manager_Filled
	 *			- default: empty, meaning all mail classes
	 *
	 *	@todo	test
	 */
	public function removeAttachments()
	{
		$age		= $this->parameters->get( '--age', '1Y' );
		$age		= $age ? strtoupper( $age ) : '1Y';
		$threshold	= date_create()->sub( new DateInterval( 'P'.$age ) );

		$class		= $this->parameters->get( '--class', NULL );
		if( $class !== NULL ){
			$class	= preg_split( '/\s*,\s*/', $class );
			foreach( $class as $nr => $mailClassName )
				if( !preg_match( '/\\\/', $mailClassName ) )
					$class[$nr]	= 'Mail_'.$mailClassName;
		}
		$conditions	= array(
			'status'		=> $this->statusesHandledMails,
			'mailClass'		=> $class,
			'enqueuedAt' 	=> '< '.$threshold->format( 'U' ),
		);

		$orders		= array( 'mailId' => 'DESC' );
		$fails		= array();
		$results	= (object) array(
			'mails'			=> 0,
			'attachments'	=> 0,
			'sizeBefore'	=> 0,
			'sizeAfter'		=> 0,
		);

		if( $this->dryMode )
			$this->out( 'DRY RUN - no changes will be made.' );

		$mailIds	= $this->model->getAllByIndices( $conditions, $orders, array(), array( 'mailId' ) );
		foreach( $mailIds as $mailId ){
			try{
				$mail			= $this->model->get( $mailId );
				$this->logicMail->decompressMailObject( $mail );
				$sizeBefore		= strlen( $mail->object->raw );
				if( $this->libraries & Logic_Mail::LIBRARY_MAIL_V2 ){
					$parts		= $mail->object->instance->mail->getParts();
					foreach( $parts as $nr => $part ){
//						$this->out( "Part: ".get_class( $part ) );
						if( $part->isAttachment() ){
							$mail->object->instance->mail->removePart( $nr );
							$this->logicMail->compressMailObject( $mail );
							$renderer	= new \CeusMedia\Mail\Message\Renderer();
							$raw		= $renderer->render( $mail->object->instance->mail );
							if( !$this->dryMode ){
								$this->model->edit( $mail->mailId, array(
									'object'	=> $mail->object->raw,
									'raw'		=> $this->logicMail->compressString( $raw ),
								), FALSE );
							}
							$results->attachments++;
							$results->sizeBefore	+= $sizeBefore;
							$results->sizeAfter		+= strlen( $mail->object->raw );
						}
					}
				}
				else if( $this->libraries & Logic_Mail::LIBRARY_MAIL_V1 ){
					$parts		= $mail->object->instance->mail->getParts();
					foreach( $parts as $nr => $part ){
//						$this->out( "Part: ".get_class( $part ) );
						if( $part instanceof \CeusMedia\Mail\Part\Attachment ){
							$mail->object->instance->mail->removePart( $nr );
							$this->logicMail->compressMailObject( $mail );
							$raw	= \CeusMedia\Mail\Renderer::render( $mail->object->instance->mail );
							if( !$this->dryMode ){
								$this->model->edit( $mail->mailId, array(
									'object'	=> $mail->object->raw,
									'raw'		=> $this->logicMail->compressString( $raw ),
								), FALSE );
							}
							$results->attachments++;
							$results->sizeBefore	+= $sizeBefore;
							$results->sizeAfter		+= strlen( $mail->object->raw );
						}
					}
				}
				$this->showProgress( ++$results->mails, count( $mailIds ) );
			}
			catch( Exception $e ){
				$this->showProgress( ++$results->mails, count( $mailIds ), 'E' );
				$fails[$mail->mailId]	= $e->getMessage();
			}
		}
		if( $mailIds )
			$this->out();
		if( $results->attachments ){
			$message	= 'Detached %s attachments, deflated mails from %s to %s.';
			$this->out( vsprintf( $message, array(
				$results->attachments,
				Alg_UnitFormater::formatBytes( $results->sizeBefore ),
				Alg_UnitFormater::formatBytes( $results->sizeAfter )
			) ) );
		}
		else
			$this->out( 'No detachable attachments found.' );
		$this->showErrors( 'removeAttachments', $fails );
	}

	//  --  PROTECTED  --  //

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
		$loadedClasses	= array();
		$mailClassPaths	= array( './', 'admin/' );
		if( $this->env->getModules()->has( 'Resource_Frontend' ) )
			$mailClassPaths[]	= Logic_Frontend::getInstance( $this->env )->getPath();
		foreach( array_unique( $mailClassPaths ) as $mailClassPath ){
			if( !is_dir( $mailClassPath ) )
				continue;
			$path	= rtrim( trim( $mailClassPath ), '/' ).'/classes/Mail/';
			foreach( new FS_File_RecursiveRegexFilter( $path, '/\.php5?/' ) as $entry ){
				$content	= FS_File_Reader::load( $entry->getPathname() );
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
						$raw	= $this->logicMail->decompressString( $mail->raw, (int) $mail->compression );
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
					$list	= array( $template->styles );
					if( strpos( $template->styles, ',' ) )
						$list	= explode( ',', $template->styles );
					$model->edit( $template->mailTemplateId, array( 'styles' => json_encode( $list ) ) );
				}
			}
			if( strlen( trim( $template->images ) ) ){
				if( substr( $template->images, 0, 2 ) !== '["' ){
					$list	= array( $template->images );
					if( strpos( $template->images, ',' ) )
						$list	= explode( ',', $template->images );
					$model->edit( $template->mailTemplateId, array( 'images' => json_encode( $list ) ) );
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
			$rawLines	= array();
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
