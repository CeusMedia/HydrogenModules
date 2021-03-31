<?php
class Logic_Newsletter_Editor extends Logic_Newsletter
{
	public function addGroup( array $data )
	{
		$data['createdAt']	= time();
		return $this->modelGroup->add( $data );
	}

	public function addNewsletter( array $data )
	{
		$data['creatorId']	= 0;
		$data['createdAt']	= time();
		return $this->modelNewsletter->add( $data, FALSE );
	}

	public function addTemplate( array $data )
	{
		$data['createdAt']	= time();
		return $this->modelTemplate->add( $data, FALSE );
	}

	public function addTemplateStyle( $templateId, $url )
	{
		if( !strlen( trim( $url ) ) )
			throw new RuntimeException( 'No URL given' );
		$this->checkTemplateId( $templateId, TRUE );
		$styles	= $this->getTemplateAttributeList( $templateId, 'styles' );
		$styles[]	= $url;
		$this->setTemplateAttributeList( $templateId, 'styles', $styles );
	}

	public function convertHtmlToText( string $html, int $wrap = 65 )
	{
		if( $this->env->getConfig()->get( 'module.resource_newsletter.premailer.plain' ) ){
			$premailer	= new Net_API_Premailer();
			$premailer->convertFromHtml( $html, array( 'line_length' => $wrap ) );
			return $premailer->getPlainText();
		}
		if( class_exists( 'View_Helper_HtmlToPlainText' ) ){
			return View_Helper_HtmlToPlainText::convert( $html );
		}
		$replacements	= array(
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
		);
		$html		= html_entity_decode( $html );
		foreach( $replacements as $regex => $replacement )
			$html	= preg_replace( $regex, $replacement, $html );
		$html	= $wrap ? wordwrap( $html, $wrap ) : $html;
return $html;
		return strip_tags( $html );
	}

	public function createQueue( $newsletterId, $creatorId = NULL )
	{
		return $this->modelQueue->add( array(
			'newsletterId'	=> $newsletterId,
			'creatorId'		=> (int) $creatorId,
			'status'		=> Model_Newsletter_Queue::STATUS_NEW,
			'createdAt'		=> time(),
			'modifiedAt'	=> time(),
		) );
	}

	public function dequeue( $readerLetterId )
	{
		$letter	= $this->getReaderLetter( $readerLetterId );
		if( (int) $letter->status > 0 )
			throw new RuntimeException( 'Letter has been sent and cannot be removed' );
		return $this->modelReaderLetter->remove( $readerLetterId );
	}

	public function editGroup( $groupId, array $data )
	{
		$this->checkGroupId( $groupId, TRUE );
		$data['modifiedAt']	= time();
		$this->modelGroup->edit( $groupId, $data );
	}

	public function editReaderLetter( $letterId, array $data )
	{
		$this->checkReaderLetterId( $letterId, TRUE );
		$this->modelReaderLetter->edit( $letterId, $data );
	}

	public function editTemplate( $templateId, array $data )
	{
		$this->checkTemplateId( $templateId, TRUE );
		$data['modifiedAt']	= time();
		$this->modelTemplate->edit( $templateId, $data, FALSE );
	}

	public function enqueue( $queueId, $readerId, $newsletterId, bool $allowDoubles = FALSE )
	{
		$indices	= array(
			'newsletterReaderId'	=> $readerId,
			'newsletterQueueId'		=> $queueId,
			'newsletterId'			=> $newsletterId,
			'status'				=> '>= 0'
		);
		if( !$allowDoubles && $this->modelReaderLetter->getByIndices( $indices ) )
			return 0;
		$data		= array(
			'newsletterReaderId'	=> $readerId,
			'newsletterQueueId'		=> $queueId,
			'newsletterId'			=> $newsletterId,
			'status'				=> 0,
			'enqueuedAt'			=> time()
		);
		return $this->modelReaderLetter->add( $data );
	}

