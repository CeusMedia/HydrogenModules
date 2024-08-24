<?php
use CeusMedia\Common\Net\API\Premailer;

class Logic_Newsletter_Editor extends Logic_Newsletter
{
	/**
	 *	@param		array		$data
	 *	@return		string
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function addGroup( array $data ): string
	{
		$data['createdAt']	= time();
		return $this->modelGroup->add( $data );
	}

	/**
	 *	@param		array		$data
	 *	@return		string
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function addNewsletter( array $data ): string
	{
		$data['creatorId']	= 0;
		$data['createdAt']	= time();
		return $this->modelNewsletter->add( $data, FALSE );
	}

	/**
	 *	@param		array		$data
	 *	@return		string
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function addTemplate( array $data ): string
	{
		$data['createdAt']	= time();
		return $this->modelTemplate->add( $data, FALSE );
	}

	/**
	 *	@param		int|string		$templateId
	 *	@param		string			$url
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function addTemplateStyle( int|string $templateId, string $url ): void
	{
		if( !strlen( trim( $url ) ) )
			throw new RuntimeException( 'No URL given' );
		$this->checkTemplateId( $templateId, TRUE );
		$styles	= $this->getTemplateAttributeList( $templateId, 'styles' );
		$styles[]	= $url;
		$this->setTemplateAttributeList( $templateId, 'styles', $styles );
	}

	/**
	 *	@param		string		$html
	 *	@param		int			$wrap
	 *	@return		string
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function convertHtmlToText( string $html, int $wrap = 65 ): string
	{
		if( $this->env->getConfig()->get( 'module.resource_newsletter.premailer.plain' ) ){
			$premailer	= new Premailer();
			$premailer->convertFromHtml( $html, ['line_length' => $wrap] );
			return $premailer->getPlainText();
		}
		if( class_exists( 'View_Helper_HtmlToPlainText' ) ){
			return View_Helper_HtmlToPlainText::convert( $html );
		}
		$replacements	= [
			"/\n\s+/"								=> "\n",							//  remove leading whitespace
			"/<a.+href=\"(.+)\"*>(.+)<\/a>/U"		=> "\\2 (\\1)",
			"/<img.+\/?>(\r?\n)*/U"					=> "",								//  remove images
			"/>\n+([^<])/U"							=> ">\\1",							//  remove line breaks before tag content
			"/([^>])(\r?\n)+</U"					=> "\\1<",							//  remove line breaks after tag content
			"/<h[1-2].*>(.+)<\/h[1-2]>(\r?\n)?/U"	=> "\n** \\1\n<hr2 />",				//  convert h1 and h2
			"/<h[3-4].*>(.+)<\/h[3-4]>(\r?\n)?/U"	=> "\n* \\1\n<hr />",				//  convert h3 and h4
			"/<h[5-6].*>(.+)<\/h[5-6]>(\r?\n)?/U"	=> "\n* \\1\n",						//  convert h5 and h6
			"/<hr ?\/?>/U"							=> str_repeat( "-", "40" )."\n",
			"/<hr2 ?\/?>/U"							=> str_repeat( "=", "40" )."\n",
			"/<li(\s+.+)?>(.+)<\/li>/U"				=> "- \\2",
			"/<b(\s+.+)?>(.+)<\/b>/U"				=> "*\\2*",
			"/<strong(\s+.+)?>(.+)<\/strong>/U"		=> "*\\2*",
			"/>(\r?\n)+<\//U"						=> "></",
			"/<ul(\s+.+)?>(\r?\n)?/"				=> "",
			"/<\/ul>(\r?\n)?/"						=> "\n",
			"/>\s+</s"								=> "><",
			"/<(p|div|span)(\s+.+)?><\/(p|div|span)>/U"	=> "",
			"/<p>(\r?\n)?/"							=> "",
			"/<\/p>(\r?\n)?/"						=> "\n\n",
		];
		$html		= html_entity_decode( $html );
		foreach( $replacements as $regex => $replacement )
			$html	= preg_replace( $regex, $replacement, $html );
		$html	= $wrap ? wordwrap( $html, $wrap ) : $html;
