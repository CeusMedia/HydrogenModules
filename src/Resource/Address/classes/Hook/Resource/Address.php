<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Resource_Address extends Hook
{
	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function onListUserRelations(): void
	{
		if( empty( $this->payload['userId'] ) ){
			$message	= 'Hook "Hook_Resource_Address::onListUserRelations" is missing user ID in data.';
			$this->env->getMessenger()->noteFailure( $message );
			return;
		}
		$modelUser		= new Model_User( $this->env );
		if( !$modelUser->has( $this->payload['userId'] ) )
			return;

		$linkable		= $this->payload['linkable'] ?? FALSE;
		$activeOnly		= $this->payload['activeOnly'] ?? FALSE;
		$linkController	= NULL;
		$linkAction		= NULL;
/*		$auth			= Logic_Authentication::getInstance( $this->env );
		if( $linkable && $auth->isAuthenticated() ){
			$acl			= $this->env->getAcl();
			$viewerRoleId	= $auth->getCurrentRoleId();
			if( $acl->hasRight( $viewerRoleId, 'admin/mail/queue', 'view' ) ){
				$linkController	= 'Admin_Mail_Queue';
				$linkAction		= 'view';
			}
			else if( $acl->hasRight( $viewerRoleId, 'manage/my/mail', 'view' ) ){
				$linkController	= 'Manage_My_Mail';
				$linkAction		= 'view';
			}
			else
				$linkable		= FALSE;
		}
*/
		$addresses		= Logic_Address::getInstance( $this->env )
			->getAllByRelation( 'user', $this->payload['userId'], [], ['addressId' => 'DESC'] );

		$icon			= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-map-marker', 'title' => 'Adresse'] );

		$words			= $this->env->getLanguage()->getWords( 'address' );

		$list			= [];
		/** @var object{mailId: int, status: int, 'subject: string, enqueuedAt: int} $mail */
		foreach( $addresses as $address ){
			$label	= match( $address->type ){
				Model_Address::TYPE_LOCATION	=> 'Ort',
				Model_Address::TYPE_BILLING		=> 'Rechnungsanschrift',
				Model_Address::TYPE_DELIVERY	=> 'Lieferanschrift',
				default							=> 'Adresse',
			};
			$list[]		= new Entity_ModuleEntityRelationItem( [
				'id'		=> $linkable ? $address->addressId : NULL,
				'label'		=> $icon.'&nbsp;'.$label.': '.$address->street.', '.$address->city,
			] );
		}

		View_Helper_ItemRelationLister::enqueueRelations(
			$this->payload,																	//  hook content data
			$this->module,																			//  module called by hook
			Entity_ModuleEntityRelation::TYPE_ENTITY,											//  relation type: entity or relation
			$list,																					//  list of related items
			$words['hook-relations']['label'],														//  label of type of related items
			$linkController,																		//  controller of entity
			$linkAction																				//  action to view or edit entity
		);
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function onUserRemove(): void
	{
		/* @todo make this configurable or dynamic or whatever */
		$relationTypes	= ['user', 'customer'];

		$data	= $this->getPayload();
		if( empty( $data->userId ) ){
			$message	= 'Hook "Hook_Resource_Address::onUserRemove" is missing user ID in data.';
			$this->env->getMessenger()->noteFailure( $message );
			return;
		}

		$logicAddress	= Logic_Address::getInstance( $this->env );
		$addresses		= $logicAddress->getAllByRelation( $relationTypes, $data->userId );
		foreach( $addresses as $address )
			$logicAddress->remove( $address );

		if( isset( $this->payload['counts'] ) )
			$this->payload['counts']['Resource_Address']	= (object) [
				'entities' => count( $addresses )
			];
	}
}
