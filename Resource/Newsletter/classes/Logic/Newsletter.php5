<?php
/**
 *	@todo	extend CMF_Hydrogen_Logic instead
 *	@todo	code doc
 */
class Logic_Newsletter extends CMF_Hydrogen_Environment_Resource_Logic
{
	/**	@var		Model_Newsletter_Group			$modelGroup */
	protected $modelGroup;

	/**	@var		Model_Newsletter				$modelNewsletter */
	protected $modelNewsletter;

	/**	@var		Model_Newsletter_Queue			$modelQueue */
	protected $modelQueue;

	/**	@var		Model_Newsletter_Reader			$modelReader */
	protected $modelReader;

	/**	@var		Model_Newsletter_Reader_Group	$modelReaderGroup */
	protected $modelReaderGroup;

	/**	@var		Model_Newsletter_Reader_Letter	$modelReaderLetter */
	protected $modelReaderLetter;

	/**	@var		Model_Newsletter_Template		$modelTemplate */
	protected $modelTemplate;

	public function addReader( $data )
	{
		if( !isset( $data['registeredAt'] ) )
			$data['registeredAt']	= time();
		return $this->modelReader->add( $data );
	}

	public function addReaderToGroup( $readerId, $groupId, $strict = TRUE )
	{
		$this->checkReaderId( $readerId, $strict );
		$this->checkGroupId( $groupId, $strict );
		$has	= $this->getGroupsOfReader( $readerId, array( 'newsletterGroupId' => $groupId ) );
		if( $has )
			return $has[0]->newsletterReaderGroupId;
		$data	= array(
			'newsletterReaderId'	=> $readerId,
			'newsletterGroupId'		=> $groupId,
			'createdAt'				=> time(),
		);
		return $this->modelReaderGroup->add( $data );
	}

	public function checkGroupId( $groupId, $throwException = FALSE )
	{
		if( $this->modelGroup->has( (int) $groupId ) )
			return TRUE;
		if( $throwException )
			throw new InvalidArgumentException( 'Invalid newsletter group ID '.$groupId );
		return FALSE;
	}

	/**
	 *	Indicates whether a given newsletter ID is valid.
	 *	@access		public
	 *	@param		integer		$newsletterId		ID of newsletter to check
	 *	@param		boolean		$throwException		Flag: throw exception if not existing, otherwise return FALSE (default: TRUE)
	 *	@return		boolean
	 *	@throws		InvalidArgumentException		if newsletter is not exising and $throwException is TRUE
	 */
	public function checkNewsletterId( $newsletterId, $throwException = FALSE )
	{
		if( $this->modelNewsletter->has( (int) $newsletterId ) )
			return TRUE;
		if( $throwException )
			throw new InvalidArgumentException( 'Invalid newsletter ID '.$newsletterId );
		return FALSE;
	}

	/**
	 *	Indicates whether a given newsletter reader letter ID is valid.
	 *	@access		public
	 *	@param		integer		$readerLetterId		ID of newsletter reader letter to check
	 *	@param		boolean		$throwException		Flag: throw exception if not existing, otherwise return FALSE (default: TRUE)
	 *	@return		boolean
	 *	@throws		InvalidArgumentException		if newsletter reader letter is not exising and $throwException is TRUE
	 */
	public function checkReaderLetterId( $readerLetterId, $throwException = FALSE )
	{
		if( $this->modelReaderLetter->has( (int) $readerLetterId ) )
			return TRUE;
		if( $throwException )
			throw new InvalidArgumentException( 'Invalid newsletter reader letter ID '.$readerLetterId );
		return FALSE;
	}

