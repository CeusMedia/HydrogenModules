<?php

use CeusMedia\HydrogenFramework\Hook;

class Hook_Manage_Blog extends Hook
{
	public function onTinyMCE_getLinkList(): void
	{
		$frontend		= Logic_Frontend::getInstance( $this->env );
		if( !$frontend->hasModule( 'Info_Blog' ) )
			return;

		$words		= $this->env->getLanguage()->getWords( 'manage/blog' );
		$model		= new Model_Blog_Post( $this->env );
		$list		= [];
		$conditions	= ['status' => 1];
		$orders		= ['createdAt' => 'DESC'];
		foreach( $model->getAll( $conditions, $orders ) as $nr => $post ){
			$list[$post->postId]	= (object) [
				'title'	=> str_replace( '/', '-', $post->title ),
				'type'	=> 'link:page',
				'value'	=> './info/blog/post/'.$post->postId.'-'.Controller_Manage_Blog::getUriPart( $post->title )
			];
		}
		if( $list ){
			$list	= array( (object) [
				'title'	=> $words['tinyMCE']['prefix'],
				'menu'	=> array_values( $list ),
			] );
			//		$context->list	= array_merge( $context->list, array_values( $list ) );
			$this->context->list	= array_merge( $this->context->list, $list );
		}
	}
}