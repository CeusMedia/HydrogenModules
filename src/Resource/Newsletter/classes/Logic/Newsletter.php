<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\ADT\Collection;
use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Logic\Shared as SharedLogic;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Definition as ModuleDefinition;

class Logic_Newsletter extends SharedLogic
{
	public static string $defaultPath				= 'contents/newsletter-themes/';

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

	protected Dictionary $moduleConfig;

	protected bool $useUserGroupRelations		= FALSE;

	/**
	 *	@param		Entity_Newsletter_Reader|array		$data
	 *	@return		string
	 */
	public function addReader( Entity_Newsletter_Reader|array $data ): string
	{
		if( is_object( $data ) )
			if( !isset( $data->registeredAt ) )
				$data->registeredAt	= time();
		if( is_array( $data ) )
			if( !isset( $data['registeredAt'] ) )
				$data['registeredAt']	= time();
		return $this->modelReader->add( $data );
	}

	/**
	 *	@param		Entity_Newsletter_Reader|int|string		$reader
	 *	@param		int|string		$groupId
	 *	@param		bool			$strict
	 *	@return		string
	 *	@throws		InvalidArgumentException			if newsletter reader is not exising and strict mode
	 *	@throws		ReflectionException
	 */
	public function addReaderToGroup( Entity_Newsletter_Reader|int|string $reader, int|string $groupId, bool $strict = TRUE ): string
	{
		/** @var int|string $readerId */
		$readerId	= is_object( $reader ) ? $reader->newsletterReaderId : $reader;

		$this->checkReaderId( $readerId, $strict );
		$this->checkGroupId( $groupId, $strict );

		$readerGroups	= $this->getGroupsOfReader( $reader, ['newsletterGroupId' => $groupId], [], FALSE );
		if( [] !== $readerGroups )
			return $readerGroups[0]->newsletterReaderGroupId;

		$data	= [
			'newsletterReaderId'	=> $readerId,
			'newsletterGroupId'		=> $groupId,
			'createdAt'				=> time(),
		];
		return $this->modelReaderGroup->add( $data );
	}