	/**
	 *	Indicates whether a given newsletter reader ID is valid.
	 *	@access		public
	 *	@param		integer		$readerId			ID of newsletter reader to check
	 *	@param		boolean		$throwException		Flag: throw exception if not existing, otherwise return FALSE (default: TRUE)
	 *	@return		boolean
	 *	@throws		InvalidArgumentException		if newsletter reader is not exising and $throwException is TRUE
	 */
	public function checkReaderId( $readerId, $throwException = FALSE )
	{
		if( $this->modelReader->has( (int) $readerId ) )
			return TRUE;
		if( $throwException )
			throw new InvalidArgumentException( 'Invalid newsletter reader ID '.$readerId );
		return FALSE;
	}

	/**
	 *	Indicates whether a given newsletter template ID is valid.
	 *	@access		public
	 *	@param		integer		$templateId			ID of newsletter template to check
	 *	@param		boolean		$throwException		Flag: throw exception if not existing, otherwise return FALSE (default: TRUE)
	 *	@return		boolean
	 *	@throws		InvalidArgumentException		if newsletter template is not exising and $throwException is TRUE
	 */
	public function checkTemplateId( $templateId, $throwException = FALSE )
	{
		if( $this->modelTemplate->has( (int) $templateId ) )
			return TRUE;
		if( $throwException )
			throw new InvalidArgumentException( 'Invalid newsletter template ID '.$templateId );
		return FALSE;
	}

	public function countGroupReaders( $groupId )
	{
		return $this->modelReaderGroup->countByIndex( 'newsletterGroupId', $groupId );
	}

	public function countNewsletters( $conditions = [] )
	{
		return $this->modelNewsletter->count( $conditions );
	}

	public function editNewsletter( $newsletterId, $data )
	{
		$this->checkNewsletterId( $newsletterId, TRUE );
		$data['modifiedAt']	= time();
		$this->modelNewsletter->edit( $newsletterId, $data, FALSE );
	}

	public function editReader( $readerId, $data, $strict = TRUE )
	{
		$this->checkReaderId( $readerId, $strict );
		$this->modelReader->edit( $readerId, $data );
	}

	public function getActiveReaderFromEmail( $email, $activeOnly = TRUE, $strict = TRUE )
	{
		$conditions	= array( 'email' => $email);
		if( $activeOnly )
			$conditions['status']	= '> 0';
		$readers	= $this->getReaders( $conditions );
		if( !$readers ){
			if( $strict )
				throw new RuntimeException( 'Invalid reader email' );
			return NULL;
		}
		return array_shift( $readers );
	}

	public function getGroup( $groupId, $strict = TRUE )
	{
		$this->checkGroupId( $groupId, $strict );
		return $this->modelGroup->get( $groupId );
	}

	public function getGroupReaders( $groupId )
	{
		$list		= [];
		$readers	= [];
		foreach( $this->modelReader->getAllByIndex( 'status', '> 0' ) as $reader )
			$readers[$reader->newsletterReaderId]	= $reader;
		$relations	= $this->modelReaderGroup->getAllByIndex( 'newsletterGroupId', $groupId );
		foreach( $relations as $relation )
			if( array_key_exists( $relation->newsletterReaderId, $readers ) )
				$list[]	= $readers[$relation->newsletterReaderId];
		return $list;
	}

	public function getGroups( $conditions = [], $orders = [] )
	{
		$list	= [];
		foreach( $this->modelGroup->getAll( $conditions, $orders ) as $group )
			$list[$group->newsletterGroupId]	= $group;
		return $list;
	}

	public function getGroupsOfReader( $readerId, $conditions = [], $orders = [] )
	{
		$this->checkReaderId( $readerId, TRUE );
		$list		= [];
		$groupIds	= [];
		$conditions['newsletterReaderId']	= $readerId;
		$relations	= $this->modelReaderGroup->getAll( $conditions );
		foreach( $relations as $relation )
			$groupIds[]	= $relation->newsletterGroupId;
		if( $groupIds ){
			$conditions	= array( 'newsletterGroupId' => $groupIds );
			foreach( $this->modelGroup->getAll( $conditions, $orders ) as $group )
				$list[$group->newsletterGroupId]	= $group;
		}
		return $list;
	}

