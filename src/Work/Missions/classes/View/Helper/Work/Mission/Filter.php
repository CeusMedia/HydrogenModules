<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

class View_Helper_Work_Mission_Filter
{
	protected WebEnvironment $env;
	protected array $defaultFilterValues	= [];
	protected array $words;
	protected array $modals	= [];
	protected View_Helper_ModalRegistry $modalRegistry;

	public function __construct( WebEnvironment $env, $defaultFilterValues, $words )
	{
		$this->env	= $env;
		$this->setDefaultFilterValues( $defaultFilterValues );
		$this->setWords( $words );
		$this->modalRegistry	= new View_Helper_ModalRegistry( $this->env );
	}

	/*  -- mission types  --  */
	public function renderTypeFilter( array $filteredTypes ): string
	{
		$helper	= new View_Helper_Work_Mission_Filter_Type( $this->env );
		$helper->setModalRegistry( $this->modalRegistry );
		$helper->setValues( $this->defaultFilterValues['types'], $filteredTypes );
		return $helper->render();
	}

	/*  -- mission priorities  --  */
	public function renderPriorityFilter( array $filteredPriorities ): string
	{
		$helper	= new View_Helper_Work_Mission_Filter_Priority( $this->env );
		$helper->setModalRegistry( $this->modalRegistry );
		$helper->setValues( $this->defaultFilterValues['priorities'], $filteredPriorities );
		return $helper->render();
	}

	/*  -- mission states  --  */
	public function renderStateFilter( array $filteredStates ): string
	{
		$helper	= new View_Helper_Work_Mission_Filter_Status( $this->env );
		$helper->setModalRegistry( $this->modalRegistry );
		$helper->setValues( $this->defaultFilterValues['states'], $filteredStates );
		return $helper->render();
	}

	/*  -- mission projects  --  */
	public function renderProjectFilter( array $filteredProjects, array $userProjects ): string
	{
		$helper	= new View_Helper_Work_Mission_Filter_Project( $this->env );
		$helper->setModalRegistry( $this->modalRegistry );
		$helper->setValues( $userProjects, $filteredProjects );
		return $helper->render();
	}

	public function renderWorkerFilter( array $filteredWorkers, array $workers ): string
	{
		$helper	= new View_Helper_Work_Mission_Filter_Worker( $this->env );
		$helper->setModalRegistry( $this->modalRegistry );
		$helper->setValues( $workers, $filteredWorkers );
		return $helper->render();
	}

	public function renderModals(): string
	{
		return $this->modalRegistry->render();
	}

	/*  -- query search  --  */
	public function renderSearch( ?string $filteredQuery = NULL ): string
	{
		$inputSearch	= HtmlTag::create( 'input', NULL, [
			'type'			=> "text",
			'name'			=> "query",
			'id'			=> "filter_query",
			'class'			=> 'span2 '.( $filteredQuery ? 'changed' : '' ),
			'value'			=> htmlentities( $filteredQuery ?? '', ENT_QUOTES, 'UTF-8' ),
			'placeholder'	=> $this->words['index']['labelQuery'],
		] );

		$label				= '<i class="icon-search '.( $filteredQuery ? 'icon-white' : '' ).'"></i>';
		$buttonSearch	= HtmlTag::create( 'button', $label, [
			'type'		=> "button",
			'class'		=> 'btn '.( $filteredQuery ? 'btn-info' : '' ),
			'id'		=> 'button_filter_search'
		] );
		return $inputSearch.$buttonSearch;
	}

	public function renderReset(): string
	{
		$label				= '<i class="icon-remove-circle"></i>';
		$buttonSearchReset	= HtmlTag::create( 'button', $label, [
			'type'				=> "button",
			'disabled'			=> "disabled",/*$changedFilters ? NULL : "disabled",*/
			'class'				=> 'btn',/*'btn '.( $changedFilters ? 'btn-inverse' : "" ),*/
			'id'				=> 'button_filter_reset',					//  remove query only: 'button_filter_search_reset',
			'title'				=> 'alle Filter zurücksetzen',
		] );
		return $buttonSearchReset;
	}

