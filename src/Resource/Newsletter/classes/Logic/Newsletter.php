<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\ADT\Collection;
use CeusMedia\HydrogenFramework\Environment\Resource\Logic;

/**
 *	@todo	extend \CeusMedia\HydrogenFramework\Logic instead
 *	@todo	code doc
 */
class Logic_Newsletter extends Logic
{
	/**	@var		Model_Newsletter_Group			$modelGroup */
	protected Model_Newsletter_Group $modelGroup;

	/**	@var		Model_Newsletter				$modelNewsletter */
	protected Model_Newsletter $modelNewsletter;

	/**	@var		Model_Newsletter_Queue			$modelQueue */
	protected Model_Newsletter_Queue $modelQueue;

	/**	@var		Model_Newsletter_Reader			$modelReader */
	protected Model_Newsletter_Reader $modelReader;

	/**	@var		Model_Newsletter_Reader_Group	$modelReaderGroup */
	protected Model_Newsletter_Reader_Group $modelReaderGroup;

	/**	@var		Model_Newsletter_Reader_Letter	$modelReaderLetter */
	protected Model_Newsletter_Reader_Letter $modelReaderLetter;

	/**	@var		Model_Newsletter_Template		$modelTemplate */
	protected Model_Newsletter_Template $modelTemplate;

	public function addReader( array $data ): string
	{
		if( !isset( $data['registeredAt'] ) )
			$data['registeredAt']	= time();
		return $this->modelReader->add( $data );
	}

	public function addReaderToGroup( int|string $readerId, int|string $groupId, bool $strict = TRUE ): string
	{
		$this->checkReaderId( $readerId, $strict );
		$this->checkGroupId( $groupId, $strict );
		$has	= $this->getGroupsOfReader( $readerId, ['newsletterGroupId' => $groupId] );
		if( $has )
			return $has[0]->newsletterReaderGroupId;
		$data	= [
			'newsletterReaderId'	=> $readerId,
			'newsletterGroupId'		=> $groupId,
			'createdAt'				=> time(),
		];
		return $this->modelReaderGroup->add( $data );
	}

	public function checkGroupId( int|string $groupId, bool $throwException = FALSE ): bool
	{
		if( $this->modelGroup->has( $groupId ) )
			return TRUE;
		if( $throwException )
			throw new InvalidArgumentException( 'Invalid newsletter group ID '.$groupId );
		return FALSE;
	}

	/**
	 *	Indicates whether a given newsletter ID is valid.
	 *	@access		public
	 *	@param		int|string		$newsletterId		ID of newsletter to check
	 *	@param		boolean			$throwException		Flag: throw exception if not existing, otherwise return FALSE (default: TRUE)
	 *	@return		boolean
	 *	@throws		InvalidArgumentException			if newsletter is not exising and $throwException is TRUE
	 */
	public function checkNewsletterId( int|string $newsletterId, bool $throwException = FALSE ): bool
	{
		if( $this->modelNewsletter->has( $newsletterId ) )
			return TRUE;
		if( $throwException )
			throw new InvalidArgumentException( 'Invalid newsletter ID '.$newsletterId );
		return FALSE;
	}

	/**
	 *	Indicates whether a given newsletter reader letter ID is valid.
	 *	@access		public
	 *	@param		int|string		$readerLetterId		ID of newsletter reader letter to check
	 *	@param		boolean		$throwException		Flag: throw exception if not existing, otherwise return FALSE (default: TRUE)
	 *	@return		boolean
	 *	@throws		InvalidArgumentException		if newsletter reader letter is not exising and $throwException is TRUE
	 */
	public function checkReaderLetterId( int|string $readerLetterId, bool $throwException = FALSE ): bool
	{
		if( $this->modelReaderLetter->has( $readerLetterId ) )
			return TRUE;
		if( $throwException )
			throw new InvalidArgumentException( 'Invalid newsletter reader letter ID '.$readerLetterId );
		return FALSE;
	}

	/**
	 *	Indicates whether a given newsletter reader ID is valid.
	 *	@access		public
	 *	@param		int|string		$readerId			ID of newsletter reader to check
	 *	@param		boolean			$throwException		Flag: throw exception if not existing, otherwise return FALSE (default: TRUE)
	 *	@return		boolean
	 *	@throws		InvalidArgumentException			if newsletter reader is not exising and $throwException is TRUE
	 */
	public function checkReaderId( int|string $readerId, bool $throwException = FALSE ): bool
	{
		if( $this->modelReader->has( $readerId ) )
			return TRUE;
		if( $throwException )
			throw new InvalidArgumentException( 'Invalid newsletter reader ID '.$readerId );
		return FALSE;
	}