	public function getLettersOfReader( $readerId, $conditions = [], $orders = [] )
	{
		$this->checkReaderId( $readerId, TRUE );
		$letters	= $this->modelReaderLetter->getAllByIndex( 'newsletterReaderId', $readerId );
		foreach( $letters as $letter )
			$letter->newsletter	= $this->getNewsletter( $letter->newsletterId );
		return $letters;
	}

	public function getNewsletter( $newsletterId, $strict = TRUE )
	{
		$this->checkNewsletterId( $newsletterId, $strict );
		return $this->modelNewsletter->get( $newsletterId );
	}

	public function getNewsletters( $conditions = [], $orders = [], $limits = [] )
	{
		$list	= [];
		foreach( $this->modelNewsletter->getAll( $conditions, $orders, $limits ) as $newsletter )
			$list[$newsletter->newsletterId]	= $newsletter;
		return $list;
	}

	public function getQueue( $queueId, $extended = FALSE )
	{
		$queue	= $this->modelQueue->get( $queueId );
		if( $extended ){
			$indices	= array( 'newsletterQueueId' => $queueId );
			$queue->countLetters	= $this->modelReaderLetter->count( $indices );
			$queue->countLettersByStatus	= [];
			for( $i=-3; $i<3; $i++ ){
				$queue->countLettersByStatus[$i]	= $this->modelReaderLetter->count(
					array_merge( $indices, array( 'status' => $i ) )
				);
			}
			$letters	= $this->modelReaderLetter->getAllByIndices( $indices );
			$queue->letters	= $letter;
/*			foreach( $letters as $letter ){
				$letter->reader	= $this->modelReader->getByIndex( $letter->newsletterReaderId );
			}*/
		}
		return $queue;
	}

	public function getQueues( $conditions = [], $orders = [], $limits = [] )
	{
		return $this->modelQueue->getAll( $conditions, $orders, $limits );
	}

	public function getQueuesOfNewsletter( $newsletterId, $extended = FALSE )
	{
		$queues	= $this->modelQueue->getAllByIndex( 'newsletterId', $newsletterId );
		foreach( $queues as $queue ){
			$indices	= array( 'newsletterQueueId' => $queue->newsletterQueueId );
			$queue->countLetters	= $this->modelReaderLetter->count( $indices );
			if( $queue->creatorId ){
				$modelUser		= new Model_User( $this->env );
				$queue->creator	= $modelUser->get( $queue->creatorId );
			}
			$queue->countLettersByStatus	= [];
			for( $i=-3; $i<3; $i++ ){
				$queue->countLettersByStatus[$i]	= $this->modelReaderLetter->count(
					array_merge( $indices, array( 'status' => $i ) )
				);
			}
/*			$letters	= $this->modelReaderLetter->getAllByIndex( 'newsletterQueueId', $queueId );
			foreach( $letters as $letter ){
				$letter->reader	= $this->getReader( $letter->newsletterReaderId );
			}
			$queue->letters	= $letter;*/
		}
		return $queues;
	}

	public function getReader( $readerId, $strict = TRUE )
	{
		$this->checkReaderId( $readerId, $strict );
		return $this->modelReader->get( $readerId );
	}

	public function getReaderLetter( $readerLetterId )
	{
		return $this->modelReaderLetter->get( $readerLetterId );
	}

	public function getReaderLetters( $conditions = [], $orders = [], $limits = [] )
	{
		$list	= [];
		foreach( $this->modelReaderLetter->getAll( $conditions, $orders, $limits ) as $letter ){
			$letter->reader		= $this->getReader( $letter->newsletterReaderId );
			$list[$letter->newsletterReaderLetterId]	= $letter;
		}
		return $list;
	}

	public function getReaders( $conditions = [], $orders = [], $limits = [] )
	{
		$list	= [];
		foreach( $this->modelReader->getAll( $conditions, $orders, $limits ) as $reader )
			$list[$reader->newsletterReaderId]	= $reader;
		return $list;
	}