	public function renderViewTypeSwitch( string $mode ): string
	{
		$caret	= HtmlTag::create( 'span', '', ['class' => 'caret'] );
		$items	= [];

		$wordsViewTypes	= (object) $this->words['viewTypes'];

		$current	= '';
		$hasFontAwesome	= $this->env->getModules()->has( 'UI_Font_FontAwesome' );
		foreach( $this->words['viewTypes'] as $typeKey => $typeLabel ){
			$iconClass		= NULL;
			$currentModes	= [$typeKey];
			switch( $typeKey ){
				case 'calendar':
					$iconClass	= $hasFontAwesome ? 'fa fa-fw fa-calendar' : 'icon-calendar';
					break;
				case 'kanban':
					$iconClass	= $hasFontAwesome ? 'fa fa-fw fa-thumb-tack' : 'icon-note';
					break;
				case 'gantt':
					$iconClass	= $hasFontAwesome ? 'fa fa-fw fa-signal fa-rotate-90' : 'not-icon-any';
					break;
				case 'now':
				case 'archive':
				case 'future':
					$iconClass	= $hasFontAwesome ? 'fa fa-fw fa-list' : 'icon-wrench';
					$currentModes	= ['now', 'archive', 'future'];
					break;
			}
			$icon		= $iconClass ? HtmlTag::create( 'i', '', ['class' => $iconClass] ).'&nbsp;' : '';
			$link		= HtmlTag::create( 'a', $icon.$typeLabel, ['href' => './work/mission/'.$typeKey] );
			$class		= in_array( $mode, $currentModes, TRUE ) ? 'active' : NULL;
			$current	= in_array( $mode, $currentModes, TRUE ) ? $typeLabel : $current;
			$items[]	= HtmlTag::create( 'li', $link, ['class' => $class] );
		}

		$labelFilter	= $this->words['filters']['viewType'];
		return HtmlTag::create( 'div', [
			HtmlTag::create( 'button', '<span class="not-muted">'.$labelFilter.':</span> <b>'.$current.'</b>', ['class' => 'btn dropdown-toggle', 'data-toggle' => "dropdown"] ),
	//		HtmlTag::create( 'button', $caret, ['class' => 'btn dropdown-toggle', 'data-toggle' => "dropdown"] ),
			HtmlTag::create( 'ul', $items, ['class' => 'dropdown-menu'] ),
		], ['class' => 'btn-group'] );
	}

	public function renderViewModeSwitch( string $mode ): string
	{
		if( !in_array( $mode, ['archive', 'now', 'future'], TRUE ) )
			return '';
		$caret	= HtmlTag::create( 'span', '', ['class' => 'caret'] );
		$items	= [];

		$wordsViewTypes	= (object) $this->words['modeTypes'];

		$current	= '';
		$hasFontAwesome	= $this->env->getModules()->has( 'UI_Font_FontAwesome' );
		foreach( $this->words['modeTypes'] as $modeKey => $modeLabel ){
			$iconClass	= NULL;
			switch( $modeKey ){
				case 'archive':
					$iconClass	= $hasFontAwesome ? 'fa fa-fw fa-arrow-left' : 'icon-arrow-left';
					$url		= "...";
					$id			= "work-mission-view-mode-archive";
					break;
				case 'now':
					$iconClass	= $hasFontAwesome ? 'fa fa-fw fa-star' : 'icon-star';
					$url		= "...";
					$id			= "work-mission-view-mode-now";
					break;
				case 'future':
					$iconClass	= $hasFontAwesome ? 'fa fa-fw fa-arrow-right' : 'icon-arrow-right';
					$url		= "...";
					$id			= "work-mission-view-mode-future";
					break;
			}
			$icon		= $iconClass ? HtmlTag::create( 'i', '', ['class' => $iconClass] ).'&nbsp;' : '';
			$link		= HtmlTag::create( 'a', $icon.$modeLabel, ['href' => './work/mission/'.$modeKey] );
			$items[]	= HtmlTag::create( 'li', $link, ['class' => $mode === $modeKey ? 'active' : NULL] );
			$current	= $mode === $modeKey ? $modeLabel : $current;
		}
		$labelFilter	= $this->words['filters']['modeType'];
		return HtmlTag::create( 'div', [
			HtmlTag::create( 'button', '<span class="not-muted">'.$labelFilter.':</span> <b>'.$current.'</b>', ['class' => 'btn dropdown-toggle', 'data-toggle' => "dropdown"] ),
//			HtmlTag::create( 'button', $caret, ['class' => 'btn dropdown-toggle', 'data-toggle' => "dropdown"] ),
			HtmlTag::create( 'ul', $items, ['class' => 'dropdown-menu'] ),
		], ['class' => 'btn-group'] );
	}

	public function setDefaultFilterValues( $defaultFilterValues ): self
	{
		$this->defaultFilterValues	= $defaultFilterValues;
		return $this;
	}

	public function setWords( array $words ): self
	{
		$this->words	= $words;
		return $this;
	}
}
