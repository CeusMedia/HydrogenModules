<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Definition as ModuleDefinition;

class View_Helper_ItemRelationLister
{
	public const RENDER_MODE_LIST		= 'list';
	public const RENDER_MODE_TABLE		= 'table';

	public const RENDER_MODES			= [
		self::RENDER_MODE_LIST,
		self::RENDER_MODE_TABLE,
	];

	protected const STATUS_EMPTY		= 0;
	protected const STATUS_LOADING		= 1;
	protected const STATUS_LOADED		= 2;

	protected Environment $env;
	protected int $status				= self::STATUS_EMPTY;

	/** @var Entity_ModuleEntityRelation[]	$relations */
	protected array $relations				= [];
	protected string $hookResource;
	protected string $hookEvent;
	protected array $hookIndices			= [];
	protected string $tableClass			= '';
	protected string $renderMode			= self::RENDER_MODE_TABLE;
	protected bool $activeOnly				= FALSE;
	protected bool $linkable				= TRUE;
	protected int $limit					= 20;
	protected string $labelCountEntities	= '';
	protected string $labelCountRelations	= '';
//	protected $hintEntities;
//	protected $hintRelations;

	protected array $types					= [];

	/** @var array<string,array<string,string>> $words */
	protected array $words;

	/** @var array<string,string> $labels */
	protected array $labels;

	public function __construct( Environment $env )
	{
		$this->env		= $env;
		$this->words	= $this->env->getLanguage()->getWords( 'helper/relation' );
		$this->labels	= [
			'entities.count.label'		=> $this->words['entities']['countLabel'],
			'entities.count.hint'		=> $this->words['entities']['countHint'],
			'relations.count.label'		=> $this->words['relations']['countLabel'],
			'relations.count.hint'		=> $this->words['entities']['countHint'],
		];
	}

	/**
	 *	@param		array								$data
	 *	@param		ModuleDefinition|NULL				$module
	 *	@param		string								$type
	 *	@param		Entity_ModuleEntityRelationItem[]	$items
	 *	@param		string								$label
	 *	@param		string|NULL							$controller
	 *	@param		string|NULL							$action
	 *	@return		void
	 */
	public static function enqueueRelations( array & $data, ModuleDefinition|NULL $module, string $type, array $items, string $label, ?string $controller = NULL, ?string $action = NULL ): void
	{
		if( !isset( $data['list'] ) )
			$data['list']	= [];
		if( count( $items ) ){
			$data['list'][]	= new Entity_ModuleEntityRelation( [
				'module'		=> $module,
				'type'			=> $type,
				'label'			=> $label,
				'items'			=> $items,
				'controller'	=> $controller,
				'action'		=> $action,
			] );
		}
	}

	/**
	 *	@return		bool
	 *	@throws		ReflectionException
	 */
	public function hasRelations(): bool
	{
		if( self::STATUS_LOADED !== $this->status )
			$this->load();
		return [] !== $this->relations;
	}

	/**
	 *	@return		string
	 *	@throws		ReflectionException
	 */
	public function render(): string
	{
		if( self::STATUS_LOADED !== $this->status )
			$this->load();
		$this->renderTypes();
		return match( $this->renderMode ){
			self::RENDER_MODE_TABLE	=> $this->renderRelationsAsTable(),
			default					=> $this->renderRelationsAsList(),
		};
	}

	public function setActiveOnly( bool $boolean ): self
	{
		$this->activeOnly	= $boolean;
		$this->status		= self::STATUS_EMPTY;
		$this->relations	= [];
		return $this;
	}

	public function setHintTextForEntities( string $text ): self
	{
		$this->labels['entities.count.hint']	= $text;
		return $this;
	}

	public function setHintTextForRelations( string $text ): self
	{
		$this->labels['relations.count.hint']	= $text;
		return $this;
	}

	public function setHook( string $resource, string $event, array $indices ): self
	{
		$this->hookResource	= $resource;
		$this->hookEvent	= $event;
		$this->hookIndices	= $indices;
		$this->status		= self::STATUS_EMPTY;
		$this->relations	= [];
		return $this;
	}

	public function setLimit( int $limit ): self
	{
		$this->limit		= $limit;
		return $this;
	}

	public function setLinkable( bool $boolean ): self
	{
		$this->linkable		= $boolean;
		$this->status		= self::STATUS_EMPTY;
		$this->relations	= [];
		return $this;
	}

	/**
	 *	@param		string		$mode
	 *	@throws		RangeException	if given mode is invalid
	 *	@return		self
	 */
	public function setMode( string $mode ): self
	{
		if( !in_array( $mode, self::RENDER_MODES, TRUE ) )
			throw new RangeException( 'Invalid render mode: '.$mode );
		$this->renderMode	= $mode;
		return $this;
	}

	public function setTableClass( string $class ): self
	{
		$this->tableClass	= $class;
		return $this;
	}

	//  --  PROTECTED  --  //

