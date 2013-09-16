<?php
class Logic_Newsletter extends CMF_Hydrogen_Environment_Resource_Logic{

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment_Abstract	$env	Environment
	 *	@param		mixed		$a		Test argument
	 *	@return		void
	 */
	public function  __construct( CMF_Hydrogen_Environment_Abstract $env, $a = NULL ) {
		parent::__construct( $env, $a );
	}

	protected function __onInit( $a = NULL ){
		$this->modelNewsletter		= new Model_Newsletter( $this->env );
		$this->modelGroup			= new Model_Newsletter_Group( $this->env );
		$this->modelReader			= new Model_Newsletter_Reader( $this->env );
		$this->modelTemplate		= new Model_Newsletter_Template( $this->env );
		$this->modelReaderGroup		= new Model_Newsletter_Reader_Group( $this->env );
		$this->modelReaderLetter	= new Model_Newsletter_Reader_Letter( $this->env );
	}

	public function addGroup( $data ){
		$data['createdAt']	= time();
		return $this->modelGroup->add( $data );
	}

	public function addNewsletter( $data ){
		$data['createdAt']	= time();
		return $this->modelNewsletter->add( $data, FALSE );
	}

	public function addReader( $data ){
		$data['registeredAt']	= time();
		return $this->modelReader->add( $data );
	}

	public function addReaderToGroup( $readerId, $groupId ){
		$this->checkReaderId( $readerId );
		$this->checkGroupId( $groupId );
		$indices	= array(
			'newsletterReaderId'	=> $readerId,
			'newsletterGroupId'		=> $groupId,
		);
		return $this->modelReaderGroup->add( $indices );
	}

	public function addTemplate( $data ){
		$data['createdAt']	= time();
		return $this->modelTemplate->add( $data, FALSE );
	}

	public function addTemplateScript( $templateId, $url ){
		if( !strlen( trim( $url ) ) )
			throw new RuntimeException( 'No URL given' );
		$this->checkTemplateId( $templateId, TRUE );
		$scripts	= $this->getTemplateAttributeList( $templateId, 'scripts' );
		$scripts[]	= $url;
		$this->setTemplateAttributeList( $templateId, 'scripts', $scripts );
	}

	public function addTemplateStyle( $templateId, $url ){
		if( !strlen( trim( $url ) ) )
			throw new RuntimeException( 'No URL given' );
		$this->checkTemplateId( $templateId, TRUE );
		$styles	= $this->getTemplateAttributeList( $templateId, 'styles' );
		$styles[]	= $url;
		$this->setTemplateAttributeList( $templateId, 'styles', $styles );
	}

	public function checkGroupId( $groupId, $throwException = FALSE ){
		if( $this->modelGroup->has( (int) $groupId ) )
			return TRUE;
		if( $throwException )
			throw new InvalidArgumentException( 'Invalid newsletter group ID '.$groupId );
		return FALSE;
	}

	public function checkNewsletterId( $newsletterId, $throwException = FALSE ){
		if( $this->modelNewsletter->has( (int) $newsletterId ) )
			return TRUE;
		if( $throwException )
			throw new InvalidArgumentException( 'Invalid newsletter ID '.$newsletterId );
		return FALSE;
	}

	public function checkReaderLetterId( $readerLetterId, $throwException = FALSE ){
		if( $this->modelReaderLetter->has( (int) $readerLetterId ) )
			return TRUE;
		if( $throwException )
			throw new InvalidArgumentException( 'Invalid newsletter reader letter ID '.$readerLetterId );
		return FALSE;
	}

	public function checkReaderId( $readerId, $throwException = FALSE ){
		if( $this->modelReader->has( (int) $readerId ) )
			return TRUE;
		if( $throwException )
			throw new InvalidArgumentException( 'Invalid newsletter reader ID '.$readerId );
		return FALSE;
	}

	public function checkTemplateId( $templateId, $throwException = FALSE ){
		if( $this->modelTemplate->has( (int) $templateId ) )
			return TRUE;
		if( $throwException )
			throw new InvalidArgumentException( 'Invalid newsletter template ID '.$templateId );
		return FALSE;
	}

	public function dequeue( $readerLetterId ){
		$letter	= $this->getReaderLetter( $readerLetterId );
		if( (int) $letter->status > 0 )
			throw new RuntimeException( 'Letter has been sent and cannot be removed' );
		return $this->modelReaderLetter->remove( $readerLetterId );
	}

