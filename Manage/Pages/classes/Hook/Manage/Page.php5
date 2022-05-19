<?php
class Hook_Manage_Page extends CMF_Hydrogen_Hook
{
	static public function onTinyMceGetLinkList( CMF_Hydrogen_Environment $env, $context, $module, $payload = [] ){
		$frontend		= Logic_Frontend::getInstance( $env );
		if( !$frontend->hasModule( 'Resource_Pages' ) )
			return;

		$words		= $env->getLanguage()->getWords( 'manage/page' );
		$model		= new Model_Page( $env );
		$list		= [];
		foreach( $model->getAllByIndex( 'status', 1, array( 'rank' => 'ASC' ) ) as $nr => $page ){
			$page->level		= 0;
			if( $page->parentId ){
				$parent = $model->get( $page->parentId );
				$page->level		= 1;
				if( $parent->parentId ){
					$grand  = $model->get( $parent->parentId );
					$parent->identifier = $grand->identifier.'/'.$parent->identifier;
					$parent->title		= $grand->title.' / '.$parent->title;
					$page->level		= 2;
				}
				$page->identifier   = $parent->identifier.'/'.$page->identifier;
				$page->title		= $parent->title.' / '.$page->title;
			}
			$list[$page->title.$nr]	= (object) array(
				'title'	=> $page->title,
				'value'	=> './'.$page->identifier,
			);
		}
		if( $list ){
			ksort( $list );
			$list	= array( (object) array(
				'title'	=> $words['tinyMCE']['prefix'],
				'menu'	=> array_values( $list ),
			) );
	//		$context->list	= array_merge( $context->list, array_values( $list ) );
			$context->list	= array_merge( $context->list, $list );
		}
	}
}
