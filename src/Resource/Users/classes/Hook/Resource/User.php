<?php

use CeusMedia\HydrogenFramework\Hook;

class Hook_Resource_User extends Hook
{
	protected Model_User $modelUser;

	public function onChangeLanguage(): void
	{
		$user	= $this->getAuthenticatedUser();
		if( NULL === $user )
			return;

		$payload	= $this->getPayload();
		$language	= trim( $payload['language'] ?? '' );
		if( '' === $language )
			return;

		if( $language !== $user->language )
			$this->modelUser->edit( $user->userId, ['language' => $language] );
	}

	public function onPageApplyModules(): void
	{
		$user	= $this->getAuthenticatedUser();
		if( NULL === $user || !isset( $user->language ) )
			return;

		$language	= $this->env->getLanguage();
		if( $language->getLanguage() !== $user->language )					//  user language differs from currently set language
			if( $language->isAllowedLanguage( $user->language ) )			//  user language is allowed
				$language->setLanguage( $user->language );					//  set user language on environment
	}

	/**
	 *	...
	 *	@access		public
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function onUserRemove(): void
	{
		if( empty( $this->payload['userId'] ) )
			return;

		$modelPassword	= new Model_User_Password( $this->env );
		$modelGroupUser	= new Model_Group_User( $this->env );

		$modelPassword->removeByIndex( 'userId', $this->payload['userId'] );
		$modelGroupUser->removeByIndex( 'userId', $this->payload['userId'] );
		$this->modelUser->remove( $this->payload['userId'] );

		if( isset( $this->payload['counts'] ) )
			$this->payload['counts']['Resource_Users']	= (object) ['entities' => 1];
	}

	protected function __onInit(): void
	{
		$this->modelUser	= new Model_User( $this->env );
	}

	protected function getAuthenticatedUser(): ?Entity_User
	{
		if( !$this->env->getLogic()->get( 'Authentication' ) )
			return NULL;

		$session	= $this->env->getSession();
		$userId		= (int) $session->get( Logic_Authentication::$sessionKeyAuthUserId );			//  get ID of current user (or zero)
		if( 0 === $userId )
			return NULL;

		/** @var ?Entity_User $user */
		$user		= $this->modelUser->get( $userId );
		return $user;
	}
}
