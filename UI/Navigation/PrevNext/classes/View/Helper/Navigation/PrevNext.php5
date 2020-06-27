<?php
class View_Helper_Navigation_PrevNext
{
	protected $env;
	protected $modelClass;
	protected $modelObject;
	protected $labelColumn			= 'title';
	protected $currentId;
	protected $useIcons				= TRUE;
	protected $useIndex				= FALSE;
	protected $urlTemplate;
	protected $buttonSize			= 'small';
	protected $buttonType			= NULL;
	protected $indexUrl;
	protected $indexLabel;

	const BUTTON_SIZE_DEFAULT		= '';
	const BUTTON_SIZE_SMALL			= 'small';
	const BUTTON_SIZE_MINI			= 'mini';
	const BUTTON_SIZE_LARGE			= 'large';

	const BUTTON_SIZES				= array(
		self::BUTTON_SIZE_DEFAULT,
		self::BUTTON_SIZE_SMALL,
		self::BUTTON_SIZE_MINI,
		self::BUTTON_SIZE_LARGE,
	);


	public function __construct( CMF_Hydrogen_Environment $env )
	{
		$this->env	= $env;
	}

	public static function create( CMF_Hydrogen_Environment $env ): self
	{
		return new self( $env );
	}

	public function render(): string
	{
		if( !$this->currentId )
			throw new RuntimeException( 'No current ID set' );
		if( !$this->urlTemplate )
			throw new RuntimeException( 'No URL template set' );
		if( $this->useIndex && !$this->indexUrl )
			throw new RuntimeException( 'No index URL set' );
		if( !$this->modelObject ){
			if( !$this->modelClass )
				throw new RuntimeException( 'Neither model class nor object set' );
			$this->modelObject	= new $this->modelClass( $this->env );
		}
		$primaryKey	= $this->modelObject->getPrimaryKey();

		$next	= $this->modelObject->getAll(
			array( $primaryKey => '> '.$this->currentId ),
			array( $primaryKey => 'ASC' ),
			array( 0, 1 )
		);
		$prev	= $this->modelObject->getAll(
			array( $primaryKey => '< '.$this->currentId ),
			array( $primaryKey => 'DESC' ),
			array( 0, 1 )
		);
		$buttonNext	= '';
		$buttonPrev	= '';
		$buttonIndex	= '';
		if( $next ){
			$label		= $next[0]->{$this->labelColumn};
			if( $this->useIcons ){
				$iconNext	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-right' ) );
				$label	= $label.'&nbsp;'.$iconNext;
			}
			$url		= sprintf( $this->urlTemplate, $next[0]->{$primaryKey} );
			$buttonNext	= $this->renderButton( $url, $label );
		}
		if( $prev ){
			$label		= $prev[0]->{$this->labelColumn};
			if( $this->useIcons ){
				$iconPrev	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
				$label		= $iconPrev.'&nbsp;'.$label;
			}
			$url		= sprintf( $this->urlTemplate, $prev[0]->{$primaryKey} );
			$buttonPrev	= $this->renderButton( $url, $label );
		}
		if( $this->useIndex ){
			if( !$this->indexUrl )
				throw new RuntimeException( 'No index URL set' );
			$label	= $this->indexLabel ? $this->indexLabel : '';
			if( $this->useIcons ){
				$iconIndex	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-list' ) );
				$label		= strlen( $label ) ? $iconIndex.'&nbsp;'.$label : $iconIndex;
			}
			$buttonIndex	= $this->renderButton( $this->indexUrl, $label );
		}
		return UI_HTML_Tag::create( 'div', array( $buttonPrev, $buttonIndex, $buttonNext ), array( 'class' => 'btn-group' ) );
	}

	protected function renderButton( $url, $label ){
		$classes	= array( 'btn' );
		if( $this->buttonSize )
			$classes[]	= 'btn-'.$this->buttonSize;
		if( $this->buttonType )
			$classes[]	= 'btn-'.$this->buttonType;
		$button		= UI_HTML_Tag::create( 'a', $label, array(
			'href'	=> $url,
			'class'	=> join( ' ', $classes ),
		) );
		return $button;
	}

	public function setButtonSize( $buttonSize ): self
	{
		if( !in_array( $buttonSize, self::BUTTON_SIZES ) )
			throw new RangeException( 'Invalid button size' );
		$this->buttonSize	= $buttonSize;
		return $this;
	}

	public function setButtonType( $buttonType ): self
	{
		if( !in_array( $buttonType, self::BUTTON_TYPES ) )
			throw new RangeException( 'Invalid button type' );
		$this->buttonType	= $buttonType;
		return $this;
	}

	public function setCurrentId( int $currentId ): self
	{
		$this->currentId	= $currentId;
		return $this;
	}

	public function setLabelColumn( string $column ): self
	{
		$this->labelColumn	= $column;
		return $this;
	}

	public function setModelClass( string $modelClass ): self
	{
		$this->modelClass	= $modelClass;
		return $this;
	}

	public function setModelObject( object $modelObject ): self
	{
		$this->modelObject	= $modelObject;
		return $this;
	}

	public function setUrlTemplate( string $urlTemplate ): self
	{
		$this->urlTemplate	= $urlTemplate;
		return $this;
	}

	public function setIndexUrl( $indexUrl ): self
	{
		$this->indexUrl		= $indexUrl;
		return $this;
	}

	public function useIcons( bool $useIcons = TRUE ): self
	{
		$this->useIcons		= $useIcons;
		return $this;
	}

	public function useIndex( bool $useIndex = TRUE ): self
	{
		$this->useIndex		= $useIndex;
		return $this;
	}
}
