<?php
class Job_Info_Forum extends Job_Abstract
{
	protected $config;

	protected $language;

	protected $options;

	protected $words;

	public function sendDaily( bool $verbose = FALSE )
	{
//		if( $this->isLocked( 'info.forum.send' ) )
//			return;
//		$this->lock( 'info.forum.send' );

		$start		= microtime( TRUE );
//		$words		= (object) $this->words->send;													//  get words or like date formats
//		$saluations	= $this->words->salutations;													//  get salutational words

		$modelUser		= new Model_User( $this->env );
		$modelPost		= new Model_Forum_Post( $this->env );
		$logicMail		= Logic_Mail::getInstance( $this->env );

		$receivers		= [];
		$roleIds		= trim( $this->options->get( 'mail.inform.managers.roleIds' ) );
		$userIds		= trim( $this->options->get( 'mail.inform.managers.userIds' ) );
		if( strlen( $roleIds ) ){
			$listIds	= [];
			foreach( explode( ",", $roleIds ) as $roleId )
				if( strlen( trim( $roleId ) ) && (int) $roleId > 0 )
					$listIds[]	= (int) trim( $roleId );
			if( $listIds )
				foreach( $modelUser->getAllByIndex( 'roleId', $listIds ) as $user )
					$receivers[(int) $user->userId]	= $user;
		}
		if( strlen( $userIds ) ){
			$listIds	= [];
			foreach( explode( ",", $userIds ) as $userId )
				if( strlen( trim( $userId ) ) && (int) $userId > 0 )
					$listIds[]	= (int) $userId;
			if( $listIds )
				foreach( $modelUser->getAllByIndex( 'userId', $listIds ) as $user )
					$receivers[(int) $user->userId]	= $user;
		}

		$posts		= $modelPost->getAll( ['status' => 0], ['createdAt' => 'DESC'] );

		if( $posts ){
			foreach( $receivers as $receiver ){
				$data	= [
					'posts'			=> $posts,
					'user'			=> $receiver,
				];
				$mail	= new Mail_Forum_Daily( $this->env, $data );
				if( $this->options->get( 'mail.sender' ) )
					$mail->setSender( $this->options->get( 'mail.sender' ) );
				$language	= $this->env->getLanguage()->getLanguage();
				$logicMail->handleMail( $mail, $receiver, $language );
			}
			$time	= round( microtime( TRUE ) - $start, 3 ) * 1000;
			$this->log( sprintf( 'Daily forum mail sent to '.count( $receivers ).' manager(s) in %d ms.', $time ) );
		}
		else
			$this->log( 'No daily forum mail sent.' );
//		$this->unlock( 'info.forum.send' );
	}

	protected function __onInit(): void
	{
		$this->config		= $this->env->getConfig();												//  get app config
		$this->language		= $this->env->getLanguage();											//  get language support
		$this->options		= $this->config->getAll( 'module.info_forum.', TRUE );					//  get module options for job
		$this->words		= (object) $this->language->getWords( 'info/forum' );					//  get module words
	}
}