	public function getReadersOfGroup( $groupId, $conditions = [], $orders = [] )
	{
		return $this->getReadersOfGroups( array( $groupId ), $conditions, $orders );
	}

	public function getReadersOfGroups( $groupIds, $conditions = [], $orders = [] )
	{
		$list		= [];
		$readerIds	= [];
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

	/**
	 *	Returns template data object for template ID.
	 *	@access		public
	 *	@param		integer		$templateId		ID of template to get data object for
	 *	@param		boolean		$strict			Strict mode: throw exception if checks fail
	 *	@return		object						Data object of template
	 *	@throws		InvalidArgumentException	if template ID is invalid
	 */
	public function getTemplate( $templateId, $strict = TRUE )
	{
		$this->checkTemplateId( $templateId, $strict );
		return $this->modelTemplate->get( $templateId );
	}

	public function getTemplates( $conditions = [], $orders = [] )
	{
		$list		= [];
		$modelTheme	= new Model_Newsletter_Theme( $this->env, 'contents/themes/' );
		foreach( $this->modelTemplate->getAll( $conditions, $orders ) as $template ){
			if( $template->themeId )
				$template->theme	= $modelTheme->getFromId( $template->themeId );
			$list[$template->newsletterTemplateId]	= $template;
		}
		return $list;
	}

	public function getTemplateAttributeList( $templateId, $columnKey, $strict = TRUE )
	{
		$this->checkTemplateId( $templateId, $strict );
		$template	= $this->modelTemplate->get( $templateId );
		$list		= [];

		if( !empty( $template->$columnKey ) && strlen( trim( $template->$columnKey ) ) ){
			$list	= substr( $template->$columnKey, 1, -1 );
			$list	= new ADT_List( explode( "|", $list ) );
			$list	= $list->getValues();
		}
		return $list;
	}

	public function removeReaderFromGroup( $readerId, $groupId, $strict = TRUE )
	{
		$this->checkReaderId( $readerId, $strict );
		$this->checkGroupId( $groupId, $strict );
		$indices	= array(
			'newsletterReaderId'	=> $readerId,
			'newsletterGroupId'		=> $groupId,
		);
		return $this->modelReaderGroup->removeByIndices( $indices );
	}

	public function setQueueStatus( $queueId, $status )
	{
		return $this->modelQueue->edit( $queueId, array(
			'status'		=> $status,
			'modifiedAt'	=> time(),
		) );
	}

	public function setReaderLetterStatus( $readerLetterId, $status )
	{
		$readerLetter	= $this->modelReaderLetter->get( $readerLetterId );
		if( !$readerLetter || $readerLetter->status >= $status )
			return;
		$data	= array(
			'status'		=> $status,
			'modifiedAt'	=> time(),
		);
		if( $status == Model_Newsletter_Reader_Letter::STATUS_SENT )
			$data['sentAt']	= time();
		if( $status == Model_Newsletter_Reader_Letter::STATUS_OPENED )
			$data['openedAt']	= time();
		return $this->modelReaderLetter->edit( $readerLetterId, $data );
	}

	public function setReaderLetterMailId( $readerLetterId, $mailId )
	{
		return $this->modelReaderLetter->edit( $readerLetterId, array(
			'mailId'	=> $mailId,
		) );
	}

	protected function __onInit()
	{
		$this->modelGroup			= new Model_Newsletter_Group( $this->env );
		$this->modelNewsletter		= new Model_Newsletter( $this->env );
		$this->modelReader			= new Model_Newsletter_Reader( $this->env );
		$this->modelReaderGroup		= new Model_Newsletter_Reader_Group( $this->env );
		$this->modelReaderLetter	= new Model_Newsletter_Reader_Letter( $this->env );
		$this->modelTemplate		= new Model_Newsletter_Template( $this->env );
		$this->modelQueue			= new Model_Newsletter_Queue( $this->env );
	}
}