	public function editGroup( $groupId, $data ){
		$this->checkGroupId( $groupId, TRUE );
		$data['modifiedAt']	= time();
		$this->modelGroup->edit( $groupId, $data );
	}

	public function editNewsletter( $newsletterId, $data ){
		$this->checkNewsletterId( $newsletterId, TRUE );
		$data['modifiedAt']	= time();
		$this->modelNewsletter->edit( $newsletterId, $data, FALSE );
	}

	public function editReader( $readerId, $data ){
		$this->checkReaderId( $readerId, TRUE );
		$this->modelReader->edit( $readerId, $data );
	}

	public function editReaderLetter( $letterId, $data ){
		$this->checkReaderLetterId( $letterId, TRUE );
		$this->modelReaderLetter->edit( $letterId, $data );
	}

	public function editTemplate( $templateId, $data ){
		$this->checkTemplateId( $templateId, TRUE );
		$data['modifiedAt']	= time();
		$this->modelTemplate->edit( $templateId, $data, FALSE );
	}

	public function enqueue( $readerId, $newsletterId, $allowDoubles = FALSE ){
		$indices	= array(
			'newsletterReaderId'	=> $readerId,
			'newsletterId'			=> $newsletterId,
			'status'				=> '>=0'
		);
		if( !$allowDoubles && $this->modelReaderLetter->getByIndices( $indices ) )
			return 0;
		$data		= array(
			'newsletterReaderId'	=> $readerId,
			'newsletterId'			=> $newsletterId,
			'status'				=> 0,
			'enqueuedAt'			=> time()
		);
		return $this->modelReaderLetter->add( $data );
	}

	protected function generateMail( $newsletterId, $readerId, $data = array(), $strict = TRUE ){
		$config		= $this->env->getConfig()->getAll( 'module.work_newsletter.', TRUE );
		$newsletter	= $this->getNewsletter( $newsletterId );
		$reader		= $this->getReader( $readerId );

		$mail		= new Net_Mail();
		$mail->setReceiver( $reader->email );
		$mail->setSender( $config->get( 'email.sender' ) );
		$mail->setSubject( $newsletter->subject );
		$mail->setHeaderPair( 'X-Auto-Response-Suppress', 'All' );

		$plain		= $this->renderNewsletterPlain( $newsletterId, $readerId, $data );
		$body		= new Net_Mail_Body( base64_encode( $plain ), Net_Mail_Body::TYPE_PLAIN );
		$body->setContentEncoding( "base64" );
		$mail->addBody( $body );

		$html		= $this->renderNewsletterHtml( $newsletterId, $readerId, $data );
		$body		= new Net_Mail_Body( base64_encode( $html ), Net_Mail_Body::TYPE_HTML );
		$body->setContentEncoding( "base64" );
		$mail->addBody( $body );
		return $mail;
	}

	public function getGroup( $groupId ){
		$this->checkGroupId( $groupId, TRUE );
		return $this->modelGroup->get( $groupId );
	}

	public function getGroupReaders( $groupId ){
		$list		= array();
		$readers	= array();
		foreach( $this->modelReader->getAllByIndex( 'status', 1 ) as $reader )
			$readers[$reader->newsletterReaderId]	= $reader;
		$relations	= $this->modelReaderGroup->getAllByIndex( 'newsletterGroupId', $groupId );
		foreach( $relations as $relation )
			if( array_key_exists( $relation->newsletterReaderId, $readers ) )
				$list[]	= $readers[$relation->newsletterReaderId];
		return $list;
	}

	public function getGroups( $conditions = array(), $orders = array() ){
		$list	= array();
		foreach( $this->modelGroup->getAll( $conditions, $orders ) as $group )
			$list[$group->newsletterGroupId]	= $group;
		return $list;
	}

	public function getGroupsOfReader( $readerId, $conditions = array(), $orders = array() ){
		$this->checkReaderId( $readerId, TRUE );
		$list		= array();
		$groupIds	= array();
		$relations	= $this->modelReaderGroup->getAllByIndex( 'newsletterReaderId', $readerId );
		foreach( $relations as $relation )
			$groupIds[]	= $relation->newsletterGroupId;
		if( $groupIds ){
			$conditions['newsletterGroupId']	= $groupIds;
			foreach( $this->modelGroup->getAll( $conditions, $orders ) as $group )
				$list[$group->newsletterGroupId]	= $group;
		}
		return $list;
	}

