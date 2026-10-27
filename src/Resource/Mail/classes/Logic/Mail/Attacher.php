<?php

/**
 *	@author		Christian Würker <christian.wuerker@ceusmedia.de>
 */

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Exception\IO as IoException;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Definition as ModuleDefinition;
use CeusMedia\HydrogenFramework\Logic;

/**
 *	@author		Christian Würker <christian.wuerker@ceusmedia.de>
 *	@todo		code doc
 */
class Logic_Mail_Attacher extends Logic
{
	protected Dictionary $options;
	protected Model_Mail_Attachment $modelAttachment;
	protected string $pathAttachments;
	protected string $frontendPath;

	/**
	 *	...
	 *	@access		public
	 *	@param		Mail_Abstract	$mail			...
	 *	@param		string			$language		...
	 *	@return		int				Number of added attachments
	 *	@throws		IoException
	 */
	public function appendRegisteredAttachments( Mail_Abstract $mail, string $language ): int
	{
		$class			= get_class( $mail );
		$indices		= ['className' => $class, 'status' => Model_Mail::STATUS_SENDING, 'language' => $language];
		/** @var array<Entity_Mail_Attachment> $attachments */
		$attachments	= $this->modelAttachment->getAllByIndices( $indices );
		foreach( $attachments as $attachment ){
			$fileName	= $this->pathAttachments.$attachment->filename;
			$mail->addAttachment( $fileName, $attachment->mimeType );
		}
		return count( $attachments );
	}

	/**
	 *	Returns absolute to mail attachments folder, also considering frontend environment setup.
	 *	@return		string
	 *	@throws		ReflectionException
	 */
	public function getMailAttachmentPath(): string
	{
		$configKey			= 'path.attachments';

		if( $this->env->getModules()->has( 'Resource_Frontend' ) ){
			/** @var ModuleDefinition $module */
			$module	= $this->env->getModules()->get( 'Resource_Frontend' );
			if( './' !== $module->getConfigAsDictionary()->get( 'path' ) ){
				$frontend	= Logic_Frontend::getInstance( $this->env );
				$path		= $frontend->getModuleConfigValue( 'Resource_Mail', $configKey );
				return $frontend->getUri().$path;
			}
		}

		$path	= $this->env->getConfig()->get( 'module.resource_mail.'.$configKey );
		return $this->env->uri.$path;
	}


	//  --  STATIC PUBLIC  --  //

	//  --  PROTECTED  --  //


	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		/*  --  INIT QUEUE  --  */
		$this->modelAttachment	= new Model_Mail_Attachment( $this->env );

		/*  --  DEFAULT PATHS  --  */
		$this->options	= $this->env->getConfig()->getAll( 'module.resource_mail.', TRUE );
		$this->frontendPath		= './';
		$this->pathAttachments	= $this->options->get( 'path.attachments' );

		/*  --  PATHS WITH FRONTEND  --  */
		if( $this->env->getModules()->has( 'Resource_Frontend' ) ){
			$frontend				= Logic_Frontend::getInstance( $this->env );
			$this->options			= new Dictionary( $frontend->getModuleConfigValues( 'Resource_Mail' ) );
			$this->frontendPath		= $frontend->getPath();
			$this->pathAttachments	= $this->frontendPath.$this->pathAttachments;
			foreach( ['attachments', 'classes'] as $type )
				$this->options->set( 'path.'.$type, $frontend->getPath().$this->options->get( 'path.'.$type ) );
		}

//		$this->detectTemplateToUse();

		/*  --  ENSURE ATTACHMENTS FOLDER  --  */
		if( !file_exists( $this->pathAttachments ) ){
			mkdir( $this->pathAttachments, 0755, TRUE );
			if( !file_exists( $this->pathAttachments.'.htaccess' ) )
				copy( 'classes/.htaccess', $this->pathAttachments.'.htaccess' );
		}
	}
}
if( !class_exists( 'PHP_Incomplete_Class' ) ){
	class PHP_Incomplete_Class{}
}
