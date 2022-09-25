<?php

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_ItemRelationLister
{
	protected $env;
	protected $relations;
	protected $hookResource;
	protected $hookEvent;
	protected $hookIndices				= [];
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
	protected $labels;

	public function __construct( Environment $env )
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
			$data->list	= [];
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
		return $this;
	}

	public function setHintTextForRelations( string $text ): self
	{
		$this->labels['relations.count.hint']	= $text;
		return $this;
	}

	public function setHook( string $resource, string $event, array $indices ): self
	{
		$this->hookResource		= $resource;
		$this->hookEvent		= $event;
		$this->hookIndices		= $indices;
		$this->relations		= NULL;
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
//		$roleId		= $this->env->getSession()->get( 'auth_role_id' );
//		$fullAccess
		$acl		= $this->env->getAcl();
		$list		= [];
		$iconMore	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-list' ) );
		foreach( $this->relations as $relation ){
			$type	= sprintf( $this->types[$relation->type], $relation->count );
			$items	= [];
			$icon	= '';
			if( !empty( $relation->icon ) )
				$icon	= HtmlTag::create( 'i', '', array( 'class' => $relation->icon ) ).'&nbsp;';
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
					$label		= HtmlTag::create( 'a', $label, array( 'href' => $url, 'class' => 'autocut' ) );
				}
				$items[]	= HtmlTag::create( 'li', $label, array( 'class' => 'autocut' ) );
			}
			if( $this->limit > 0 && $total > count( $items ) ){
				$label		= sprintf( 'und %s weitere', $total - count( $items ) );
				$items[]	= HtmlTag::create( 'li', $iconMore.'&nbsp;'.$label );
			}
			$items	= HtmlTag::create( 'ul', $items, array( 'class' => 'unstyled '.$this->tableClass ) );
			$count	= HtmlTag::create( 'small', '('.$type.')', array( 'class' => 'muted' ) );
			$list[]	= HtmlTag::create( 'h5', $relation->label.'&nbsp;'.$count ).$items;
		}
		return HtmlTag::create( 'div', $list, array( 'class' => 'item-relations' ) );
	}

	protected function renderRelationsAsTable(): string
	{
		if( !$this->relations )
			return '';
//		$roleId		= $this->env->getSession()->get( 'auth_role_id' );
//		$fullAccess
		$acl		= $this->env->getAcl();
		$rows		= [];
		$iconMore	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-list' ) );
		foreach( $this->relations as $relation ){
			$items	= [];
			$icon	= '';
			if( !empty( $relation->icon ) )
				$icon	= HtmlTag::create( 'i', '', array( 'class' => $relation->icon ) ).'&nbsp;';
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
					$label		= HtmlTag::create( 'a', $label, array( 'href' => $url, 'class' => 'autocut' ) );
				}
				$items[]	= HtmlTag::create( 'li', $label, array( 'class' => 'autocut' ) );
			}
			if( $this->limit > 0 && $total > count( $items ) ){
				$label		= sprintf( 'und %s weitere', $total - count( $items ) );
				$items[]	= HtmlTag::create( 'li', $iconMore.'&nbsp;'.$label );
			}

			$type	= sprintf( $this->types[$relation->type], $relation->count );
			$type	= HtmlTag::create( 'small', $type, array( 'class' => 'muted' ) );

			$items	= HtmlTag::create( 'ul', $items, array( 'class' => 'unstyled' ) );
			$count	= HtmlTag::create( 'small', '('.$relation->count.')', array( 'class' => 'muted' ) );
			$label	= HtmlTag::create( 'big', $relation->label ).'<br/>'.$type;
			$rows[]	= HtmlTag::create( 'tr', array(
				HtmlTag::create( 'td', $label, array( 'class' => 'cell-relation-label' ) ),
				HtmlTag::create( 'td', $items, array( 'class' => 'cell-relation-items' ) ),
			) );
		}
		$colgroup	= HtmlElements::ColumnGroup( "30%", "" );
		$thead		= HtmlTag::create( 'thead', HtmlElements::TableHeads( array(
			"Typ",
			"Verknüpfungen / Einträge",
		) ) );
		$tbody		= HtmlTag::create( 'tbody', $rows );
		$table		= HtmlTag::create( 'table', $colgroup.$thead.$tbody, array(
			'class'		=> 'table table-striped item-relation-lister '.$this->tableClass,
		) );
		return HtmlTag::create( 'div', $table, array( 'class' => 'item-relations' ) );
	}

	/**
	 *	@todo			code style
	 */
	protected function renderTypes()
	{
		$this->types	= [];
		$this->types['entity']		= $this->labels['entities.count.label'];
		$this->types['relation']	= $this->labels['relations.count.label'];
		if( $this->labels['entities.count.hint'] )
			$this->types['entity']		=  HtmlTag::create( 'abbr', $this->types['entity'], array( 'title' => $this->labels['entities.count.hint'] ) );
		if( $this->labels['relations.count.hint'] )
			$this->types['relation']	=  HtmlTag::create( 'abbr', $this->types['relation'], array( 'title' => $this->labels['relations.count.hint'] ) );
	}
}