	/**
	 *	Indicates whether a given newsletter template ID is valid.
	 *	@access		public
	 *	@param		int|string		$templateId			ID of newsletter template to check
	 *	@param		boolean			$throwException		Flag: throw exception if not existing, otherwise return FALSE (default: TRUE)
	 *	@return		boolean
	 *	@throws		InvalidArgumentException			if newsletter template is not exising and $throwException is TRUE
	 */
	public function checkTemplateId( int|string $templateId, bool $throwException = FALSE ): bool
	{
		if( $this->modelTemplate->has( $templateId ) )
			return TRUE;
		if( $throwException )
			throw new InvalidArgumentException( 'Invalid newsletter template ID '.$templateId );
		return FALSE;
	}

	public function countGroupReaders( int|string $groupId ): int
	{
		return $this->modelReaderGroup->countByIndex( 'newsletterGroupId', $groupId );
	}

	public function countNewsletters( array $conditions = [] ): int
	{
		return $this->modelNewsletter->count( $conditions );
	}

	public function editNewsletter( int|string $newsletterId, array $data ): int
	{
		$this->checkNewsletterId( $newsletterId, TRUE );
		$data['modifiedAt']	= time();
		return $this->modelNewsletter->edit( $newsletterId, $data, FALSE );
	}

	public function editReader( int|string $readerId, array $data, bool $strict = TRUE ): int
	{
		$this->checkReaderId( $readerId, $strict );
		return $this->modelReader->edit( $readerId, $data );
	}