return $html;
		return strip_tags( $html );
	}

	/**
	 *	@param		int|string			$newsletterId
	 *	@param		int|string|NULL		$creatorId
	 *	@return		string
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function createQueue( int|string $newsletterId, int|string|NULL $creatorId = NULL ): string
	{
		return $this->modelQueue->add( [
			'newsletterId'	=> $newsletterId,
			'creatorId'		=> (int) $creatorId,
			'status'		=> Model_Newsletter_Queue::STATUS_NEW,
			'createdAt'		=> time(),
			'modifiedAt'	=> time(),
		] );
	}

	/**
	 *	@param		int|string		$readerLetterId
	 *	@return		bool
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function dequeue( int|string $readerLetterId ): bool
	{
		$letter	= $this->getReaderLetter( $readerLetterId );
		if( (int) $letter->status > 0 )
			throw new RuntimeException( 'Letter has been sent and cannot be removed' );
		return $this->modelReaderLetter->remove( $readerLetterId );
	}

	/**
	 *	@param		int|string		$groupId
	 *	@param		array			$data
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function editGroup( int|string $groupId, array $data ): void
	{
		$this->checkGroupId( $groupId, TRUE );
		$data['modifiedAt']	= time();
		$this->modelGroup->edit( $groupId, $data );
	}

	/**
	 *	@param		int|string		$letterId
	 *	@param		array			$data
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function editReaderLetter( int|string $letterId, array $data ): void
	{
		$this->checkReaderLetterId( $letterId, TRUE );
		$this->modelReaderLetter->edit( $letterId, $data );
	}

	/**
	 *	@param		int|string		$templateId
	 *	@param		array			$data
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function editTemplate( int|string $templateId, array $data ): void
	{
		$this->checkTemplateId( $templateId, TRUE );
		$data['modifiedAt']	= time();
		$this->modelTemplate->edit( $templateId, $data, FALSE );
	}

	/**
	 *	@param		int|string		$queueId
	 *	@param		int|string		$readerId
	 *	@param		int|string		$newsletterId
	 *	@param		bool			$allowDoubles
	 *	@return		int|string
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function enqueue( int|string $queueId, int|string $readerId, int|string $newsletterId, bool $allowDoubles = FALSE ): int|string
	{
		$indices	= [
			'newsletterReaderId'	=> $readerId,
			'newsletterQueueId'		=> $queueId,
			'newsletterId'			=> $newsletterId,
			'status'				=> '>= 0'
		];
		if( !$allowDoubles && $this->modelReaderLetter->getByIndices( $indices ) )
			return 0;
		$data		= [
			'newsletterReaderId'	=> $readerId,
			'newsletterQueueId'		=> $queueId,
			'newsletterId'			=> $newsletterId,
			'status'				=> 0,
			'enqueuedAt'			=> time()
		];
		return $this->modelReaderLetter->add( $data );
	}

	/**
	 *	@param		int|string		$groupId
	 *	@return		bool
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function removeGroup( int|string $groupId ): bool
	{
		$modelMail	= new Model_Mail( $this->env );
		$readers	= $this->getReadersOfGroup( $groupId );
		foreach( $readers as $reader ){
			$conditions	= [
				'newsletterReaderId'	=> $reader->newsletterReaderId,
			];
			$letters	= $this->getReaderLetters( $conditions );
			foreach( $letters as $letter ){
				if( $letter->mailId )
					$modelMail->remove( $letter->mailId );
				$this->modelReaderLetter->remove( $letter->newsletterReaderLetterId );
			}
			$this->modelReaderGroup->removeByIndex( 'newsletterGroupId', $groupId );
			if( !$this->getGroupsOfReader( $letter->newsletterReaderId ) )
				$this->modelReader->remove( $letter->newsletterReaderId );
		}
		return $this->modelGroup->remove( $groupId );
	}

	/**
	 *	@param		int|string		$readerId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function removeReader( int|string $readerId ): void
	{
		$this->checkReaderId( $readerId );
		$groups		= $this->getGroupsOfReader( $readerId );
		$letters	= $this->getLettersOfReader( $readerId );
		foreach( $groups as $group )
			$this->removeReaderFromGroup( $readerId, $group->newsletterGroupId );
		foreach( $letters as $letter )
			$this->modelReaderLetter->remove( $letter->newsletterReaderLetterId );
		$this->modelReader->remove( $readerId );
	}

	/**
	 *	Tries to remove newsletter and all of its related entities.
	 *	Calls hook Newsletter::removeNewsletter.
	 *	Removes reader letters and related mails.
	 *	Removes queues of newsletter before removing newsletter itself.
	 *	@access		public
	 *	@param		int|string		$newsletterId		ID of newsletter to remove
	 *	@return		boolean
	 *	@throws		InvalidArgumentException		if newsletter ID is not valid
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function removeNewsletter( int|string $newsletterId ): bool
	{
		$this->checkNewsletterId( $newsletterId, TRUE );
		$modelMail	= new Model_Mail( $this->env );												//  get mail model
		$payload	= ['newsletterId'	=> $newsletterId];
		$this->env->getCaptain()->callHook( 'Newsletter', 'removeNewsletter', $this, $payload );
		$mailIds	= $this->modelReaderLetter->getAllByIndices( array(							//  get reader letters
			'newsletterId'	=> $newsletterId,													//  ... of newsletter
			'mailId'		=> '> 0',															//  ... having a mail ID
		), [], [], ['mailId'] );																//  ... returning mail IDs, only
		if( $mailIds )
			$modelMail->removeByIndex( 'mailId', $mailIds );									//  remove mails by IDs
		$this->modelReaderLetter->removeByIndex( 'newsletterId', $newsletterId );				//  remove reader letters of newsletter
		$this->modelQueue->removeByIndex( 'newsletterId', $newsletterId );						//  remove queues or newsletter
		return $this->modelNewsletter->remove( $newsletterId );									//  remove newsletter itself
	}

	/**
	 * @param int|string $templateId
	 * @return bool
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	public function removeTemplate( int|string $templateId ): bool
	{
		$this->checkTemplateId( $templateId, TRUE );
		$newsletterConditions	= ['newsletterTemplateId' => $templateId, 'status' => 2];
		if( $this->getNewsletters( $newsletterConditions ) )
			throw new RuntimeException( 'Template already used in sent newsletter' );
		return $this->modelTemplate->remove( $templateId );
	}

	/**
	 * @param int|string $templateId
	 * @param $index
	 * @return void
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	public function removeTemplateStyle( int|string $templateId, $index ): void
	{
		$this->checkTemplateId( $templateId, TRUE );
		$styles		= $this->getTemplateAttributeList( $templateId, 'styles' );
		if( isset( $styles[$index] ) )
			unset( $styles[$index] );
		$this->setTemplateAttributeList( $templateId, 'styles', $styles );
	}

	/**
	 *	@deprecated		not used anymore, replaced by sendTestLetter
	 */
