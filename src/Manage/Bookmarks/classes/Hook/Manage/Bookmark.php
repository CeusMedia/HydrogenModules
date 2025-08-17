<?php

use CeusMedia\HydrogenFramework\Hook;

class Hook_Manage_Bookmark extends Hook
{
	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function onTinyMceGetLinkList(): void
	{
		$words = $this->env->getLanguage()->getWords('js/tinymce');
		$prefixes = (object)$words['link-prefixes'];

		$list = [];
		$model = new Model_Bookmark($this->env);
		$orders = ['title' => 'ASC'];
		foreach ($model->getAll([], $orders) as $link) {
			$list[] = (object)[
				'title' => /*$prefixes->bookmark.*/
					$link->title,
				'type' => 'link:bookmark',
				'value' => $link->url,
			];
		}
		$list = [(object)[
			'title' => $prefixes->bookmark,
			'menu' => array_values($list),
		]];
//		$context->list	= array_merge( $context->list, array_values( $list ) );
		$this->context->list = array_merge($this->context->list, $list);
	}
}