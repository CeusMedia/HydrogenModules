<?php
class View_Helper_Work_Mission_Filter{

	protected $defaultFilterValues	= array();
	protected $words;
	protected $modals	= array();

	public function __construct( $env, $defaultFilterValues, $words ){
		$this->env	= $env;
		$this->setDefaultFilterValues( $defaultFilterValues );
		$this->setWords( $words );
		$this->modalRegistry	= new View_Helper_ModalRegistry( $this->env );
	}

	/*  -- mission types  --  */
	public function renderTypeFilter( $filteredTypes ){
		$helper	= new View_Helper_Work_Mission_Filter_Type( $this->env );
		$helper->setModalRegistry( $this->modalRegistry );
		$helper->setValues( $this->defaultFilterValues['types'], $filteredTypes );
		return $helper->render();
	}

	/*  -- mission priorities  --  */
	public function renderPriorityFilter( $filteredPriorities ){
		$helper	= new View_Helper_Work_Mission_Filter_Priority( $this->env );
		$helper->setModalRegistry( $this->modalRegistry );
		$helper->setValues( $this->defaultFilterValues['priorities'], $filteredPriorities );
		return $helper->render();
	}

	/*  -- mission states  --  */
	public function renderStateFilter( $filteredStates ){
		$helper	= new View_Helper_Work_Mission_Filter_Status( $this->env );
		$helper->setModalRegistry( $this->modalRegistry );
		$helper->setValues( $this->defaultFilterValues['states'], $filteredStates );
		return $helper->render();
	}

	/*  -- mission projects  --  */
	public function renderProjectFilter( $filteredProjects, $userProjects ){
		$helper	= new View_Helper_Work_Mission_Filter_Project( $this->env );
		$helper->setModalRegistry( $this->modalRegistry );
		$helper->setValues( $userProjects, $filteredProjects );
		return $helper->render();
	}

	public function renderWorkerFilter( $filteredWorkers, $workers ){
		$helper	= new View_Helper_Work_Mission_Filter_Worker( $this->env );
		$helper->setModalRegistry( $this->modalRegistry );
		$helper->setValues( $workers, $filteredWorkers );
		return $helper->render();
	}

	public function renderModals(){
		return $this->modalRegistry->render();
	}

	/*  -- query search  --  */
	public function renderSearch( $filteredQuery ){
		$inputSearch	= UI_HTML_Tag::create( 'input', NULL, array(
			'type'			=> "text",
			'name'			=> "query",
			'id'			=> "filter_query",
			'class'			=> 'span2 '.( $filteredQuery ? 'changed' : '' ),
			'value'			=> htmlentities( $filteredQuery, ENT_QUOTES, 'UTF-8' ),
			'placeholder'	=> $this->words['index']['labelQuery'],
		) );

		$label				= '<i class="icon-search '.( $filteredQuery ? 'icon-white' : '' ).'"></i>';
		$buttonSearch	= UI_HTML_Tag::create( 'button', $label, array(
			'type'		=> "button",
			'class'		=> 'btn '.( $filteredQuery ? 'btn-info' : '' ),
			'id'		=> 'button_filter_search'
		) );
		return $inputSearch.$buttonSearch;
	}

	public function renderReset(){
		$label				= '<i class="icon-remove-circle"></i>';
		$buttonSearchReset	= UI_HTML_Tag::create( 'button', $label, array(
			'type'				=> "button",
			'disabled'			=> "disabled",/*$changedFilters ? NULL : "disabled",*/
			'class'				=> 'btn',/*'btn '.( $changedFilters ? 'btn-inverse' : "" ),*/
			'id'				=> 'button_filter_reset',					//  remove query only: 'button_filter_search_reset',
			'title'				=> 'alle Filter zurücksetzen',
		) );
		return $buttonSearchReset;
	}

	public function renderViewTypeSwitch( $mode ){
		$caret	= UI_HTML_Tag::create( 'span', '', array( 'class' => 'caret' ) );
		$items	= array();

		$wordsViewTypes	= (object) $this->words['viewTypes'];

		$current	= '';
		$hasFontAwesome	= $this->env->getModules()->has( 'UI_Font_FontAwesome' );
		foreach( $this->words['viewTypes'] as $typeKey => $typeLabel ){
			$iconClass		= NULL;
			$currentModes	= array( $typeKey );
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
					$currentModes	= array( 'now', 'archive', 'future' );
					break;
			}
			$icon		= $iconClass ? UI_HTML_Tag::create( 'i', '', array( 'class' => $iconClass ) ).'&nbsp;' : '';
			$link		= UI_HTML_Tag::create( 'a', $icon.$typeLabel, array( 'href' => './work/mission/'.$typeKey ) );
			$class		= in_array( $mode, $currentModes ) ? 'active' : NULL;
			$current	= in_array( $mode, $currentModes ) ? $typeLabel : $current;
			$items[]	= UI_HTML_Tag::create( 'li', $link, array( 'class' => $class ) );
		}

		$labelFilter	= $this->words['filters']['viewType'];
		return UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'button', '<span class="not-muted">'.$labelFilter.':</span> <b>'.$current.'</b>', array( 'class' => 'btn dropdown-toggle', 'data-toggle' => "dropdown" ) ),
	//		UI_HTML_Tag::create( 'button', $caret, array( 'class' => 'btn dropdown-toggle', 'data-toggle' => "dropdown" ) ),
			UI_HTML_Tag::create( 'ul', $items, array( 'class' => 'dropdown-menu' ) ),
		), array( 'class' => 'btn-group' ) );
	}

	public function renderViewModeSwitch( $mode ){
		if( !in_array( $mode, array( 'archive', 'now', 'future' ) ) )
			return "";
		$caret	= UI_HTML_Tag::create( 'span', '', array( 'class' => 'caret' ) );
		$items	= array();

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
			$icon		= $iconClass ? UI_HTML_Tag::create( 'i', '', array( 'class' => $iconClass ) ).'&nbsp;' : '';
			$link		= UI_HTML_Tag::create( 'a', $icon.$modeLabel, array( 'href' => './work/mission/'.$modeKey ) );
			$items[]	= UI_HTML_Tag::create( 'li', $link, array( 'class' => $mode === $modeKey ? 'active' : NULL ) );
			$current	= $mode === $modeKey ? $modeLabel : $current;
		}
		$labelFilter	= $this->words['filters']['modeType'];
		return UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'button', '<span class="not-muted">'.$labelFilter.':</span> <b>'.$current.'</b>', array( 'class' => 'btn dropdown-toggle', 'data-toggle' => "dropdown" ) ),
//			UI_HTML_Tag::create( 'button', $caret, array( 'class' => 'btn dropdown-toggle', 'data-toggle' => "dropdown" ) ),
			UI_HTML_Tag::create( 'ul', $items, array( 'class' => 'dropdown-menu' ) ),
		), array( 'class' => 'btn-group' ) );
	}

	public function setDefaultFilterValues( $defaultFilterValues ){
		$this->defaultFilterValues	= $defaultFilterValues;
	}

	public function setWords( $words ){
		$this->words	= $words;
	}
}
?>
