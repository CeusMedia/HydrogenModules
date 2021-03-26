<?php
class View_Helper_ItemRelationLister
{
	protected $env;
	protected $relations;
	protected $hookResource;
	protected $hookEvent;
	protected $hookIndices				= array();
	protected $tableClass				= '';
	protected $renderMode				= 'table';
	protected $activeOnly				= FALSE;
	protected $linkable					= TRUE;
	protected $limit					= 20;
	protected $labelCountEntities		= '';
	protected $labelCountRelations		= '';
	protected $hintEntities;
	protected $hintRelations;

	protected $types;
	protected $words;

	public function __construct( CMF_Hydrogen_Environment $env )
	{
		$this->env		= $env;
		$this->words	= $this->env->getLanguage()->getWords( 'helper/relation' );
		$this->labels	= array(
			'entities.count.label'		=> $this->words['entities']['countLabel'],
			'entities.count.hint'		=> $this->words['entities']['countHint'],
			'relations.count.label'		=> $this->words['relations']['countLabel'],
			'relations.count.hint'		=> $this->words['entities']['countHint'],
		);
	}

	public static function enqueueRelations( $data, $module, $type, $items, $label, $controller = NULL, $action = NULL )
	{
		if( !isset( $data->list ) )
			$data->list	= array();
		if( count( $items ) ){
			$data->list[]	= (object) array(
				'module'		=> (object) array(
					'id'		=> $module->id,
					'label'		=> $module->title,
				),
				'type'			=> $type,
				'label'			=> $label,
				'items'			=> $items,
				'count'			=> count( $items ),
				'controller'	=> $controller,
				'action'		=> $action,
			);
		}
	}

	public function hasRelations(): bool
	{
		if( $this->relations === NULL )
			$this->load();
		return count( $this->relations ) !== 0;
	}


	public function render(): string
	{
		if( $this->relations === NULL )
			$this->load();
		$this->renderTypes();
		if( $this->renderMode == 'table' )
			return $this->renderRelationsAsTable();
		return $this->renderRelationsAsList();
	}

	public function setActiveOnly( bool $boolean ): self
	{
		$this->activeOnly	= $boolean;
		$this->relations	= NULL;
		return $this;
	}

	public function setHintTextForEntities( string $text ): self
	{
		$this->labels['entities.count.hint']	= $text;
	}

	public function setHintTextForRelations( string $text ): self
	{
		$this->labels['relations.count.hint']	= $text;
	}

	public function setHook( string $resource, string $event, array $indices ): self
	{
		$this->hookResource		= $resource;
		$this->hookEvent		= $event;
		$this->hookIndices		= $indices;
		$this->relations		= NULL;
		return $this;
	}

	public function setLimit( ínt $limit ): self
	{
		$this->limit		= $limit;
		return $this;
	}

	public function setLinkable( bool $boolean ): self
	{
		$this->linkable		= $boolean;
		$this->relations	= NULL;
		return $this;
	}

	public function setMode( string $mode ): self
	{
		$this->renderMode	= $mode;
		return $this;
	}

	public function setTableClass( string $class ): self
	{
		$this->tableClass	= $class;
		return $this;
	}

	//  --  PROTECTED  --  //

	protected function load()
	{
		if( !$this->hookResource || !$this->hookEvent )
			throw new RuntimeException( 'No hook for event call defined' );
		if( !$this->hookIndices )
			throw new RuntimeException( 'No indices set' );
		$data	= array_merge( $this->hookIndices, array(
			'activeOnly'	=> $this->activeOnly,
			'linkable'		=> $this->linkable,
			'list'			=> array(),
		) );
		$data	= (object) $data;
		$this->env->getCaptain()->callHook( $this->hookResource, $this->hookEvent, $this, $data );
		$this->relations	= $data->list;
	}