	public function getLettersOfReader( $readerId, $conditions = array(), $orders = array() ){
		$this->checkReaderId( $readerId, TRUE );
		$list		= array();
		$letterIds	= array();
		foreach( $this->modelReaderLetter->getAllByIndex( 'newsletterReaderId', $readerId ) as $letter )
			$letterIds[]	= $letter->newsletterId;
		if( $letterIds ){
			$conditions['newsletterId']	= $lettersByNewsletterId;
			$newsletters	= $this->modelNewsletter->getAll( $conditions, $orders );
			foreach( $newsletters as $newsletter ){
				$letter	= $lettersByNewsletterId[$newsletter->newsletterId];
				$letter->newsletter				= $newsletter;
				$list[$letter->newsletterId]	= $letter;
			}
		}
		return $list;
	}

	public function getNewsletter( $newsletterId ){
		$this->checkNewsletterId( $newsletterId, TRUE );
		return $this->modelNewsletter->get( $newsletterId );
	}

	public function getNewsletters( $conditions = array(), $orders = array() ){
		$list	= array();
		foreach( $this->modelNewsletter->getAll( $conditions, $orders ) as $newsletter )
			$list[$newsletter->newsletterId]	= $newsletter;
		return $list;
	}

	public function getReader( $readerId ){
		$this->checkReaderId( $readerId, TRUE );
		return $this->modelReader->get( $readerId );
	}

	public function getReaderLetter( $readerLetterId ){
		return $this->modelReaderLetter->get( $readerLetterId );
	}

	public function getReaderLetters( $conditions = array(), $orders = array() ){
		$list	= array();
		foreach( $this->modelReaderLetter->getAll( $conditions, $orders ) as $letter ){
			$letter->reader		= $this->getReader( $letter->newsletterReaderId );
			$list[$letter->newsletterReaderLetterId]	= $letter;
		}
		return $list;
	}

	public function getReaders( $conditions = array(), $orders = array() ){
		$list	= array();
		foreach( $this->modelReader->getAll( $conditions, $orders ) as $reader )
			$list[$reader->newsletterReaderId]	= $reader;
		return $list;
	}

	public function getReadersOfGroup( $groupId, $conditions = array(), $orders = array() ){
		return $this->getReadersOfGroups( array( $groupId ), $conditions = array(), $orders = array() );
	}

	public function getReadersOfGroups( $groupIds, $conditions = array(), $orders = array() ){
		$list		= array();
		$readerIds	= array();
		foreach( $groupIds as $groupId ){
			$relations	= $this->modelReaderGroup->getAllByIndex( 'newsletterGroupId', $groupId );
			foreach( $relations as $relation )
				$readerIds[]	= $relation->newsletterReaderId;
		}
		if( $readerIds ){
			$conditions['newsletterReaderId']	= $readerIds;
			$readers	= $this->modelReader->getAll( $conditions, $orders );
			foreach( $readers as $reader )
				$list[$reader->newsletterReaderId]	= $reader;
		}
		return $list;
	}

	public function getTemplate( $templateId ){
		$this->checkTemplateId( $templateId, TRUE );
		return $this->modelTemplate->get( $templateId );
	}

	public function getTemplates( $conditions = array(), $orders = array() ){
		$list	= array();
		foreach( $this->modelTemplate->getAll( $conditions, $orders ) as $template )
			$list[$template->newsletterTemplateId]	= $template;
		return $list;
	}

	public function getTemplateAttributeList( $templateId, $columnKey ){
		$this->checkTemplateId( $templateId, TRUE );
		$template	= $this->modelTemplate->get( $templateId );
		$list		= array();
		if( strlen( trim( $template->$columnKey ) ) ){
			$list	= substr( $template->$columnKey, 1, -1 );
			$list	= new ADT_List( explode( "|", $list ) );
			$list	= $list->getValues();
		}
		return $list;
	}

	public function removeGroup( $groupId ){
		if( $this->getReadersOfGroup( $groupId ) )
			throw new RuntimeException( 'Group not empty' );
		return $this->modelGroup->remove( $groupId );
	}

	public function removeReader( $readerId ){
		$this->checkReaderId( $readerId );
		$groups		= $this->getGroupsOfReader( $readerId );
		$letters	= $this->getLettersOfReader( $readerId );
		foreach( $groups as $group )
			$this->removeReaderFromGroup( $readerId, $group->newsletterGroupId );
		foreach( $letters as $letter )
			$this->modelReaderLetter->remove( $letter->newsletterReaderLetterId );
		$this->modelReader->remove( $readerId );
	}

