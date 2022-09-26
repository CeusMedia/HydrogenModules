<?php
use CeusMedia\Common\FS\File\Reader as FileReader;
use CeusMedia\Common\FS\File\Writer as FileWriter;
use CeusMedia\Common\FS\File\RecursiveRegexFilter as RecursiveRegexFileIndex;
use CeusMedia\Common\FS\Folder\Editor as FolderEditor;

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

	protected $prefixPlaceholder	= '<%?prefix%>';

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

	public function dump()
	{
		$path		= $this->parameters->get( '--to', './' );

		$filename	= "dump_".date( "Y-m-d_H:i:s" )."_mails.sql";
		$pathname	= $path.$filename;

		$dbc		= $this->env->getDatabase();
		$dba		= $this->env->getConfig()->getAll( 'module.resource_database.access.', TRUE );
		$prefix		= $dba->get( 'prefix' );
		$tables		= $prefix.'mails';

		$command	= call_user_func_array( "sprintf", array(										//  call sprintf with arguments list
			"mysqldump -h%s -P%s -u%s -p%s %s %s > %s",												//  command to replace within
			escapeshellarg( $dba->get( 'host' ) ),													//  configured host name as escaped shell arg
			escapeshellarg( $dba->get( 'port' ) ? $dba->get( 'port' ) : 3306  ),					//  configured port as escaped shell arg
			escapeshellarg( $dba->get( 'username' ) ),												//  configured username as escaped shell arg
			escapeshellarg( $dba->get( 'password' ) ),												//  configured password as escaped shell arg
			escapeshellarg( $dba->get( 'name' ) ),													//  configured database name as escaped shell arg
			$tables,																				//  collected found tables
			escapeshellarg( $pathname ),															//  dump output filename
		) );
		$resultCode		= 0;
		$resultOutput	= [];
		exec( $command, $resultOutput, $resultCode );
		if( $resultCode !== 0 )
			throw new RuntimeException( 'Database dump failed' );

		/*  --  REPLACE PREFIX  --  */
		$regExp		= "@(EXISTS|FROM|INTO|TABLE|TABLES|for table)( `)(".$prefix.")(.+)(`)@U";		//  build regular expression
		$callback	= array( $this, '_callbackReplacePrefix' );										//  create replace callback
		rename( $pathname, $pathname."_" );															//  move dump file to source file
		$fpIn		= fopen( $pathname."_", "r" );													//  open source file
		$fpOut		= fopen( $pathname, "a" );														//  prepare empty target file
		while( !feof( $fpIn ) ){																	//  read input file until end
			$line	= fgets( $fpIn );																//  read line buffer
			$line	= preg_replace_callback( $regExp, $callback, $line );							//  perform replace in buffer
			fwrite( $fpOut, $line );																//  write buffer to target file
		}
		fclose( $fpOut );																			//  close target file
		fclose( $fpIn );																			//  close source file
		unlink( $pathname."_" );
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
		$fails		= [];
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
		$fails		= [];
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

	/**
	 *	Work in progress!
	 *	Store raw mails in shard folders.
	 */
	public function shard()
	{
		$path		= 'contents/mails/';
		$indexFile	= $path.'index.json';
		if( !file_exists( $path ) )
			FolderEditor::createFolder( $path );
		if( !file_exists( $indexFile ) )
			FileWriter::save( $indexFile, '[]' );
		$index		= json_decode( FileReader::load( $indexFile ), TRUE );

		$conditions	= array( 'status' => $this->statusesHandledMails );
		$orders		= array( 'mailId' => 'ASC' );
		$limits		= array(
			max( 0, (int) $this->parameters->get( '--offset', '0' ) ),
			max( 1, (int) $this->parameters->get( '--limit', '1000' ) ),
		);
		$count		= 0;
		$fails		= [];
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
					FolderEditor::createFolder( $path.$shard );
				FileWriter::save( $path.$shard.$uuid.'.raw', $mail->raw );
				FileWriter::save( $path.$shard.$uuid.'.raw.bz2', bzcompress( $mail->raw ) );
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
						FolderEditor::createFolder( $path.$shard );
					FileWriter::save( $path.$shard.$uuid.'.raw', $raw );
					FileWriter::save( $path.$shard.$uuid.'.raw.bz2', bzcompress( $raw ) );
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
		FileWriter::save( $indexFile, json_encode( $index ) );
		$this->out();
		$this->showErrors( 'shard', $fails );
	}

	//  --  PROTECTED  --  //

	protected function __onInit()
	{
		$this->model		= new Model_Mail( $this->env );
		$this->logicMail	= $this->env->getLogic()->get( 'Mail' );
		$this->libraries	= $this->logicMail->detectAvailableMailLibraries();
		$this->_loadMailClasses();
	}

	protected function _callbackReplacePrefix( array $matches ): string
	{
		if( $matches[1] === 'for table' )
			return $matches[1].$matches[2].$matches[4].$matches[5];
		return $matches[1].$matches[2].$this->prefixPlaceholder.$matches[4].$matches[5];
	}

	protected function _loadMailClasses()
	{
		$loadedClasses	= [];
		$mailClassPaths	= array( './', 'admin/' );
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
}