	/**
	 *	@param		int|string		$groupId
	 *	@param		bool			$throwException
	 *	@return		bool
	 *	@throws		InvalidArgumentException		if newsletter group ID is invalid
	 *	@throws		ReflectionException
	 */
	public function checkGroupId( int|string $groupId, bool $throwException = FALSE ): bool
	{
		if( $this->modelGroup->has( $groupId ) ){
			if( !$this->useUserGroupRelations )
				return TRUE;
			if( $this->hasGroupAccessToNewsletterGroup( $groupId ) )
				return TRUE;
		}
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
	 *	@param		boolean			$throwException		Flag: throw exception if not existing, otherwise return FALSE (default: TRUE)
	 *	@return		boolean
	 *	@throws		InvalidArgumentException			if newsletter reader letter is not exising and $throwException is TRUE
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

	/**
	 *	@param		Entity_Newsletter_Group|int|string 		$group
	 *	@return		int
	 */
	public function countGroupReaders( Entity_Newsletter_Group|int|string $group ): int
	{
		$groupId	= is_object( $group ) ? $group->newsletterGroupId : $group;
		return $this->modelReaderGroup->countByIndex( 'newsletterGroupId', $groupId );
	}

	/**
	 *	@param		array		$conditions
	 *	@return		int
	 */
	public function countNewsletters( array $conditions = [] ): int
	{
		return $this->modelNewsletter->count( $conditions );
	}

	/**
	 *	Count reader letters by conditions, like: reader letters sent by a specific newsletter.
	 *	@param		array		$conditions
	 *	@return		int
	 */
	public function countReaderLetters( array $conditions = [] ): int
	{
		return $this->modelReaderLetter->count( $conditions );
	}

	/**
	 *	@param		Entity_Newsletter|int|string	$newsletter
	 *	@param		array			$data
	 *	@return		int
	 */
	public function editNewsletter( Entity_Newsletter|int|string $newsletter, array $data ): int
	{
		$newsletterId	= is_object( $newsletter ) ? $newsletter->newsletterId : $newsletter;
		$this->checkNewsletterId( $newsletterId, TRUE );
		$data['modifiedAt']	= time();
		return $this->modelNewsletter->edit( $newsletterId, $data, FALSE );
	}

	/**
	 *	@param		Entity_Newsletter_Reader|int|string		$reader
	 *	@param		array 			$data
	 *	@param		bool			$strict
	 *	@return		int
	 */
	public function editReader( Entity_Newsletter_Reader|int|string $reader, array $data, bool $strict = TRUE ): int
	{
		$readerId	= is_object( $reader ) ? $reader->newsletterReaderId : $reader;
		$this->checkReaderId( $readerId, $strict );
		return $this->modelReader->edit( $readerId, $data );
	}

	/**
	 *	@param		string		$email
	 *	@param		bool		$activeOnly
	 *	@param		bool		$strict
	 *	@return		object|NULL
	 */
	public function getActiveReaderFromEmail( string $email, bool $activeOnly = TRUE, bool $strict = TRUE ): ?object
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

	/**
	 *	@param		int|string		$groupId
	 *	@param		bool			$strict
	 *	@return		object|NULL
	 *	@throws		ReflectionException
	 */
	public function getGroup( int|string $groupId, bool $strict = TRUE ): ?object
	{
		$this->checkGroupId( $groupId, $strict );
		return $this->modelGroup->get( $groupId );
	}

	/**
	 *	@param		Entity_Newsletter_Group|int|string		$group
	 *	@return		array<Entity_Newsletter_Reader>
	 * @todo improve performance on higher scale
	 */
	public function getGroupReaders( Entity_Newsletter_Group|int|string $group ): array
	{
		$groupId	= is_object( $group ) ? $group->newsletterGroupId : $group;
		$list		= [];
		$readers	= [];
		/** @var Entity_Newsletter_Reader $reader */
		foreach( $this->modelReader->getAllByIndex( 'status', Model_Newsletter_Reader::STATUS_CONFIRMED ) as $reader )
			$readers[$reader->newsletterReaderId]	= $reader;
		$relations	= $this->modelReaderGroup->getAllByIndex( 'newsletterGroupId', $groupId );
		foreach( $relations as $relation )
			if( array_key_exists( $relation->newsletterReaderId, $readers ) )
				$list[]	= $readers[$relation->newsletterReaderId];
		return $list;
	}

	/**
	 *	@param		array		$conditions
	 *	@param		array		$orders
	 *	@param		array		$limits
	 *	@return		array<int|string,Entity_Newsletter_Group>
	 *	@throws		ReflectionException
	 */
	public function getGroups( array $conditions = [], array $orders = [], array $limits = [] ): array
	{
		$list	= [];

		if( $this->useUserGroupRelations ){
			if( !Logic_Authentication::getInstance( $this->env )->hasFullAccess() ){
				$logic		= Logic_GroupRelation::getInstance( $this->env );
				$entityIds	= $logic->getModuleEntityIdsFromCurrentGroups( 'Resource_Newsletter.Group' ) ?: [0];
				if( isset( $conditions['newsletterGroupId'] ) )
					$entityIds	= array_intersect( $conditions['newsletterGroupId'], $entityIds );
				$conditions['newsletterGroupId']	= $entityIds;
			}
		}

		/** @var Entity_Newsletter_Group $group */
		foreach( $this->modelGroup->getAll( $conditions, $orders, $limits ) as $group )
			$list[$group->newsletterGroupId]	= $group;
		return $list;
	}

	/**
	 *	@param		Entity_Newsletter_Reader|int|string		$reader
	 *	@param		array			$conditions
	 *	@param		array			$orders
	 *	@param		bool			$useRelations			Flag: use group relations
	 *	@return		array
	 *	@throws		ReflectionException
	 */
	public function getGroupsOfReader( Entity_Newsletter_Reader|int|string $reader, array $conditions = [], array $orders = [], bool $useRelations = TRUE ): array
	{
		/** @var int|string $readerId */
		$readerId	= is_object( $reader ) ? $reader->newsletterReaderId : $reader;

		$this->checkReaderId( $readerId, TRUE );

		$conditions['newsletterReaderId']	= $readerId;
		if( $useRelations && $this->useUserGroupRelations )
			if( !Logic_Authentication::getInstance( $this->env )->hasFullAccess() )
				$conditions['newsletterGroupId']	= Logic_GroupRelation::getInstance( $this->env )
					->getModuleEntityIdsFromCurrentGroups( 'Resource_Newsletter.Group' ) ?: [0];

		$groupIds	= [];
		foreach( $this->modelReaderGroup->getAllByIndices( $conditions ) as $relation )
			$groupIds[]	= $relation->newsletterGroupId;

		$list		= [];
		if( $groupIds )
			foreach( $this->getGroups( ['newsletterGroupId' => $groupIds], $orders ) as $group )
				$list[$group->newsletterGroupId]	= $group;
		return $list;
	}

	/**
	 *	@param		Entity_Newsletter_Reader|int|string		$reader
	 *	@param		array			$conditions
	 *	@param		array			$orders
	 *	@return		array
	 */
	public function getLettersOfReader( Entity_Newsletter_Reader|int|string $reader, array $conditions = [], array $orders = [] ): array
	{
		$readerId	= is_object( $reader ) ? $reader->newsletterReaderId : $reader;
		try{
			$this->checkReaderId( $readerId, TRUE );
			$indices	= array_merge( $conditions, ['newsletterReaderId' => $readerId] );
			if( $this->useUserGroupRelations && !Logic_Authentication::getInstance( $this->env )->hasFullAccess() ){
//				newsletterReaderLetterId <- newsletterId <- groupId <- session
				$newsletterIds	= Logic_GroupRelation::getInstance( $this->env )
					->getModuleEntityIdsFromCurrentGroups( 'Resource_Newsletter' ) ?: [0];
				$indices	= array_merge( $conditions, ['newsletterId' => $newsletterIds] );
			}
			$letters	= $this->modelReaderLetter->getAllByIndices( $indices, $orders  );
			foreach( $letters as $letter )
				$letter->newsletter	= $this->getNewsletter( $letter->newsletterId );
			return $letters;
		}
		catch( Throwable $e ){
			$this->env->getLog()->logException( $e );
			return [];
		}
	}

	/**
	 *	@param		int|string		$newsletterId
	 *	@param		bool			$strict
	 *	@return		Entity_Newsletter|NULL
	 *	@throws		InvalidArgumentException			if newsletter is not exising and strict mode
	 */
	public function getNewsletter( int|string $newsletterId, bool $strict = TRUE ): ?object
	{
		if( $this->checkNewsletterId( $newsletterId, $strict ) )
			return $this->modelNewsletter->get( $newsletterId );
		return NULL;
	}

	/**
	 *	@param		array		$conditions
	 *	@param		array		$orders
	 *	@param		array		$limits
	 *	@return		array
	 */
	public function getNewsletters( array $conditions = [], array $orders = [], array $limits = [] ): array
	{
		$list	= [];
		foreach( $this->modelNewsletter->getAll( $conditions, $orders, $limits ) as $newsletter )
			if( $this->hasGroupAccessToNewsletter( $newsletter ) )
				$list[$newsletter->newsletterId]	= $newsletter;
		return $list;
	}

	/**
	 *	Returns absolute path to newsletter themes.
	 *	Configured by 'path.themes'.
	 *	Will prefix with environment base uri, if not configured absolutely.
	 *	Supports frontend resource.
	 *	Hint: This method is a core method.
	 *  - Used for every instance of Model_Newsletter_Template.
	 *	- Allows absolute path definition for custom setups.
	 *	- Appends trailing slash if needed.
	 *
	 *	@return		string
	 *	@throws		ReflectionException
	 */
	public function getNewsletterThemesPath(): string
	{
		$configKey			= 'path.themes';
		$moduleConfigKey	= 'module.resource_newsletter.'.$configKey;

		/** @var ?ModuleDefinition $module */
		$module	= $this->env->getModules()->get( 'Resource_Frontend', TRUE, FALSE );
		if( NULL !== $module && ( './' !== $module->getConfigAsDictionary()->get( 'path' ) ) ){
			$frontend	= Logic_Frontend::getInstance( $this->env );
			$path		= $frontend->getModuleConfigValue( 'Resource_Newsletter', $configKey );
			$path		??= self::$defaultPath;
			$path		.= !str_ends_with( $path, '/' ) ? '/' : '';									//  ensure trailing slash
			return !str_starts_with( $path, '/' ) ? $frontend->getUri().$path : $path;				//  prepend with remote app environment base uri if path is releative
		}

		$path	= $this->env->getConfig()->get( $moduleConfigKey, self::$defaultPath );				//  get path from module config or default path
		$path	.= !str_ends_with( $path, '/' ) ? '/' : '';											//  ensure trailing slash
		return !str_starts_with( $path, '/' ) ? $this->env->uri.$path : $path;						//  prepend with app environment base uri if path is releative
	}

	/**
	 *	@param		int|string		$queueId
	 *	@param		bool			$extended
	 *	@return		object|NULL
	 */
	public function getQueue( int|string $queueId, bool $extended = FALSE ): ?object
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

	/**
	 *	@param		array		$conditions
	 *	@param		array		$orders
	 *	@param		array		$limits
	 *	@return		array
	 */
	public function getQueues( array $conditions = [], array $orders = [], array $limits = [] ): array
	{
		return $this->modelQueue->getAll( $conditions, $orders, $limits );
	}

	/**
	 *	@param		int|string		$newsletterId
	 *	@param		bool			$extended
	 *	@return		array
	 *	@throws		ReflectionException
	 */
	public function getQueuesOfNewsletter( int|string $newsletterId, bool $extended = FALSE ): array
	{
		$queues	= $this->modelQueue->getAllByIndex( 'newsletterId', $newsletterId );
		foreach( $queues as $queue ){
			$indices	= ['newsletterQueueId' => $queue->newsletterQueueId];
			$queue->countLetters	= $this->modelReaderLetter->count( $indices );
			if( $queue->creatorId )
				$queue->creator	= Logic_User::getInstance( $this->env )->getUser( $queue->creatorId );

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

	/**
	 *	@param		int|string		$readerId
	 *	@param		bool			$strict
	 *	@return		Entity_Newsletter_Reader|NULL
	 *	@throws		InvalidArgumentException			if newsletter reader is not exising and $throwException is TRUE
	 */
	public function getReader( int|string $readerId, bool $strict = TRUE ): ?Entity_Newsletter_Reader
	{
		if( $this->checkReaderId( $readerId, $strict ) ){
			/** @var Entity_Newsletter_Reader $reader */
			$reader	= $this->modelReader->get( $readerId );
			return $reader;
		}
		return NULL;
	}

	/**
	 *	@param		int|string		$readerLetterId
	 *	@return		Entity_Newsletter_Reader_Letter|NULL
	 */
	public function getReaderLetter( int|string $readerLetterId ): ?object
	{
		return $this->modelReaderLetter->get( $readerLetterId );
	}

	/**
	 *	@param		array		$conditions
	 *	@param		array		$orders
	 *	@param		array		$limits
	 *	@return		array
	 *	@throws		InvalidArgumentException			if newsletter reader is not exising and $throwException is TRUE
	 */
	public function getReaderLetters( array $conditions = [], array $orders = [], array $limits = [] ): array
	{
		$list	= [];
		/** @var array<Entity_Newsletter_Reader_Letter> $letters */
		$letters	= $this->modelReaderLetter->getAll( $conditions, $orders, $limits );
		foreach( $letters as $letter ){
			$letter->reader		= $this->getReader( $letter->newsletterReaderId );
			$list[$letter->newsletterReaderLetterId]	= $letter;
		}
		return $list;
	}

	/**
	 *	@param		array		$conditions
	 *	@param		array		$orders
	 *	@param		array		$limits
	 *	@return		array<int|string,Entity_Newsletter_Reader>
	 */
	public function getReaders( array $conditions = [], array $orders = [], array $limits = [] ): array
	{
		$list	= [];
		/** @var Entity_Newsletter_Reader $reader */
		foreach( $this->modelReader->getAll( $conditions, $orders, $limits ) as $reader )
			$list[$reader->newsletterReaderId]	= $reader;
		return $list;
	}

	/**
	 *	@param		Entity_Newsletter_Group|int|string		$group
	 *	@param		array			$conditions
	 *	@param		array			$orders
	 *	@return		array
	 */
	public function getReadersOfGroup( Entity_Newsletter_Group|int|string $group, array $conditions = [], array $orders = [] ): array
	{
		$groupId	= is_object( $group ) ? $group->newsletterGroupId : $group;
		return $this->getReadersOfGroups( [$groupId], $conditions, $orders );
	}

	/**
	 *	@param		array<Entity_Newsletter_Group|int|string>	$groups		List of group objects or IDs
	 *	@param		array		$conditions
	 *	@param		array		$orders
	 *	@return		array
	 */
	public function getReadersOfGroups( array $groups, array $conditions = [], array $orders = [] ): array
	{
		$list		= [];
		$readerIds	= [];
		foreach( $groups as $group ){
			$groupId	= is_object( $group ) ? $group->newsletterGroupId : $group;
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
	 *	@return		?Entity_Newsletter_Template		Data object of template
	 *	@throws		InvalidArgumentException		if newsletter template is not exising and strict mode
	 */
	public function getTemplate( int|string $templateId, bool $strict = TRUE ): ?Entity_Newsletter_Template
	{
		if( !$this->checkTemplateId( $templateId, $strict ) )
			return NULL;
		/** @var Entity_Newsletter_Template $template */
		$template	= $this->modelTemplate->get( $templateId );
		return $template;
	}

	/**
	 *	@param		array		$conditions
	 *	@param		array		$orders
	 *	@return		array
	 *	@throws		ReflectionException
	 */
	public function getTemplates( array $conditions = [], array $orders = [] ): array
	{
		$list	= [];
		$modelTheme	= new Model_Newsletter_Theme( $this->env, $this->getNewsletterThemesPath() );
		foreach( $this->modelTemplate->getAll( $conditions, $orders ) as $template ){
			if( $template->themeId )
				$template->theme	= $modelTheme->getFromId( $template->themeId );
			$list[$template->newsletterTemplateId]	= $template;
		}
		return $list;
	}

	/**
	 *	@param		int|string		$templateId
	 *	@param		string			$columnKey
	 *	@param		bool			$strict
	 *	@return		array
	 *	@throws		InvalidArgumentException		if newsletter template is not exising and strict mode
	 */
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

	/**
	 *	@param		Entity_Newsletter|int|string		$newsletter
	 *	@return		bool
	 *	@throws		ReflectionException
	 */
	public function hasGroupAccessToNewsletter( Entity_Newsletter|int|string $newsletter ): bool
	{
		$newsletterId	= is_object( $newsletter ) ? $newsletter->newsletterId : $newsletter;
		$moduleConfig	= $this->env->getConfig()->getAll( 'module.work_newsletter.', TRUE );
		if( !$moduleConfig->get( 'useUserGroupRelations', FALSE ) )
			return TRUE;

		if( Logic_Authentication::getInstance( $this->env )->hasFullAccess() )
			return TRUE;

		return in_array( $newsletterId, Logic_GroupRelation::getInstance( $this->env )
			->getModuleEntityIdsFromCurrentGroups( 'Resource_Newsletter' ) );
	}

	/**
	 *	@param		Entity_Newsletter_Group|int|string		$newsletterGroup
	 *	@return		bool
	 *	@throws		ReflectionException
	 */
	public function hasGroupAccessToNewsletterGroup( Entity_Newsletter_Group|int|string $newsletterGroup ): bool
	{
		$groupId		= is_object( $newsletterGroup ) ? $newsletterGroup->newsletterGroupId : $newsletterGroup;
		$moduleConfig	= $this->env->getConfig()->getAll( 'module.work_newsletter.', TRUE );
		if( !$moduleConfig->get( 'useUserGroupRelations', FALSE ) )
			return TRUE;

		if( Logic_Authentication::getInstance( $this->env )->hasFullAccess() )
			return TRUE;

		return in_array( $groupId, Logic_GroupRelation::getInstance( $this->env )
			->getModuleEntityIdsFromCurrentGroups( 'Resource_Newsletter.Group' ) );
	}

	/**
	 *	@param		Entity_Newsletter_Reader|int|string		$reader
	 *	@param		Entity_Newsletter_Group|int|string		$group
	 *	@param		bool			$strict
	 *	@return		int
	 */
	public function removeReaderFromGroup( Entity_Newsletter_Reader|int|string $reader, Entity_Newsletter_Group|int|string $group, bool $strict = TRUE ): int
	{
		$readerId	= is_object( $reader ) ? $reader->newsletterReaderId : $reader;
		$groupId	= is_object( $group ) ? $group->newsletterGroupId : $group;
		try{
			$this->checkReaderId( $readerId, $strict );
			$this->checkGroupId( $groupId, $strict );
			$indices	= [
				'newsletterReaderId'	=> $readerId,
				'newsletterGroupId'		=> $groupId,
			];
			return $this->modelReaderGroup->removeByIndices( $indices );
		}
		catch( Throwable $e ){
			$this->env->getLog()->logException( $e );
			return 0;
		}
	}

	/**
	 *	@param		Entity_Newsletter_Queue|int|string		$queue
	 *	@param		int				$status
	 *	@return		int
	 */
	public function setQueueStatus( Entity_Newsletter_Queue|int|string $queue, int $status ): int
	{
		$queueId	= is_object( $queue ) ? $queue->newsletterQueueId : $queue;
		return $this->modelQueue->edit( $queueId, [
			'status'		=> $status,
			'modifiedAt'	=> time(),
		] );
	}

	/**
	 *	@param		Entity_Newsletter_Reader_Letter|int|string		$readerLetter
	 *	@param		int				$status
	 *	@return		int
	 */
	public function setReaderLetterStatus( Entity_Newsletter_Reader_Letter|int|string $readerLetter, int $status ): int
	{
		$readerLetterId	= is_object( $readerLetter ) ? $readerLetter->newsletterReaderId : $readerLetter;
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

	/**
	 *	@param		Entity_Newsletter_Reader_Letter|int|string		$readerLetter
	 *	@param		int|string		$mailId
	 *	@return		int
	 */
	public function setReaderLetterMailId( Entity_Newsletter_Reader_Letter|int|string $readerLetter, int|string $mailId ): int
	{
		$readerLetterId	= is_object( $readerLetter ) ? $readerLetter->newsletterReaderLetterId : $readerLetter;
		return $this->modelReaderLetter->edit( $readerLetterId, ['mailId' => $mailId] );
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->modelGroup			= new Model_Newsletter_Group( $this->env );
		$this->modelNewsletter		= new Model_Newsletter( $this->env );
		$this->modelReader			= new Model_Newsletter_Reader( $this->env );
		$this->modelReaderGroup		= new Model_Newsletter_Reader_Group( $this->env );
		$this->modelReaderLetter	= new Model_Newsletter_Reader_Letter( $this->env );
		$this->modelTemplate		= new Model_Newsletter_Template( $this->env );
		$this->modelQueue			= new Model_Newsletter_Queue( $this->env );

		$this->moduleConfig				= $this->env->getConfig()->getAll( 'module.work_newsletter.', TRUE );
		$this->useUserGroupRelations	= $this->moduleConfig->get( 'useUserGroupRelations', FALSE );
	}
}

