<?php

/**
 *	@author		Christian Würker <christian.wuerker@ceusmedia.de>
 */

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Alg\Obj\Factory as ObjectFactory;
use CeusMedia\Common\Exception\Conversion as ConversionException;
use CeusMedia\Common\Exception\MissingExtension as MissingExtensionException;
use CeusMedia\Common\Exception\NotSupported as NotSupportedException;
use CeusMedia\Common\FS\File\Reader as FileReader;
use CeusMedia\Common\FS\File\RecursiveRegexFilter as RecursiveRegexFileIndex;
use CeusMedia\HydrogenFramework\Logic;
use CeusMedia\Mail\Message as MailMessage;
use Psr\SimpleCache\InvalidArgumentException as SimpleCacheInvalidArgumentException;

/**
 *	@author		Christian Würker <christian.wuerker@ceusmedia.de>
 *	@todo		code doc
 */
class Logic_Mail extends Logic
{
	public const LIBRARY_UNKNOWN		= 0;
	public const LIBRARY_COMMON			= 1;
	public const LIBRARY_MAIL_V1		= 2;
	public const LIBRARY_MAIL_V2		= 4;

	protected Logic_Mail_Queue $queue;
	protected Logic_Mail_Attacher $attacher;
	protected Model_Mail_Template $modelTemplate;
	protected Dictionary $options;

	/** @var array<int,Entity_Mail_Template> $detectedTemplates */
	protected array $detectedTemplates	= [];

	/**
	 *	@return		int
	 */
	public function abortMailsWithTooManyAttempts(): int
	{
		return $this->queue->abortMailsWithTooManyAttempts();
	}

	public function appendRegisteredAttachments( Mail_Abstract $mail, string $language ): int
	{
		return $this->attacher->appendRegisteredAttachments( $mail, $language );
	}

	/**
	 *	@param		array|string	$userIds
	 *	@param		array|string	$roleIds
	 *	@return		array
	 *	@throws		ReflectionException
	 *	@deprecated
	 *	@todo		find usage and move to module or remove, reason: uncool hard binding to user/role models
	 */
	public function collectConfiguredReceivers( array|string $userIds, array|string $roleIds = [] ): array
	{
		if( !$this->env->getModules()->has( 'Resource_Users' ) )
			return [];
		$receivers		= [];
		if( is_string( $userIds ) )
			$userIds	= explode( ",", trim( $userIds ) );
		if( is_string( $roleIds ) )
			$roleIds	= explode( ",", trim( $roleIds ) );
		if( !is_array( $userIds ) )
			throw new InvalidArgumentException( 'Invalid list of user IDs' );
		if( !is_array( $roleIds ) )
			throw new InvalidArgumentException( 'Invalid list of role IDs' );
		$modelUser		= new Model_User( $this->env );
		foreach( $roleIds as $roleId ){
			if( strlen( trim( $roleId ) ) && (int) $roleId > 0 ){
				$users	= $modelUser->getAllByIndex( 'roleId', $roleId );
				foreach( $users as $user )
					$receivers[(int) $user->userId]	= $user;
			}
		}
		foreach( $userIds as $userId ){
			if( strlen( trim( $userId ) ) && (int) $userId > 0 ){
				if( !isset( $receivers[(int) $userId] ) ){
					$user	= $modelUser->get( (int) $userId );
					$receivers[(int) $userId]	= $user;
				}
			}
		}
		return $receivers;
	}

	/**
	 *	Returns number of mails in queue by given conditions.
	 *	@access		public
	 *	@param		array		$conditions		Map of column conditions to look for
	 *	@return		integer						Number of mails in queue matching conditions
	 */
	public function countQueue( array $conditions = [] ): int
	{
		return $this->queue->countQueue( $conditions );
	}