	protected function renderRelationsAsList(): string
	{
		if( !$this->relations )
			return '';
//		$roleId		= $this->env->getSession()->get( 'roleId' );
//		$fullAccess
		$acl		= $this->env->getAcl();
		$list		= array();
		$iconMore	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-list' ) );
		foreach( $this->relations as $relation ){
			$type	= sprintf( $this->types[$relation->type], $relation->count );
			$items	= array();
			$icon	= '';
			if( !empty( $relation->icon ) )
				$icon	= UI_HTML_Tag::create( 'i', '', array( 'class' => $relation->icon ) ).'&nbsp;';
			$access	= $acl->has( $relation->controller, $relation->action );
			$total		= count( $relation->items );
			if( $this->limit > 0 )
				$relation->items	= array_slice( $relation->items, 0, $this->limit );
			foreach( $relation->items as $item ){
				$label		= $icon.$item->label;
				if( $access && (bool) $item->id ){
					$controller	= str_replace( "_", "/", strtolower( $relation->controller ) );
					$arguments	= !empty( $item->arguments ) ? join( "/", $item->arguments ) : $item->id;
					$url		= './'.$controller.'/'.$relation->action.'/'.$arguments;
					$label		= UI_HTML_Tag::create( 'a', $label, array( 'href' => $url, 'class' => 'autocut' ) );
				}
				$items[]	= UI_HTML_Tag::create( 'li', $label, array( 'class' => 'autocut' ) );
			}
			if( $this->limit > 0 && $total > count( $items ) ){
				$label		= sprintf( 'und %s weitere', $total - count( $items ) );
				$items[]	= UI_HTML_Tag::create( 'li', $iconMore.'&nbsp;'.$label );
			}
			$items	= UI_HTML_Tag::create( 'ul', $items, array( 'class' => 'unstyled '.$this->tableClass ) );
			$count	= UI_HTML_Tag::create( 'small', '('.$type.')', array( 'class' => 'muted' ) );
			$list[]	= UI_HTML_Tag::create( 'h5', $relation->label.'&nbsp;'.$count ).$items;
		}
		return UI_HTML_Tag::create( 'div', $list, array( 'class' => 'item-relations' ) );
	}

	protected function renderRelationsAsTable(): string
	{
		if( !$this->relations )
			return '';
//		$roleId		= $this->env->getSession()->get( 'roleId' );
//		$fullAccess
		$acl		= $this->env->getAcl();
		$rows		= array();
		$iconMore	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-list' ) );
		foreach( $this->relations as $relation ){
			$items	= array();
			$icon	= '';
			if( !empty( $relation->icon ) )
				$icon	= UI_HTML_Tag::create( 'i', '', array( 'class' => $relation->icon ) ).'&nbsp;';
			$access		= $acl->has( $relation->controller, $relation->action );
			$total		= count( $relation->items );
			if( $this->limit > 0 )
				$relation->items	= array_slice( $relation->items, 0, $this->limit );
			foreach( $relation->items as $item ){
				$label		= $icon.$item->label;
				if( $access && $item->id ){
					$controller	= str_replace( "_", "/", strtolower( $relation->controller ) );
					$arguments	= !empty( $item->arguments ) ? join( "/", $item->arguments ) : $item->id;
					$url		= './'.$controller.'/'.$relation->action.'/'.$arguments;
					$label		= UI_HTML_Tag::create( 'a', $label, array( 'href' => $url, 'class' => 'autocut' ) );
				}
				$items[]	= UI_HTML_Tag::create( 'li', $label, array( 'class' => 'autocut' ) );
			}
			if( $this->limit > 0 && $total > count( $items ) ){
				$label		= sprintf( 'und %s weitere', $total - count( $items ) );
				$items[]	= UI_HTML_Tag::create( 'li', $iconMore.'&nbsp;'.$label );
			}

			$type	= sprintf( $this->types[$relation->type], $relation->count );
			$type	= UI_HTML_Tag::create( 'small', $type, array( 'class' => 'muted' ) );

			$items	= UI_HTML_Tag::create( 'ul', $items, array( 'class' => 'unstyled' ) );
			$count	= UI_HTML_Tag::create( 'small', '('.$relation->count.')', array( 'class' => 'muted' ) );
			$label	= UI_HTML_Tag::create( 'big', $relation->label ).'<br/>'.$type;
			$rows[]	= UI_HTML_Tag::create( 'tr', array(
				UI_HTML_Tag::create( 'td', $label, array( 'class' => 'cell-relation-label' ) ),
				UI_HTML_Tag::create( 'td', $items, array( 'class' => 'cell-relation-items' ) ),
			) );
		}
		$colgroup	= UI_HTML_Elements::ColumnGroup( "30%", "" );
		$thead		= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array(
			"Typ",
			"Verknüpfungen / Einträge",
		) ) );
		$tbody		= UI_HTML_Tag::create( 'tbody', $rows );
		$table		= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array(
			'class'		=> 'table table-striped item-relation-lister '.$this->tableClass,
		) );
		return UI_HTML_Tag::create( 'div', $table, array( 'class' => 'item-relations' ) );
	}

	/**
	 *	@todo			code style
	 */
	protected function renderTypes()
	{
		$this->types	= array();
		$this->types['entity']		= $this->labels['entities.count.label'];
		$this->types['relation']	= $this->labels['relations.count.label'];
		if( $this->labels['entities.count.hint'] )
			$this->types['entity']		=  UI_HTML_Tag::create( 'abbr', $this->types['entity'], array( 'title' => $this->labels['entities.count.hint'] ) );
		if( $this->labels['relations.count.hint'] )
			$this->types['relation']	=  UI_HTML_Tag::create( 'abbr', $this->types['relation'], array( 'title' => $this->labels['relations.count.hint'] ) );
	}
}
