<?php

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Info_Manual_CategorySelector
{
	protected Environment $env;						// \CeusMedia\HydrogenFramework\Environment
	protected array $categories			= [];
	protected int|string|NULL $categoryId		= NULL;

	/**
	 *	Constructor.
	 *	@access		protected
	 *	@param		Environment		$env		Environment object
	 *	@return		void
	 */
	public function __construct( Environment $env )
	{
		$this->env	= $env;
	}

	public function render(): string
	{
		$categoryId	= $this->env->getSession()->get( 'filter_info_manual_categoryId' );

		if( !$this->categories ){
			$model	= new Model_Manual_Category( $this->env );
			$this->categories	= $model->getAll( [
				'status'	=> '>= '.Model_Manual_Category::STATUS_NEW,
			], ['rank' => 'ASC'] );
		}
		if( count( $this->categories ) === 1 )
			return '';
		$options	 = [];
		foreach( $this->categories as $category ){
			$options[$category->manualCategoryId]	= $category->title;
		}
		$options	= HtmlElements::Options( $options, $categoryId );
		$select		= HtmlTag::create( 'select', $options, [
			'id'		=> 'select_category',
			'class'		=> 'span12',
//			'onchange'	=> 'document.location.href=\'./info/manual/category/'.$category->manualCategoryId.'\';',
		] );
		return '
			<div class="row-fluid">
				<div class="span12">
					<label for="select_category">Kategorie</label>
					'.$select.'
				</div>
			</div>
			<script></script>';
	}

	public function setActiveCategoryId( int|string $categoryId ): self
	{
		$this->categoryId	= $categoryId;
		return $this;
	}

	public function setCategories( array $categories ): self
	{
		$this->categories	= $categories;
		return $this;
	}
}