	/**
	 *	Creates instance of mail class with given mail data.
	 *	Return mail object contains available mail parts.
	 *	An active mail template will be applied.
	 *	@access		public
	 *	@param		string		$mailClassName		Name of mail class without Mail_ prefix
	 *	@param		array		$mailData			Data map for mail content generation, nested arrays and objects are possible
	 *	@return		Mail_Abstract					Instance of mail class containing rendered mail parts
	 *	@throws		RuntimeException				If mail class is not existing
	 *	@throws		ReflectionException
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function createMail( string $mailClassName, array $mailData ): Mail_Abstract
	{
		$className	= 'Mail_'.$mailClassName;
		if( !class_exists( $className ) )
			throw new RuntimeException( 'Mail class "'.$className.'" is not existing' );

		$env	= $this->env;
		if( $this->env->getModules()->has( 'Resource_Frontend' ) )
			if( './' !== $this->env->getConfig()->get( 'module.resource_frontend.path' ) )
				$env	= Logic_Frontend::getInstance( $this->env )->getRemoteEnv( $this->env );

		/** @var Mail_Abstract $mail */
		$mail	= ObjectFactory::createObject( $className, [$env, $mailData] );
		return $mail;
	}

	/**
	 *	Detects the best template to use by looking for:
	 *	- given template ID, realizing mail settings of mail class of a module
	 *	- active mail template ID within database
	 *	- default mail template ID of mail resource module of frontend application (if considered)
	 *	- default mail template ID of mail resource module
	 *	The first of these templates being usable will be stored and returned.
	 *	@access		public
	 *	@param		integer			$preferredTemplateId	Template ID to override database and module defaults, if usable
	 *	@param		boolean			$considerFrontend		Flag: consider mail resource module of frontend, if available
	 *	@param		boolean			$strict					Flag: throw exception if something goes wrong
	 *	@return		Entity_Mail_Template|NULL				Model entity object of detected mail template
	 *	@todo		see code doc
	 */
	public function detectTemplateToUse( int $preferredTemplateId = 0, bool $considerFrontend = FALSE, bool $strict = TRUE ): ?Entity_Mail_Template
	{
		if( array_key_exists( $preferredTemplateId, $this->detectedTemplates ) )
			return $this->detectedTemplates[$preferredTemplateId];

		$defaultFromMailModule	= $this->options->get( 'template' );
		$defaultFromDatabase	= $this->modelTemplate->getByIndex( 'status', Model_Mail_Template::STATUS_ACTIVE, [], ['mailTemplateId'] );
		$defaultFromFrontend	= 0;
		if( $considerFrontend && $this->env->getModules()->has( 'Resource_Frontend' ) ){
			try{
				$frontend				= Logic_Frontend::getInstance( $this->env );
				$defaultFromFrontend	= $frontend->getModuleConfigValue( 'Resource_Mail', 'template' );
			}
			catch( Exception ){}
		}

		//  collect template defaults and overrides
		$templateIds	= array_unique( [
			$defaultFromMailModule ?: 0,
			$defaultFromDatabase ?: 0,
			$defaultFromFrontend ?: 0,
			$preferredTemplateId ?: 0,
		] );

		//  get usable templates from database
		$availableTemplateIds	= $this->modelTemplate->getAll( [
			'mailTemplateId'	=> $templateIds,
			'status'			=> '>= '.Model_Mail_Template::STATUS_USABLE,
		], [], [], ['mailTemplateId'] );
		if( !$availableTemplateIds ){
			if( $strict )
				throw new RuntimeException( 'No usable mail template available' );
			return NULL;
		}

		//  match collected and usable templates
		$templateIds	= array_intersect( $templateIds, $availableTemplateIds );
		if( !$templateIds ){
			if( $strict )
				throw new RuntimeException( 'No usable mail template found' );
			return NULL;
		}

		//  get the best template and store for later
		$detectedTemplateId	= array_pop( $templateIds );
		/** @var Entity_Mail_Template $template */
		$template			= $this->modelTemplate->get( $detectedTemplateId );
		$this->detectedTemplates[$detectedTemplateId]	= $template;
		return $template;
	}

	/**
	 *	@param		Mail_Abstract $mail
	 *	@param		Entity_User $receiver
	 *	@param		int|string|NULL $senderId
	 *	@return		int|string
	 *	@throws		ReflectionException
	 */
	public function enqueueMailToUser( Mail_Abstract $mail, Entity_User $receiver, int|string|NULL $senderId = NULL ): int|string
	{
		return $this->enqueueMail( $mail, $receiver->language, $receiver, $senderId );
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		integer|string|object	$receiver		User ID or data object of receiver (must have member 'email', should have 'userId' and 'username')
	 *	@throws		InvalidArgumentException				If receiver is neither an object nor an ID
	 *	@throws		InvalidArgumentException				If receiver object is missing "email"
	 *	@throws		ReflectionException
	 */
	public function checkReceiver( int|string|object $receiver ): object
	{
		if( is_integer( $receiver ) || is_string( $receiver ) ){
			$model		= new Model_User( $this->env );
			$receiver	= $model->get( $receiver );
		}
		if( !is_object( $receiver ) )
			throw new InvalidArgumentException( 'Receiver is neither an object nor an ID' );

		if( empty( $receiver->email ) )
			throw new InvalidArgumentException( 'Receiver object is missing "email"' );

		return $receiver;
	}

	/**
	 *	Send prepared mail later.
	 *	@access		public
	 *	@param		Mail_Abstract			$mail			Mail instance to be queued
	 *	@param		string					$language		Language key
	 *	@param		integer|string|object	$receiver		User ID or data object of receiver (must have member 'email', should have 'userId' and 'username')
	 *	@param		string|NULL				$senderId		Optional: ID of sending user
	 *	@return		int|string								ID of queued mail
	 *	@throws		InvalidArgumentException				If receiver is neither an object nor an ID
	 *	@throws		InvalidArgumentException				If receiver object is missing "email"
	 *	@throws		ReflectionException
	 */
	public function enqueueMail( Mail_Abstract $mail, string $language, int|string|object $receiver, ?string $senderId = NULL ): int|string
	{
		return $this->queue->enqueueMail( $mail, $language, $receiver, $senderId );
	}

	/**
	 *	Returns saved mail as uncompressed object by mail ID.
	 *	@access		public
	 *	@param		int|string		$mailId			ID of queued mail
	 *	@return		Entity_Mail						Mail object from queue
	 *	@throws		OutOfRangeException				if mail ID is not existing
	 *	@throws		RuntimeException				if mail is compressed by BZIP which is not supported in this environment
	 *	@throws		RuntimeException				if mail is compressed by GZIP which is not supported in this environment
	 *	@throws		RuntimeException				if deserialize mail serial fails
	 */
	public function getMail( int|string $mailId ): Entity_Mail
	{
		return $this->queue->getMail( $mailId );
	}

	/**
	 * @throws
	 *	@todo		 (performance) remove double preg check for class (remove 3rd argument on index and double check if clause in loop)
	 *	@todo		 (migration) adjust regex for upcoming Hydrogen with namespaces, maybe use reflection
	 */
	public function getMailClassNames( bool $strict = TRUE, string $sort = 'ASC' ): array
	{
		$list			= [];																	//  prepare empty result list
		$matches		= [];																	//  prepare empty matches list
		$pathClasses	= $this->options->get( 'path.classes' );							//  get path to mail classes from module config

//		not needed anymore: prefix path by frontend path, is set -> already done on init
//		if( $this->env->getModules()->has( 'Resource_Frontend' ) )
//			$pathClasses	= Logic_Frontend::getInstance( $this->env )->getPath().$pathClasses;

		if( !file_exists( $pathClasses ) ){
			if( $strict )
				throw new RuntimeException( 'Path to mail classes invalid or not existing' );
			return $list;
		}
		$regexExt		= "/\.php5?$/";																//  define regular expression of acceptable mail class file extensions
		$regexClass		= "/class\s+(Mail_\S+)\s+extends\s+Mail_/i";								//  define regular expression of acceptable mail class implementations
		$index			= new RecursiveRegexFileIndex( $pathClasses, $regexExt, $regexClass );		//  get recursive list of acceptable files
		foreach( $index as $file ){																	//  iterate recursive list
			$content	= FileReader::load( $file->getPathname() );									//  get content of class file
			preg_match_all( $regexClass, $content, $matches );									//  apply regular expression of mail class to content
			if( count( $matches[0] ) && count( $matches[1] ) ){										//  if valid mail class name found
				$path			= substr( $file->getPathname(), strlen( $pathClasses ) );			//  get filename of class file as list key
				$list[$path]	= $matches[1][0];													//  enqueue mail class name by list key
			}
		}
		in_array( $sort, ['DESC', -1], TRUE ) ? krsort( $list ) : ksort( $list );			//  sort list
		return $list;																				//  return map of found mail classes by their files
	}

	/**
	 *	Returns headers of mail as array map.
	 *	@access		public
	 *	@param		Mail_Abstract|string	$mail		Mail object or ID
	 *	@return		array					Map of headers
	 *	@deprecated	this method has no real value and will be removed
	 *	@todo		remove this method
	 *	@throws		SimpleCacheInvalidArgumentException
	 *	@throws		RuntimeException		if mail object is not extending Mail_Abstract
	 */
	public function getMailHeaders( Mail_Abstract|string $mail ): array
	{
		$mail	= $this->queue->getMailFromObjectOrId( $mail );
		if( !is_object( $mail->objectInstance ) )
			Logic_Mail_Compressor::decompressMailObject( $mail );
		if( !is_a( $mail->objectInstance, 'Mail_Abstract' ) )											//  stored mail object os not a known mail class
			throw new RuntimeException( 'Mail object is not extending Mail_Abstract' );
		$list	= [];
		foreach( $mail->objectInstance->mail->getHeaders()->getFields() as $headerField )
			$list[$headerField->getName()]	= $headerField->getValue();
		return $list;
	}

	/**
	 *	Returns mail parts.
	 *	@access		public
	 *	@param		Entity_Mail		$mail			Mail object
	 *	@return		array							List of mail part objects
	 *	@throws		InvalidArgumentException		if given argument is neither integer nor object
	 *	@throws		Exception						if given mail object is not uncompressed and unserialized (use getMail)
	 *	@throws		RuntimeException				if given mail object is of outdated Net_Mail and parser CMM_Mail_Parser is not available
	 *	@throws		RuntimeException				if given no parser is available for mail object
	 */
	public function getMailParts( Entity_Mail $mail ): array
	{
		Logic_Mail_Compressor::decompressMailObject( $mail );
		if( !is_a( $mail->objectInstance, 'Mail_Abstract' ) )									//  stored mail object os not a known mail class
			throw new Exception( 'Mail object is not extending Mail_Abstract, but '.get_class( $mail->objectInstance ) );
		if( $mail->objectInstance->mail instanceof MailMessage )									//  modern mail message with parsed body parts
			return $mail->objectInstance->mail->getParts();
		throw new RuntimeException( 'No mail parser available.' );							//  ... which is not available
	}

	/**
	 *	Returns absolute to mail attachments folder, also considering frontend environment setup.
	 *	@return		string
	 *	@throws		ReflectionException
	 */
	public function getMailAttachmentPath(): string
	{
		return $this->attacher->getMailAttachmentPath();
	}

	/**
	 *	Returns queued mail object of mail ID.
	 *	@access		public
	 *	@param		int|string		$mailId			ID of queued mail
	 *	@return		Entity_Mail						Mail object from queue
	 *	@throws		OutOfRangeException				if mail ID is not existing
	 *	@throws		MissingExtensionException		If extension for decompression (BZIP or GZIP) is not installed
	 *	@throws		ConversionException				If decompression (using BZIP or GZIP) or decoding (Base64) failed
	 *	@throws		ConversionException				If deserialization failed
	 */
	public function getQueuedMail( int|string $mailId ): Entity_Mail
	{
		return $this->queue->getMail( $mailId );
	}

	/**
	 *	Returns list of mails in queue.
	 *	@access		public
	 *	@param		array			$conditions		Map of Conditions to include in SQL Query
	 *	@param		array			$orders			Map of Orders to include in SQL Query
	 *	@param		array			$limits			Map of Limits to include in SQL Query
	 *	@param		array			$columns		List of columns to get
	 *	@return		array
	 */
	public function getQueuedMails( array $conditions = [], array $orders = [], array $limits = [], array $columns = [] ): array
	{
		return $this->queue->getMails( $conditions, $orders, $limits, $columns );
	}

	/**
	 *	Returns list of available mail templates.
	 *	@return		array<Entity_Mail_Template>
	 */
	public function getTemplates(): array
	{
		return $this->modelTemplate->getAll();
	}

	/**
	 *	Return map of mail classes used in all queued mails and the number of related mails.
	 *	@access		public
	 *	@param		array			$conditions		Map of Conditions to include in SQL Query
	 *	@return 	array
	 *	@todo		remove check for model method "getDistinct" after next minor framework release (0.8.8)
	 */
	public function getUsedMailClassNames( array $conditions = [] ): array
	{
		return $this->queue->getUsedMailClassNames( $conditions );
	}

	/**
	 *	Handles mail by sending immediately or appending to queue if allowed.
	 *	Sending mail immediately can be configured or forced by third argument.
	 *	@access		public
	 *	@param		Mail_Abstract	$mail			Mail to be sent
	 *	@param		object			$receiver		Data object of receiver, must have member 'email', should have 'userId' and 'username'
	 *	@param		string			$language		Language key
	 *	@param		boolean			$forceSendNow	Flag: override module settings and avoid queue
	 *	@return		boolean			TRUE if success
	 *	@throws		InvalidArgumentException		If receiver is neither an object nor an ID
	 *	@throws		InvalidArgumentException		If receiver object is missing "email"
	 *	@throws		ReflectionException				If mail logic construction failed
	 *	@throws		NotSupportedException			If configured transport type is invalid
	 *	@throws		RuntimeException				if message has no mail sender
	 *	@throws		RuntimeException				if message has no mail receivers
	 *	@throws		RuntimeException				if message has no mail body parts
	 *	@throws		RuntimeException				if connection to SMTP server failed
	 *	@throws		RuntimeException				if sending mail failed
	 */
	public function handleMail( Mail_Abstract $mail, object $receiver, string $language, bool $forceSendNow = NULL ): bool
	{
		if( $this->options->get( 'queue.enabled' ) && !$forceSendNow )
			return (bool) $this->queue->enqueueMail( $mail, $language, $receiver );
		return $this->sendMail( $mail, $receiver );
	}

	/**
	 *	Handles mail by sending immediately or appending to queue if allowed.
	 *	Sending mail immediately can be configured or forced by third argument.
	 *	@access		public
	 *	@param		Mail_Abstract	$mail			Mail to be sent
	 *	@param		Entity_User		$receiver		Data object of receiver, must have member 'email', should have 'userId' and 'username'
	 *	@param		boolean			$forceSendNow	Flag: override module settings and avoid queue
	 *	@return		boolean			TRUE if success
	 *	@throws		InvalidArgumentException		If receiver is neither an object nor an ID
	 *	@throws		InvalidArgumentException		If receiver object is missing "email"
	 *	@throws		ReflectionException				If mail logic construction failed
	 *	@throws		NotSupportedException			If configured transport type is invalid
	 *	@throws		RuntimeException				if message has no mail sender
	 *	@throws		RuntimeException				if message has no mail receivers
	 *	@throws		RuntimeException				if message has no mail body parts
	 *	@throws		RuntimeException				if connection to SMTP server failed
	 *	@throws		RuntimeException				if sending mail failed
	 */
	public function handleMailToUser( Mail_Abstract $mail, Entity_User $receiver, bool $forceSendNow = NULL ): bool
	{
		return $this->handleMail( $mail, $receiver, $receiver->language, $forceSendNow );
	}

	/**
	 *	Remove mail by its ID.
	 *	@access		public
	 *	@param		int|string			$mailId			ID of mail to remove
	 *	@return		boolean
	 */
	public function removeMail( int|string $mailId ): bool
	{
		return $this->queue->removeMail( $mailId );
	}

	/**
	 *	Send prepared mail right now.
	 *	@access		public
	 *	@param		Mail_Abstract	$mail			Mail to be sent
	 *	@param		integer|object	$receiver		User ID or data object of receiver (must have member 'email', should have 'userId' and 'username')
	 *	@return		boolean							TRUE if success
	 *	@throws		InvalidArgumentException		If receiver is neither an object nor an ID
	 *	@throws		InvalidArgumentException		If receiver object is missing "email"
	 *	@throws		ReflectionException				If mail logic construction failed
	 *	@throws		NotSupportedException			If configured transport type is invalid
	 *	@throws		RuntimeException				if message has no mail sender
	 *	@throws		RuntimeException				if message has no mail receivers
	 *	@throws		RuntimeException				if message has no mail body parts
	 *	@throws		RuntimeException				if connection to SMTP server failed
	 *	@throws		RuntimeException				if sending mail failed
	 */
	public function sendMail( Mail_Abstract $mail, int|object $receiver ): bool
	{
		$receiver	= $this->checkReceiver( $receiver );
		$mail->setEnv( $this->env );																//  override serialized environment
		$mail->initTransport();																		//  override serialized mail transfer

		if( $receiver instanceof Entity_User )
			return $mail->sendToUser( $receiver );
		return $mail->sendTo( $receiver );
	}

	/**
	 *	Send prepared mail right now.
	 *	Calls hook Mail::onSent (with mail ID as payload) after mail has given to transport.
	 *	@access		public
	 *	@param		Entity_Mail|int|string		$mailObjectOrId
	 *	@param		boolean			$forceResent	Flag: send mail again although last attempt was successful
	 *	@return		boolean
	 *	@throws		RuntimeException			if mail already has been sent or enqueued
	 *	@todo		use logging on exception (=sending mail failed)
	 *	@throws		ReflectionException
	 */
	public function sendQueuedMail( Entity_Mail|int|string $mailObjectOrId, bool $forceResent = FALSE ): bool
	{
		return $this->queue->sendMail( $mailObjectOrId, $forceResent );
	}

	/**
	 *	Set mail status.
	 *	New status will be validated against allowed status transitions.
	 *	@access		public
	 *	@param		Mail_Abstract|int|string	$mail		Mail object or ID
	 *	@param		integer					$status		Status to set, will be validated against allowed status transitions
	 *	@return 	boolean
	 *	@throws		DomainException			if given status is invalid
	 *	@throws		DomainException			if transition to new status is not allowed
	 *	@throws		ReflectionException
	 */
	public function setMailStatus( Mail_Abstract|int|string $mail, int $status ): bool
	{
		return $this->queue->setMailStatus( $mail, $status );
	}

	/**
	 *	Enable or disable use of queue or return current state.
	 *	Returns current state of no new state is given.
	 *	@access		public
	 *	@param		boolean|NULL	$toggle			New state or NULL to return current state
	 *	@return		boolean|self	Current state if no new state is given
	 */
	public function useQueue( ?bool $toggle = NULL ): bool|self
	{
		if( NULL === $toggle )
			return $this->options->get( 'queue.enabled' );
		$this->options->set( 'queue.enabled', $toggle );
		return $this;
	}

	//  --  STATIC PUBLIC  --  //

	//  --  PROTECTED  --  //


	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->queue			= Logic_Mail_Queue::getInstance( $this->env );
		$this->attacher			= Logic_Mail_Attacher::getInstance( $this->env );

		$this->modelTemplate	= new Model_Mail_Template( $this->env );

		/*  --  DEFAULT PATHS  --  */
		$this->options	= $this->env->getConfig()->getAll( 'module.resource_mail.', TRUE );

		/*  --  PATHS WITH FRONTEND  --  */
		if( $this->env->getModules()->has( 'Resource_Frontend' ) ){
			$frontend				= Logic_Frontend::getInstance( $this->env );
			$this->options			= new Dictionary( $frontend->getModuleConfigValues( 'Resource_Mail' ) );
			foreach( ['attachments', 'classes'] as $type )
				$this->options->set( 'path.'.$type, $frontend->getPath().$this->options->get( 'path.'.$type ) );
		}

//		$this->detectTemplateToUse();
	}
}
if( !class_exists( 'PHP_Incomplete_Class' ) ){
	class PHP_Incomplete_Class{}
}