	public function removeReaderFromGroup( $readerId, $groupId ){
		$this->checkReaderId( $readerId );
		$this->checkGroupId( $groupId );
		$indices	= array(
			'newsletterReaderId'	=> $readerId,
			'newsletterGroupId'		=> $groupId,
		);
		return $this->modelReaderGroup->removeByIndices( $indices );
	}

	public function removeTemplateScript( $templateId, $index ){
		$this->checkTemplateId( $templateId, TRUE );
		$scripts	= $this->getTemplateAttributeList( $templateId, 'scripts' );
		if( isset( $scripts[$index] ) )
			unset( $scripts[$index] );
		$this->setTemplateAttributeList( $templateId, 'scripts', $scripts );
	}

	public function removeTemplateStyle( $templateId, $index ){
		$this->checkTemplateId( $templateId, TRUE );
		$styles		= $this->getTemplateAttributeList( $templateId, 'styles' );
		if( isset( $styles[$index] ) )
			unset( $styles[$index] );
		$this->setTemplateAttributeList( $templateId, 'styles', $styles );
	}

	public function renderNewsletterPlain( $newsletterId, $readerId = NULL ){
		$newsletter	= $this->getNewsletter( $newsletterId );
		$helper		= new View_Helper_Newsletter_Mail( $this->env, $newsletter->newsletterTemplateId );
		$data		= array(
			'title'		=> $newsletter->heading, 
			'content'	=> wordwrap( $newsletter->plain, 78, "\n", FALSE ),
		);
		if( $readerId ){
			$reader		= $this->getReader( $readerId );
			$data['prefix']			= $reader->prefix;
			$data['firstname']		= $reader->firstname;
			$data['surname']		= $reader->surname;
			$data['email']			= $reader->email;
		}
		return $helper->renderPlain( $data );
	}

	public function renderNewsletterHtml( $newsletterId, $readerId = NULL, $data = array(), $strict = TRUE ){
		$newsletter	= $this->getNewsletter( $newsletterId );
		$helper		= new View_Helper_Newsletter_Mail( $this->env, $newsletter->newsletterTemplateId );
		$data['title']		= $newsletter->heading;
		$data['content']	= $newsletter->html;
		if( $readerId ){
			$reader		= $this->getReader( $readerId );
			$data['prefix']			= $reader->prefix;
			$data['firstname']		= $reader->firstname;
			$data['surname']		= $reader->surname;
			$data['email']			= $reader->email;
		}
		return $helper->renderHtml( $data, $strict );
	}

	public function sendLetter( $readerLetterId, $data = array() ){
		if( !$this->env->getModules()->has( 'Resource_Mail' ) )
			throw new RuntimeException( 'Module "Resource_Mail" is not installed' );
		$this->checkReaderLetterId( $readerLetterId );
		$letter	= $this->getReaderLetter( $readerLetterId );
		$mail	= $this->generateMail( $letter->newsletterId, $letter->newsletterReaderId, $data );

		$config		= $this->env->getConfig()->getAll( 'module.resource_mail.transport.', TRUE );
		$transport	= new Net_Mail_Transport_Default();
		if( strtolower( $config->get( 'type' ) ) == "smtp" ){
			$transport	= new Net_Mail_Transport_SMTP( $config->get( 'hostname' ) );
			$transport->setAuthUsername( $config->get( 'username' ) );
			$transport->setAuthPassword( $config->get( 'password' ) );
			$transport->setVerbose( FALSE );
		}
		$transport->send( $mail );
		$data	= array(
			'status'	=> Model_Newsletter_Reader_Letter::STATUS_SENT,
			'sentAt'	=> time(),
		);
		$this->editReaderLetter( $readerLetterId, $data );
	}

	public function setTemplateAttributeList( $templateId, $columnKey, $list ){
		$this->checkTemplateId( $templateId, TRUE );
		$list		= $list ? "|".implode( "|", $list )."|" : "";
		return $this->modelTemplate->edit( $templateId, array( $columnKey => $list ) );
	}

	public function trackLetterOpening( $letterId ){
		$letter	= $this->getReaderLetter( $letterId );												//  get letter
		if( (int) $letter->status !== 1 )															//  letter is NOT in status "sent"
			return FALSE;																			//  quit
		return (boolean) $this->editReaderLetter( $letterId, array(									//  edit letter entry
			'status'	=> Model_Newsletter_Reader_Letter::STATUS_OPENED,							//  set status to "opened"
			'openedAt'	=> time(),																	//  note opening time
		) );
	}
}
?>
