<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Pagination_PrevNext/* extends CMF_Hydrogen_View_Helper*/
{
	protected $env;					//  remove after using newer framework base helper class
	protected $modelClass;
	protected $modelObject;
	protected $labelColumn			= 'title';
	protected $orderColumn;
	protected $currentId			= NULL;
	protected $useIcons				= TRUE;
	protected $useIndex				= FALSE;
	protected $urlTemplate;
	protected $buttonSize			= self::BUTTON_SIZE_SMALL;
	protected $buttonState			= self::BUTTON_STATE_DEFAULT;
	protected $indexUrl;
	protected $indexLabel;
	protected $nextEntry			= FALSE;
	protected $previousEntry		= FALSE;

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

	const BUTTON_STATE_DEFAULT		= '';
	const BUTTON_STATE_PRIMARY		= 'primary';
	const BUTTON_STATE_SECONDARY	= 'secondary';
	const BUTTON_STATE_SUCCESS		= 'success';
	const BUTTON_STATE_DANGER		= 'danger';
	const BUTTON_STATE_WARNING		= 'warning';
	const BUTTON_STATE_INFO			= 'info';
	const BUTTON_STATE_INVERSE		= 'inverse';
	const BUTTON_STATE_LIGHT		= 'light';
	const BUTTON_STATE_DARK			= 'dark';
	const BUTTON_STATE_LINK			= 'link';

	const BUTTON_STATES				= array(
		self::BUTTON_STATE_DEFAULT,
		self::BUTTON_STATE_PRIMARY,
		self::BUTTON_STATE_SECONDARY,
		self::BUTTON_STATE_SUCCESS,
		self::BUTTON_STATE_DANGER,
		self::BUTTON_STATE_WARNING,
		self::BUTTON_STATE_INFO,
		self::BUTTON_STATE_INVERSE,
		self::BUTTON_STATE_LIGHT,
		self::BUTTON_STATE_DARK,
		self::BUTTON_STATE_LINK,
	);

	public function __construct( Environment $env )
	{
		$this->env	= $env;
	}

	public static function create( Environment $env ): self
	{
		return new self( $env );
	}

	public function render(): string
	{
		$this->validateSetup();
		$buttons	= array(
			$this->renderPreviousButton(),
			$this->renderIndexButton(),
			$this->renderNextButton(),
		);
		return HtmlTag::create( 'div', $buttons, array( 'class' => 'btn-group' ) );
	}

	public function setButtonSize( string $buttonSize ): self
	{
		if( !in_array( $buttonSize, self::BUTTON_SIZES ) )
			throw new RangeException( 'Invalid button size' );
		$this->buttonSize	= $buttonSize;
		return $this;
	}

	public function setButtonState( string $buttonState ): self
	{
		if( !in_array( $buttonState, self::BUTTON_STATES ) )
			throw new RangeException( 'Invalid button state (must be on of {'.join( ',', self::BUTTON_STATES ).'})' );
		$this->buttonState	= $buttonState;
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

	public function setNextEntry( ?object $nextEntry ): self
	{
		$this->nextEntry	= $nextEntry;
		return $this;
	}

	public function setOrderColumn( string $orderColumn ): self
	{
		$this->orderColumn	= $orderColumn;
		return $this;
	}

	/**
	 *	Set already found previous entry.
	 *	This will disable automatic fetching of previous entry using the model.
	 *	@access		public
	 *	@param		?object		$previousEntry		Already fetched previous entry to use
	 *	@return		self		for method chaining
	 */
	public function setPreviousEntry( ?object $previousEntry ): self
	{
		$this->previousEntry	= $previousEntry;
		return $this;
	}

	public function setUrlTemplate( string $urlTemplate ): self
	{
		$this->urlTemplate	= $urlTemplate;
		return $this;
	}

	public function setIndexUrl( string $indexUrl ): self
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

	//  --  PROTECTED  --  //

	protected function getNext()
	{
		if( $this->nextEntry !== FALSE )
			return $this->nextEntry;
		if( !$this->modelObject )
			$this->modelObject	= new $this->modelClass( $this->env );
		$primaryKey		= $this->modelObject->getPrimaryKey();
		$orderColumn	= $this->orderColumn ? $this->orderColumn : $primaryKey;
		$conditions		= array( $primaryKey => '> '.$this->currentId );
		if( $this->orderColumn && $this->orderColumn != $primaryKey ){
			$current	= $this->modelObject->get( $this->currentId );
			$conditions	= array( $orderColumn => '> '.$current->{$orderColumn} );
		}
		$found	= $this->modelObject->getAll(
			$conditions,
			array( $orderColumn => 'ASC' ),
			array( 0, 1 )
		);
		return $found ? $found[0] : NULL;
	}

	protected function getPrevious()
	{
		if( $this->previousEntry !== FALSE )
			return $this->previousEntry;
		if( !$this->modelObject )
			$this->modelObject	= new $this->modelClass( $this->env );
		$primaryKey		= $this->modelObject->getPrimaryKey();
		$orderColumn	= $this->orderColumn ? $this->orderColumn : $primaryKey;
		$conditions		= array( $primaryKey => '< '.$this->currentId );
		if( $this->orderColumn && $this->orderColumn != $primaryKey ){
			$current	= $this->modelObject->get( $this->currentId );
			$conditions	= array( $orderColumn => '< '.$current->{$orderColumn} );
		}
		$found	= $this->modelObject->getAll(
			$conditions,
			array( $orderColumn => 'DESC' ),
			array( 0, 1 )
		);
		return $found ? $found[0] : NULL;
	}

	protected function renderButton( string $url, string $label ): string
	{
		$classes	= array( 'btn' );
		if( $this->buttonSize )
			$classes[]	= 'btn-'.$this->buttonSize;
		if( $this->buttonState )
			$classes[]	= 'btn-'.$this->buttonState;
		$button		= HtmlTag::create( 'a', $label, array(
			'href'	=> $url,
			'class'	=> join( ' ', $classes ),
		) );
		return $button;
	}

	protected function renderIndexButton(): string
	{
		if( !$this->useIndex )
			return '';
		if( !$this->indexUrl )
			throw new RuntimeException( 'No index URL set' );
		$label	= $this->indexLabel ? $this->indexLabel : '';
		if( $this->useIcons ){
			$iconIndex	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-list' ) );
			$label		= strlen( $label ) ? $iconIndex.'&nbsp;'.$label : $iconIndex;
		}
		return $this->renderButton( $this->indexUrl, $label );
	}

	protected function renderNextButton(): string
	{
		$entry	= $this->getNext();
		if( !$entry )
			return '';
		$primaryKey	= $this->modelObject->getPrimaryKey();
		$label		= $entry->{$this->labelColumn};
		if( $this->useIcons ){
			$icon	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-right' ) );
			$label	= $label.'&nbsp;'.$icon;
		}
		$url	= sprintf( $this->urlTemplate, $entry->{$primaryKey} );
		return  $this->renderButton( $url, $label );
	}

	protected function renderPreviousButton(): string
	{
		$entry	= $this->getPrevious();
		if( !$entry )
			return '';
		$primaryKey	= $this->modelObject->getPrimaryKey();
		$label		= $entry->{$this->labelColumn};
		if( $this->useIcons ){
			$icon	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
			$label	= $icon.'&nbsp;'.$label;
		}
		$url	= sprintf( $this->urlTemplate, $entry->{$primaryKey} );
		return  $this->renderButton( $url, $label );
	}

	protected function validateSetup( bool $strict = TRUE ): bool
	{
		if( is_null( $this->currentId ) ){
			if( !$strict )
				return FALSE;
			throw new RuntimeException( 'No current ID set' );
		}
		if( !$this->urlTemplate ){
			if( !$strict )
				return FALSE;
			throw new RuntimeException( 'No URL template set' );
		}
		if( $this->useIndex && !$this->indexUrl ){
			if( !$strict )
				return FALSE;
			throw new RuntimeException( 'No index URL set' );
		}
		if( !$this->modelObject && !$this->modelClass ){
			if( !$strict )
				return FALSE;
			throw new RuntimeException( 'Neither model class nor object set' );
		}
		return TRUE;
	}
}