	public function removeGroup( $groupId )
	{
		$modelMail	= new Model_Mail( $this->env );
		$readers	= $this->getReadersOfGroup( $groupId );
		foreach( $readers as $reader ){
			$conditions	= array(
				'newsletterReaderId'	=> $reader->newsletterReaderId,
			);
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

	public function removeReader( $readerId )
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
	 *	@param		integer		$newsletterId		ID of newsletter to remove
	 *	@return		boolean
	 *	@throws		InvalidArgumentException		if newsletter ID is not valid
	 */
	public function removeNewsletter( $newsletterId )
	{
		$this->checkNewsletterId( $newsletterId, TRUE );
		$modelMail	= new Model_Mail( $this->env );												//  get mail model
		$this->env->getCaptain()->callHook( 'Newsletter', 'removeNewsletter', $this, array(
			'newsletterId'	=> $newsletterId,
		) );
		$mailIds	= $this->modelReaderLetter->getAllByIndices( array(							//  get reader letters
			'newsletterId'	=> $newsletterId,													//  ... of newsletter
			'mailId'		=> '> 0',															//  ... having a mail ID
		), array(), array(), array( 'mailId' ) );												//  ... returning mail IDs, only
		if( $mailIds )
			$modelMail->removeByIndex( 'mailId', $mailIds );									//  remove mails by IDs
		$this->modelReaderLetter->removeByIndex( 'newsletterId', $newsletterId );				//  remove reader letters of newsletter
		$this->modelQueue->removeByIndex( 'newsletterId', $newsletterId );						//  remove queues or newsletter
		return $this->modelNewsletter->remove( $newsletterId );									//  remove newsletter itself
	}

	public function removeTemplate( $templateId )
	{
		$this->checkTemplateId( $templateId, TRUE );
		$newsletterConditions	= array( 'newsletterTemplateId' => $templateId, 'status' => 2 );
		if( $this->getNewsletters( $newsletterConditions ) )
			throw new RuntimeException( 'Template already used in sent newsletter' );
		return $this->modelTemplate->remove( $templateId );
	}

	public function removeTemplateStyle( $templateId, $index )
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
		$receiver	= (object) array(
			'username'	=> $reader->firstname.' '.$reader->surname,
			'email'		=> $reader->email,
		);
		$status	= $logicMail->handleMail( $mail, $receiver, $language );
		$data		= array(
			'status'	=> Model_Newsletter_Reader_Letter::STATUS_SENT,
			'sentAt'	=> time(),
		);
		$this->editReaderLetter( $readerLetterId, $data );
		return $status;
	}*/

	public function sendTestLetter( $newsletterId, $readerId )
	{
		if( !$this->env->getModules()->has( 'Resource_Mail' ) )
			throw new RuntimeException( 'Module "Resource_Mail" is not installed' );
		$logicMail		= Logic_Mail::getInstance( $this->env );

		$mail			= new Mail_Newsletter( $this->env, array(
			'newsletterId'	=> $newsletterId,
			'readerId'		=> $readerId,
		) );
//		$logicMail->appendRegisteredAttachments( $mail, $language );

		$reader		= $this->getReader( $readerId );
		$receiver	= (object) array(
			'username'	=> $reader->firstname.' '.$reader->surname,
			'email'		=> $reader->email,
		);
		return $logicMail->sendMail( $mail, $receiver );
	}

	public function setTemplateStatus( $templateId, $status )
	{
		$template	= $this->getTemplate( $templateId );
		if( $template->status == $status )
			return;
		return $this->modelTemplate->edit( $templateId, array(
			'status'		=> $status,
			'modifiedAt'	=> time(),
		) );
	}

	public function setTemplateAttributeList( $templateId, string $columnKey, array $list )
	{
		$this->checkTemplateId( $templateId, TRUE );
		$list		= $list ? "|".implode( "|", $list )."|" : "";
		return $this->modelTemplate->edit( $templateId, array( $columnKey => $list ) );
	}
}
