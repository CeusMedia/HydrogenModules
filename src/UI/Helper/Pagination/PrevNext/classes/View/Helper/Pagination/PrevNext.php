<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Pagination_PrevNext/* extends CMF_Hydrogen_View_Helper*/
{
	protected Environment $env;					//  remove after using newer framework base helper class
	protected ?string $modelClass			= NULL;
	protected ?object $modelObject			= NULL;
	protected string $labelColumn			= 'title';
	protected ?string $orderColumn			= NULL;
	protected int|string|NULL $currentId	= NULL;
	protected bool $useIcons				= TRUE;
	protected bool $useIndex				= FALSE;
	protected ?string $urlTemplate			= NULL;
	protected string $buttonSize			= self::BUTTON_SIZE_SMALL;
	protected string $buttonState			= self::BUTTON_STATE_DEFAULT;
	protected ?string $indexUrl				= NULL;
	protected ?string $indexLabel			= NULL;
	protected ?object $nextEntry			= NULL;
	protected ?object $previousEntry		= NULL;

	public const BUTTON_SIZE_DEFAULT		= '';
	public const BUTTON_SIZE_SMALL			= 'small';
	public const BUTTON_SIZE_MINI			= 'mini';
	public const BUTTON_SIZE_LARGE			= 'large';

	public const BUTTON_SIZES				= [
		self::BUTTON_SIZE_DEFAULT,
		self::BUTTON_SIZE_SMALL,
		self::BUTTON_SIZE_MINI,
		self::BUTTON_SIZE_LARGE,
	];

	public const BUTTON_STATE_DEFAULT		= '';
	public const BUTTON_STATE_PRIMARY		= 'primary';
	public const BUTTON_STATE_SECONDARY		= 'secondary';
	public const BUTTON_STATE_SUCCESS		= 'success';
	public const BUTTON_STATE_DANGER		= 'danger';
	public const BUTTON_STATE_WARNING		= 'warning';
	public const BUTTON_STATE_INFO			= 'info';
	public const BUTTON_STATE_INVERSE		= 'inverse';
	public const BUTTON_STATE_LIGHT			= 'light';
	public const BUTTON_STATE_DARK			= 'dark';
	public const BUTTON_STATE_LINK			= 'link';

	public const BUTTON_STATES				= [
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
	];

	/**
	 *	@param		Environment		$env
	 */
	public function __construct( Environment $env )
	{
		$this->env	= $env;
	}

	/**
	 *	@param		Environment		$env
	 *	@return		self
	 */
	public static function create( Environment $env ): self
	{
		return new self( $env );
	}

	/**
	 *	@return		string
	 */
	public function render(): string
	{
		$this->validateSetup();
		$buttons	= [
			$this->renderPreviousButton(),
			$this->renderIndexButton(),
			$this->renderNextButton(),
		];
		return HtmlTag::create( 'div', $buttons, ['class' => 'btn-group'] );
	}

	/**
	 *	@param		string		$buttonSize
	 *	@return		self
	 */
	public function setButtonSize( string $buttonSize ): self
	{
		if( !in_array( $buttonSize, self::BUTTON_SIZES ) )
			throw new RangeException( 'Invalid button size' );
		$this->buttonSize	= $buttonSize;
		return $this;
	}

	/**
	 *	@param		string		$buttonState
	 *	@return		self
	 */
	public function setButtonState( string $buttonState ): self
	{
		if( !in_array( $buttonState, self::BUTTON_STATES ) )
			throw new RangeException( 'Invalid button state (must be on of {'.join( ',', self::BUTTON_STATES ).'})' );
		$this->buttonState	= $buttonState;
		return $this;
	}

	/**
	 *	@param		int|string		$currentId
	 *	@return		self
	 */
	public function setCurrentId( int|string $currentId ): self
	{
		$this->currentId	= $currentId;
		return $this;
	}

	/**
	 *	@param		string		$column
	 *	@return		self
	 */
	public function setLabelColumn( string $column ): self
	{
		$this->labelColumn	= $column;
		return $this;
	}

	/**
	 *	@param		string		$modelClass
	 *	@return		self
	 */
	public function setModelClass( string $modelClass ): self
	{
		$this->modelClass	= $modelClass;
		return $this;
	}

	/**
	 *	@param		object		$modelObject
	 *	@return		self
	 */
	public function setModelObject( object $modelObject ): self
	{
		$this->modelObject	= $modelObject;
		return $this;
	}

	/**
	 *	@param		?object		$nextEntry
	 *	@return		self
	 */
	public function setNextEntry( ?object $nextEntry ): self
	{
		$this->nextEntry	= $nextEntry;
		return $this;
	}

	/**
	 *	@param		string		$orderColumn
	 *	@return		self
	 */
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

	/**
	 *	@param		string		$urlTemplate
	 *	@return		self
	 */
	public function setUrlTemplate( string $urlTemplate ): self
	{
		$this->urlTemplate	= $urlTemplate;
		return $this;
	}

	/**
	 *	@param		string		$indexUrl
	 *	@return		self
	 */
	public function setIndexUrl( string $indexUrl ): self
	{
		$this->indexUrl		= $indexUrl;
		return $this;
	}

	/**
	 *	@param		bool		$useIcons
	 *	@return		self
	 */
	public function useIcons( bool $useIcons = TRUE ): self
	{
		$this->useIcons		= $useIcons;
		return $this;
	}

	/**
	 *	@param		bool		$useIndex
	 *	@return		self
	 */
	public function useIndex( bool $useIndex = TRUE ): self
	{
		$this->useIndex		= $useIndex;
		return $this;
	}

	//  --  PROTECTED  --  //

	/**
	 *	@return		?object
	 */
	protected function getNext(): ?object
	{
		if( NULL !== $this->nextEntry )
			return $this->nextEntry;
		if( NULL === $this->modelObject )
			$this->modelObject	= new $this->modelClass( $this->env );
		$primaryKey		= $this->modelObject->getPrimaryKey();
		$orderColumn	= $this->orderColumn ?: $primaryKey;
		$conditions		= [$primaryKey => '> '.$this->currentId];
		if( $this->orderColumn && $this->orderColumn != $primaryKey ){
			$current	= $this->modelObject->get( $this->currentId );
			$conditions	= [$orderColumn => '> '.$current->{$orderColumn}];
		}
		$found	= $this->modelObject->getAll(
			$conditions,
			[$orderColumn => 'ASC'],
			[0, 1]
		);
		return $found ? $found[0] : NULL;
	}

	/**
	 *	@return		?object
	 */
	protected function getPrevious(): ?object
	{
		if( NULL !== $this->previousEntry )
			return $this->previousEntry;
		if( NULL === $this->modelObject )
			$this->modelObject	= new $this->modelClass( $this->env );
		$primaryKey		= $this->modelObject->getPrimaryKey();
		$orderColumn	= $this->orderColumn ?: $primaryKey;
		$conditions		= [$primaryKey => '< '.$this->currentId];
		if( $this->orderColumn && $this->orderColumn != $primaryKey ){
			$current	= $this->modelObject->get( $this->currentId );
			$conditions	= [$orderColumn => '< '.$current->{$orderColumn}];
		}
		$found	= $this->modelObject->getAll(
			$conditions,
			[$orderColumn => 'DESC'],
			[0, 1]
		);
		return $found ? $found[0] : NULL;
	}

	/**
	 *	@param		string		$url
	 *	@param		string		$label
	 *	@return		string
	 */
	protected function renderButton( string $url, string $label ): string
	{
		$classes	= ['btn'];
		if( $this->buttonSize )
			$classes[]	= 'btn-'.$this->buttonSize;
		if( $this->buttonState )
			$classes[]	= 'btn-'.$this->buttonState;
		return HtmlTag::create( 'a', $label, [
			'href'	=> $url,
			'class'	=> join( ' ', $classes ),
		] );
	}

	/**
	 *	@return		string
	 */
	protected function renderIndexButton(): string
	{
		if( !$this->useIndex )
			return '';
		if( NULL === $this->indexUrl )
			throw new RuntimeException( 'No index URL set' );
		$label	= $this->indexLabel ?: '';
		if( $this->useIcons ){
			$iconIndex	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-list'] );
			$label		= strlen( $label ) ? $iconIndex.'&nbsp;'.$label : $iconIndex;
		}
		return $this->renderButton( $this->indexUrl, $label );
	}

	/**
	 *	@return		string
	 */
	protected function renderNextButton(): string
	{
		$entry	= $this->getNext();
		if( !$entry )
			return '';
		$primaryKey	= $this->modelObject->getPrimaryKey();
		$label		= $entry->{$this->labelColumn};
		if( $this->useIcons ){
			$icon	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-right'] );
			$label	= $label.'&nbsp;'.$icon;
		}
		$url	= sprintf( $this->urlTemplate, $entry->{$primaryKey} );
		return $this->renderButton( $url, $label );
	}

	/**
	 *	@return		string
	 */
	protected function renderPreviousButton(): string
	{
		$entry	= $this->getPrevious();
		if( !$entry )
			return '';
		$primaryKey	= $this->modelObject->getPrimaryKey();
		$label		= $entry->{$this->labelColumn};
		if( $this->useIcons ){
			$icon	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-left'] );
			$label	= $icon.'&nbsp;'.$label;
		}
		$url	= sprintf( $this->urlTemplate, $entry->{$primaryKey} );
		return  $this->renderButton( $url, $label );
	}

	/**
	 *	@param		bool		$strict
	 *	@return		bool
	 */
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
		if( NULL === $this->modelObject && NULL === $this->modelClass ){
			if( !$strict )
				return FALSE;
			throw new RuntimeException( 'Neither model class nor object set' );
		}
		return TRUE;
	}
}
