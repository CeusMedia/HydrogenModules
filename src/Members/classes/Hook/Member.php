<?php
class Hook_Member extends \CeusMedia\HydrogenFramework\Hook
{
	public function onGetRelatedUsers(): void
	{
		$logicMember	= Logic_Member::getInstance( $this->env );
		$modelUser	= new Model_User( $this->env );
		$userIds	= $logicMember->getRelatedUserIds( $this->payload['userId'], 2 );
		$list		= [];
		if( $userIds ){
			$relatedUsers	= $modelUser->getAll( ['userId' => $userIds], ['username' => 'ASC'] );
			foreach( $relatedUsers as $relatedUser )
				$list[$relatedUser->userId]	= $relatedUser;
		}
		$words	= $this->env->getLanguage()->getWords( 'member' );
		$this->payload['list'][]	= (object) array(
			'module'		=> 'Members',
			'label'			=> $words['hook-getRelatedUsers']['label'],
			'count'			=> count( $list ),
			'list'			=> $list,
		);
	}
}