	public function getActiveReaderFromEmail( string $email, bool $activeOnly = TRUE, bool $strict = TRUE )
	{
		$conditions	= ['email' => $email];
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

	public function getGroup( int|string $groupId, bool $strict = TRUE ): object
	{
		$this->checkGroupId( $groupId, $strict );
		return $this->modelGroup->get( $groupId );
	}

	public function getGroupReaders( int|string $groupId ): array
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

	public function getGroups( array $conditions = [], array $orders = [] ): array
	{
		$list	= [];
		foreach( $this->modelGroup->getAll( $conditions, $orders ) as $group )
			$list[$group->newsletterGroupId]	= $group;
		return $list;
	}

	public function getGroupsOfReader( int|string $readerId, array $conditions = [], array $orders = [] ): array
	{
		$this->checkReaderId( $readerId, TRUE );
		$list		= [];
		$groupIds	= [];
		$conditions['newsletterReaderId']	= $readerId;
		$relations	= $this->modelReaderGroup->getAll( $conditions );
		foreach( $relations as $relation )
			$groupIds[]	= $relation->newsletterGroupId;
		if( $groupIds ){
			$conditions	= ['newsletterGroupId' => $groupIds];
			foreach( $this->modelGroup->getAll( $conditions, $orders ) as $group )
				$list[$group->newsletterGroupId]	= $group;
		}
		return $list;
	}

	public function getLettersOfReader( int|string $readerId, array $conditions = [], array $orders = [] ): array
	{
		$this->checkReaderId( $readerId, TRUE );
		$letters	= $this->modelReaderLetter->getAllByIndex( 'newsletterReaderId', $readerId );
		foreach( $letters as $letter )
			$letter->newsletter	= $this->getNewsletter( $letter->newsletterId );
		return $letters;
	}

	public function getNewsletter( int|string $newsletterId, bool $strict = TRUE ): ?object
	{
		if( $this->checkNewsletterId( $newsletterId, $strict ) )
			return $this->modelNewsletter->get( $newsletterId );
		return NULL;
	}

	public function getQueue( int|string $queueId, $extended = FALSE ): object
	{
		$queue	= $this->modelQueue->get( $queueId );
		if( $extended ){
			$indices	= ['newsletterQueueId' => $queueId];
			$queue->countLetters	= $this->modelReaderLetter->count( $indices );
			$queue->countLettersByStatus	= [];
			for( $i=-3; $i<3; $i++ ){
				$queue->countLettersByStatus[$i]	= $this->modelReaderLetter->count(
					array_merge( $indices, ['status' => $i] )
				);
			}
			$letters	= $this->modelReaderLetter->getAllByIndices( $indices );
			$queue->letters	= $letters;
/*			foreach( $letters as $letter ){
				$letter->reader	= $this->modelReader->getByIndex( $letter->newsletterReaderId );
			}*/
		}
		return $queue;
	}

	public function getNewsletters( array $conditions = [], array $orders = [], array $limits = [] ): array
	{
		$list	= [];
		foreach( $this->modelNewsletter->getAll( $conditions, $orders, $limits ) as $newsletter )
			$list[$newsletter->newsletterId]	= $newsletter;
		return $list;
	}

	public function getQueues( array $conditions = [], array $orders = [], array $limits = [] ): array
	{
		return $this->modelQueue->getAll( $conditions, $orders, $limits );
	}

	public function getQueuesOfNewsletter( int|string $newsletterId, bool $extended = FALSE ): array
	{
		$queues	= $this->modelQueue->getAllByIndex( 'newsletterId', $newsletterId );
		foreach( $queues as $queue ){
			$indices	= ['newsletterQueueId' => $queue->newsletterQueueId];
			$queue->countLetters	= $this->modelReaderLetter->count( $indices );
			if( $queue->creatorId ){
				$modelUser		= new Model_User( $this->env );
				$queue->creator	= $modelUser->get( $queue->creatorId );
			}
			$queue->countLettersByStatus	= [];
			for( $i=-3; $i<3; $i++ ){
				$queue->countLettersByStatus[$i]	= $this->modelReaderLetter->count(
					array_merge( $indices, ['status' => $i] )
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

	public function getReader( int|string $readerId, bool $strict = TRUE ): ?object
	{
		if( $this->checkReaderId( $readerId, $strict ) )
			return $this->modelReader->get( $readerId );
		return NULL;
	}

	public function getReaderLetter( int|string $readerLetterId ): object
	{
		return $this->modelReaderLetter->get( $readerLetterId );
	}

	public function getReaderLetters( array $conditions = [], array $orders = [], array $limits = [] ): array
	{
		$list	= [];
		foreach( $this->modelReaderLetter->getAll( $conditions, $orders, $limits ) as $letter ){
			$letter->reader		= $this->getReader( $letter->newsletterReaderId );
			$list[$letter->newsletterReaderLetterId]	= $letter;
		}
		return $list;
	}

	public function getReaders( array $conditions = [], array $orders = [], array $limits = [] ): array
	{
		$list	= [];
		foreach( $this->modelReader->getAll( $conditions, $orders, $limits ) as $reader )
			$list[$reader->newsletterReaderId]	= $reader;
		return $list;
	}

	public function getReadersOfGroup( int|string $groupId, array $conditions = [], array $orders = [] ): array
	{
		return $this->getReadersOfGroups( [$groupId], $conditions, $orders );
	}

	public function getReadersOfGroups( array $groupIds, array $conditions = [], array $orders = [] ): array
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
	 *	@param		int|string		$templateId		ID of template to get data object for
	 *	@param		boolean			$strict			Strict mode: throw exception if checks fail
	 *	@return		object							Data object of template
	 *	@throws		InvalidArgumentException		if template ID is invalid
	 */
	public function getTemplate( int|string $templateId, bool $strict = TRUE ): object
	{
		$this->checkTemplateId( $templateId, $strict );
		return $this->modelTemplate->get( $templateId );
	}

	public function getTemplates( array $conditions = [], array $orders = [] ): array
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

	public function getTemplateAttributeList( int|string $templateId, string $columnKey, bool $strict = TRUE ): array
	{
		$this->checkTemplateId( $templateId, $strict );
		$template	= $this->modelTemplate->get( $templateId );
		$list		= [];

		if( !empty( $template->$columnKey ) && strlen( trim( $template->$columnKey ) ) ){
			$list	= substr( $template->$columnKey, 1, -1 );
			$list	= new Collection( explode( "|", $list ) );
			$list	= $list->getValues();
		}
		return $list;
	}

	public function removeReaderFromGroup( int|string $readerId, int|string $groupId, bool $strict = TRUE ): int
	{
		$this->checkReaderId( $readerId, $strict );
		$this->checkGroupId( $groupId, $strict );
		$indices	= [
			'newsletterReaderId'	=> $readerId,
			'newsletterGroupId'		=> $groupId,
		];
		return $this->modelReaderGroup->removeByIndices( $indices );
	}

	public function setQueueStatus( int|string $queueId, int $status ): int
	{
		return $this->modelQueue->edit( $queueId, [
			'status'		=> $status,
			'modifiedAt'	=> time(),
		] );
	}

	public function setReaderLetterStatus( int|string $readerLetterId, int $status ): int
	{
		$readerLetter	= $this->modelReaderLetter->get( $readerLetterId );
		if( !$readerLetter || $readerLetter->status >= $status )
			return 0;
		$data	= [
			'status'		=> $status,
			'modifiedAt'	=> time(),
		];
		if( $status == Model_Newsletter_Reader_Letter::STATUS_SENT )
			$data['sentAt']	= time();
		if( $status == Model_Newsletter_Reader_Letter::STATUS_OPENED )
			$data['openedAt']	= time();
		return $this->modelReaderLetter->edit( $readerLetterId, $data );
	}

	public function setReaderLetterMailId( int|string $readerLetterId, int|string $mailId ): int
	{
		return $this->modelReaderLetter->edit( $readerLetterId, ['mailId' => $mailId] );
	}

	protected function __onInit(): void
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

