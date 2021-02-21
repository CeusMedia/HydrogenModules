<?php
class View_Helper_Info_Manual_CategorySelector
{
	protected $env;									// CMF_Hydrogen_Environment
	protected $categories		 = array();
	protected $categoryId;

	/**
	 *	Constructor.
	 *	@access		protected
	 *	@param		CMF_Hydrogen_Environment	$env		Environment object
	 *	@return		void
	 */
	public function __construct( CMF_Hydrogen_Environment $env )
	{
		$this->env	= $env;
	}

	public function render(): string
	{
		if( !$this->categories ){
			$model	= new Model_Manual_Category( $this->env );
			$this->categories	= $model->getAll( array(
				'status'	=> '>='.Model_Manual_Category::STATUS_NEW,
			), array( 'rank' => 'ASC' ) );
		}
		if( count( $this->categories ) === 1 )
			return '';
		$options	 = array();
		foreach( $this->categories as $category ){
			$options[$category->manualCategoryId]	= $category->title;
		}
		$options	= UI_HTML_Elements::Options( $options );
		$select		= UI_HTML_Tag::create( 'select', $options, array(
			'id'		=> 'select_category',
			'class'		=> 'span12',
			'onchange'	=> 'document.location.href=\'./info/manual/category/'.$category->manualCategoryId.'\';',
		) );
		return '
			<div class="row-fluid">
				<div class="span12">
					<label for="select_category">Kategorie</label>
					'.$select.'
				</div>
			</div>';
	}

	public function setActiveCategoryId( $categoryId ): self
	{
		$this->categoryId	= $categoryId;
		return $this;
	}

	public function setCategories( $categories ): self
	{
		$this->categories	= $categories;
		return $this;
	}
}
