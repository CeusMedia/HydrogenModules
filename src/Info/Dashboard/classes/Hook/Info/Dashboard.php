<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Info_Dashboard extends Hook
{
	public function onListUserRelations(): void
	{
		if( empty( $this->payload['userId'] ) ){
			$message	= 'Hook "Info_Dashboard::onListUserRelations" is missing user ID in data.';
			$this->env->getMessenger()->noteFailure( $message );
			return;
		}

		/** @var Logic_Info_Dashboard $logic */
		$logic	= Logic_Info_Dashboard::getInstance( $this->env );
		$dashboards = $logic->getUserDashboards( $this->payload['userId'] );

		$activeOnly		= $this->payload['activeOnly'] ?? FALSE;
		$linkable		= $this->payload['linkable'] ?? FALSE;
		$list			= [];
		$icon			= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-th', 'title' => 'Dashboard'] );
		$words			= $this->env->getLanguage()->getWords( 'info/dashboard' );

		foreach( $dashboards as $dashboard ){
			$list[]		= (object) [
				'id'		=> $linkable ? $dashboard->dashboardId : NULL,
				'label'		=> $icon.'&nbsp;'.$dashboard->title,
			];
		}
		View_Helper_ItemRelationLister::enqueueRelations(
			$this->payload,																	//  hook content data
			$this->module,																			//  module called by hook
			'entity',																			//  relation type: entity or relation
			$list,																					//  list of related items
			$words['hook-relations']['label'],														//  label of type of related items
			'Info_Dashboard',																//  controller of entity
			'select'																			//  action to view or edit entity
		);
	}

	public function onUserRemove(): void
	{
		if( empty( $this->payload['userId'] ) ){
			$message	= 'Hook "Info_Dashboard::onUserRemove" is missing user ID in data.';
			$this->env->getMessenger()->noteFailure( $message );
			return;
		}
		$logic		= Logic_Info_Dashboard::getInstance( $this->env );
		$model		= new Model_Dashboard( $this->env );
		$dashboards = $logic->getUserDashboards( $this->payload['userId'] );
		foreach( $dashboards as $dashboard )
			$model->remove( $dashboard->dashboardId );
		if( isset( $this->payload['counts'] ) )
			$this->payload['counts']['Info_Dashboard']	= (object) ['entities' => count( $dashboards )];
	}
}