/*	public function sendLetter( $readerLetterId ){
		if( !$this->env->getModules()->has( 'Resource_Mail' ) )
			throw new RuntimeException( 'Module "Resource_Mail" is not installed' );
		$this->checkReaderLetterId( $readerLetterId );
		$readerLetter	= $this->getReaderLetter( $readerLetterId );
		$newsletter		= $this->getNewsletter( $readerLetter->newsletterId );
		$logicMail		= Logic_Mail::getInstance( $this->env );
		$helper			= new View_Helper_Newsletter( $this->env, $newsletter->newsletterTemplateId );
		$data			= $helper->prepareReaderDataForLetter( $readerLetterId );
		$language		= $this->env->getLanguage()->getLanguage();
		$mail			= new Mail_Newsletter( $this->env, $data );
		$logicMail->appendRegisteredAttachments( $mail, $language );

		$reader		= $this->getReader( $readerLetter->newsletterReaderId );
		$receiver	= (object) [
			'username'	=> $reader->firstname.' '.$reader->surname,
			'email'		=> $reader->email,
		];
		$status	= $logicMail->handleMail( $mail, $receiver, $language );
		$data		= array(
			'status'	=> Model_Newsletter_Reader_Letter::STATUS_SENT,
			'sentAt'	=> time(),
		);
		$this->editReaderLetter( $readerLetterId, $data );
		return $status;
	}*/

	/**
	 *	@param		int|string		$newsletterId
	 *	@param		int|string		$readerId
	 *	@return		bool
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function sendTestLetter( int|string $newsletterId, int|string $readerId ): bool
	{
		if( !$this->env->getModules()->has( 'Resource_Mail' ) )
			throw new RuntimeException( 'Module "Resource_Mail" is not installed' );
		/** @var Logic_Mail $logicMail */
		$logicMail		= Logic_Mail::getInstance( $this->env );

		$mail			= new Mail_Newsletter( $this->env, [
			'newsletterId'	=> $newsletterId,
			'readerId'		=> $readerId,
		] );
//		$logicMail->appendRegisteredAttachments( $mail, $language );

		$reader		= $this->getReader( $readerId );
		$receiver	= (object) [
			'username'	=> $reader->firstname.' '.$reader->surname,
			'email'		=> $reader->email,
		];
		return $logicMail->sendMail( $mail, $receiver );
	}

	/**
	 *	@param		int|string		$templateId
	 *	@param		$status
	 *	@return		bool
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function setTemplateStatus( int|string $templateId, $status ): bool
	{
		$template	= $this->getTemplate( $templateId );
		if( $template->status == $status )
			return FALSE;
		return (bool) $this->modelTemplate->edit( $templateId, [
			'status'		=> $status,
			'modifiedAt'	=> time(),
		] );
	}

	/**
	 *	@param		int|string		$templateId
	 *	@param		string			$columnKey
	 *	@param		array			$list
	 *	@return		int
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function setTemplateAttributeList( int|string $templateId, string $columnKey, array $list ): int
	{
		$this->checkTemplateId( $templateId, TRUE );
		$list		= $list ? "|".implode( "|", $list )."|" : "";
		return $this->modelTemplate->edit( $templateId, [$columnKey => $list] );
	}
}