	/**
	 *	Loads all related module entities by calling hook.
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function load(): void
	{
		if( !$this->hookResource || !$this->hookEvent )
			throw new RuntimeException( 'No hook for event call defined' );
		if( !$this->hookIndices )
			throw new RuntimeException( 'No indices set' );
		$payload	= array_merge( $this->hookIndices, [
			'activeOnly'	=> $this->activeOnly,
			'linkable'		=> $this->linkable,
			'list'			=> [],
		] );
		$this->env->getCaptain()->callHook( $this->hookResource, $this->hookEvent, $this, $payload );
		$this->relations	= $payload['list'];
	}

	/**
	 *	@return		string
	 */
	protected function renderRelationsAsList(): string
	{
		if( [] === $this->relations )
			return '';
//		$roleId		= $this->env->getSession()->get( Logic_Authentication::$sessionKeyAuthRoleId );
//		$fullAccess
		$acl		= $this->env->getAcl();
		$list		= [];
		$iconMore	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-list'] );
		foreach( $this->relations as $relation ){
			$type	= sprintf( $this->types[$relation->type], $relation->count );
			$items	= [];
			$icon	= '';
			if( !empty( $relation->icon ) )
				$icon	= HtmlTag::create( 'i', '', ['class' => $relation->icon] ).'&nbsp;';

			$access	= FALSE;
			if( NULL !== $relation->controller && NULL !== $relation->action )
				$access	= $acl->has( $relation->controller, $relation->action );

			$total		= count( $relation->items );
			if( $this->limit > 0 )
				$relation->items	= array_slice( $relation->items, 0, $this->limit );
			foreach( $relation->items as $item ){
				$label		= $icon.$item->label;
				if( $access && '' !== ( $item->id ?? '' ) ){
					$controller	= str_replace( "_", "/", strtolower( $relation->controller ) );
					$arguments	= !empty( $item->arguments ) ? join( "/", $item->arguments ) : $item->id;
					$url		= './'.$controller.'/'.$relation->action.'/'.$arguments;
					$label		= HtmlTag::create( 'a', $label, ['href' => $url, 'class' => 'autocut'] );
				}
				$items[]	= HtmlTag::create( 'li', $label, ['class' => 'autocut'] );
			}
			if( $this->limit > 0 && $total > count( $items ) ){
				$label		= sprintf( 'und %s weitere', $total - count( $items ) );
				$items[]	= HtmlTag::create( 'li', $iconMore.'&nbsp;'.$label );
			}
			$items	= HtmlTag::create( 'ul', $items, ['class' => 'unstyled '.$this->tableClass] );
			$count	= HtmlTag::create( 'small', '('.$type.')', ['class' => 'muted'] );
			$list[]	= HtmlTag::create( 'h5', $relation->label.'&nbsp;'.$count ).$items;
		}
		return HtmlTag::create( 'div', $list, ['class' => 'item-relations'] );
	}

	/**
	 *	@return		string
	 */
	protected function renderRelationsAsTable(): string
	{
		if( [] === $this->relations )
			return '';
//		$roleId		= $this->env->getSession()->get( Logic_Authentication::$sessionKeyAuthRoleId );
//		$fullAccess
		$acl		= $this->env->getAcl();
		$rows		= [];
		$iconMore	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-list'] );
		foreach( $this->relations as $relation ){
			$items	= [];
			$icon	= '';
			if( !empty( $relation->icon ) )
				$icon	= HtmlTag::create( 'i', '', ['class' => $relation->icon] ).'&nbsp;';

			$access	= FALSE;
			if( NULL !== $relation->controller && NULL !== $relation->action)
				$access		= $acl->has( $relation->controller, $relation->action );

			$total		= count( $relation->items );
			if( $this->limit > 0 )
				$relation->items	= array_slice( $relation->items, 0, $this->limit );
			foreach( $relation->items as $item ){
				$label		= $icon.$item->label;
				if( $access && '' !== ( $item->id ?? '' ) ){
					$controller	= str_replace( "_", "/", strtolower( $relation->controller ) );
					$arguments	= !empty( $item->arguments ) ? join( "/", $item->arguments ) : $item->id;
					$url		= './'.$controller.'/'.$relation->action.'/'.$arguments;
					$label		= HtmlTag::create( 'a', $label, ['href' => $url, 'class' => 'autocut'] );
				}
				$items[]	= HtmlTag::create( 'li', $label, ['class' => 'autocut'] );
			}
			if( $this->limit > 0 && $total > count( $items ) ){
				$label		= sprintf( 'und %s weitere', $total - count( $items ) );
				$items[]	= HtmlTag::create( 'li', $iconMore.'&nbsp;'.$label );
			}

			$type	= sprintf( $this->types[$relation->type], $relation->count );
			$type	= HtmlTag::create( 'small', $type, ['class' => 'muted'] );

			$items	= HtmlTag::create( 'ul', $items, ['class' => 'unstyled'] );
			$count	= HtmlTag::create( 'small', '('.$relation->count.')', ['class' => 'muted'] );
			$label	= HtmlTag::create( 'big', $relation->label ).'<br/>'.$type;
			$rows[]	= HtmlTag::create( 'tr', [
				HtmlTag::create( 'td', $label, ['class' => 'cell-relation-label'] ),
				HtmlTag::create( 'td', $items, ['class' => 'cell-relation-items'] ),
			] );
		}
		$colgroup	= HtmlElements::ColumnGroup( "30%", "" );
		$thead		= HtmlTag::create( 'thead', HtmlElements::TableHeads( [
			"Typ",
			"Verknüpfungen / Einträge",
		] ) );
		$tbody		= HtmlTag::create( 'tbody', $rows );
		$table		= HtmlTag::create( 'table', $colgroup.$thead.$tbody, [
			'class'		=> 'table table-striped item-relation-lister '.$this->tableClass,
		] );
		return HtmlTag::create( 'div', $table, ['class' => 'item-relations'] );
	}

	/**
	 *	@todo			code style
	 */
	protected function renderTypes(): void
	{
		$this->types	= [
			'entity'		=> $this->labels['entities.count.label'],
			'relation'		=> $this->labels['relations.count.label'],
		];
		if( $this->labels['entities.count.hint'] )
			$this->types['entity']		=  HtmlTag::create( 'abbr', $this->types['entity'], ['title' => $this->labels['entities.count.hint']] );
		if( $this->labels['relations.count.hint'] )
			$this->types['relation']	=  HtmlTag::create( 'abbr', $this->types['relation'], ['title' => $this->labels['relations.count.hint']] );
	}
}
