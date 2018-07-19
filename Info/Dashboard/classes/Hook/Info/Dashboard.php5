<?php
class Hook_Info_Dashboard /*extends CMF_Hydrogen_Hook*/{

	static public function onListUserRelations( CMF_Hydrogen_Environment $env, $context, $module, $data = array() ){
		$data	= (object) $data;
		if( empty( $data->userId ) ){
			$message	= 'Hook "Info_Dashboard::onListUserRelations" is missing user ID in data.';
			$env->getMessenger()->noteFailure( $message );
			return;
		}

		$logic	= Logic_Info_Dashboard::getInstance( $env );
		$dashboards = $logic->getUserDashboards( $data->userId );

		$activeOnly		= isset( $data->activeOnly ) ? $data->activeOnly : FALSE;
		$linkable		= isset( $data->linkable ) ? $data->linkable : FALSE;
		$list			= array();
		$icon			= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-th', 'title' => 'Dashboard' ) );
		$words			= $env->getLanguage()->getWords( 'info/dashboard' );

		foreach( $dashboards as $dashboard ){
			$list[]		= (object) array(
				'id'		=> $linkable ? $dashboard->dashboardId : NULL,
				'label'		=> $icon.'&nbsp;'.$dashboard->title,
			);
		}
		View_Helper_ItemRelationLister::enqueueRelations(
			$data,																					//  hook content data
			$module,																				//  module called by hook
			'entity',																				//  relation type: entity or relation
			$list,																					//  list of related items
			$words['hook-relations']['label'],														//  label of type of related items
			'Info_Dashboard',																		//  controller of entity
			'select'																				//  action to view or edit entity
		);
	}

	static public function onRemoveUser( CMF_Hydrogen_Environment $env, $context, $module, $data = array() ){
		$data	= (object) $data;
		if( empty( $data->userId ) ){
			$message	= 'Hook "Info_Dashboard::onRemoveUser" is missing user ID in data.';
			$env->getMessenger()->noteFailure( $message );
			return;
		}
		$logic		= Logic_Info_Dashboard::getInstance( $env );
		$model		= new Model_Dashboard( $env );
		$dashboards = $logic->getUserDashboards( $data->userId );
		foreach( $dashboards as $dashboard )
			$model->remove( $dashboard->dashboardId );
	}
}
