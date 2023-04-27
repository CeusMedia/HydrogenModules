<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Info_Forum extends Hook
{
	public function onPageCollectNews(): void
	{
		$model			= new Model_Forum_Thread( $this->env );
		$oneDay			= 24 * 60 * 60;
		$conditions		= ['modifiedAt' => '> '.( time() - 7 * $oneDay )];
		$orders			= [
			'modifiedAt'	=> 'DESC',
			'createdAt'		=> 'DESC',
		];
		$threads		= $model->getAll( $conditions, $orders, [0, 3] );
		foreach( $threads as $thread ){
			$this->context->news[]	= (object) array_merge( View_Helper_NewsList::$defaultAttributes, [
				'title'		=> $thread->title,
				'timestamp'	=> max( $thread->createdAt, $thread->modifiedAt ),
				'module'	=> 'Info_Forum',
				'type'		=> 'thread',
				'typeLabel'	=> 'Forum',
				'url'		=> './info/forum/thread/'.$thread->threadId,
				'icon'		=> 'fa fa-fw fa-comment-o',
			] );
		}
	}

	public function onRegisterSitemapLinks(): void
	{
		try{
			$config			= $this->env->getConfig()->getAll( 'module.info_forum.', TRUE );
			if( !$config->get( 'sitemap' ) )
				return;
			$baseUrl		= $this->env->url.'info/forum/';

			if( $config->get( 'sitemap.topics' ) ){
				$modelTopic		= new Model_Forum_Topic( $this->env );
				$topics			= $modelTopic->getAll( [], ['modifiedAt' => 'DESC'] );
				foreach( $topics as $topic ){
					$url		= $baseUrl.'topic/'.$topic->topicId;
					$this->context->addLink( $url, max( $topic->createdAt, $topic->modifiedAt ) );
				}
			}
			if( $config->get( 'sitemap.threads' ) ){
				$modelThread	= new Model_Forum_Thread( $this->env );
				$threads		= $modelThread->getAll( ['status' => '>= 0'], ['modifiedAt' => 'DESC'] );
				foreach( $threads as $thread ){
					$url		= $baseUrl.'thread/'.$thread->threadId;
					$this->context->addLink( $url, max( $thread->createdAt, $thread->modifiedAt ) );
				}
			}
		}
		catch( Exception $e ){
			die( $e->getMessage() );
		}
	}
}
