<?php

use CeusMedia\HydrogenFramework\Hook;

class Hook_Manage_Page extends Hook
{
	/**
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function onTinyMceGetLinkList(): void
	{
		$frontend		= Logic_Frontend::getInstance( $this->env );
		if( !$frontend->hasModule( 'Resource_Pages' ) )
			return;

		$words		= $this->env->getLanguage()->getWords( 'manage/page' );
		$model		= new Model_Page( $this->env );
		$list		= [];
		foreach( $model->getAllByIndex( 'status', 1, ['rank' => 'ASC'] ) as $nr => $page ){
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
			$list[$page->title.$nr]	= (object) [
				'title'	=> $page->title,
				'value'	=> './'.$page->identifier,
			];
		}
		if( $list ){
			ksort( $list );
			$list	= array( (object) array(
				'title'	=> $words['tinyMCE']['prefix'],
				'menu'	=> array_values( $list ),
			) );
	//		$this->context->list	= array_merge( $this->context->list, array_values( $list ) );
			$this->context->list	= array_merge( $this->context->list, $list );
		}
	}
}
