<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class View_Helper_Work_Mission_Filter_Project{

	public function __construct( $env ){
		$this->env	= $env;
		$this->words	= $this->env->getLanguage()->getWords( 'work/mission' );
	}

	public function setModalRegistry( $modalRegistry ){
		$this->modalRegistry	= $modalRegistry;
	}

	public function setValues( $all, $selected ){
		$this->values	= $all;
		$this->selected	= $selected;
	}

	public function render(){
		if( empty( $this->values ) )
			return;
		$iconEye			= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-eye'] );
		$iconWarning		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-warning'] );
		$iconCheck			= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check-square-o'] );
		$changedProjects	= array_diff( array_keys( $this->values ), $this->selected );
		$list				= [];
		foreach( $this->values as $project ){
			$checkbox	= HtmlTag::create( 'input', NULL, array(
				'type'		=> 'checkbox',
				'name'		=> 'projects[]',
				'id'		=> 'project-'.$project->projectId,
				'value'		=> $project->projectId,
				'checked'	=> in_array( $project->projectId, $this->selected ) ? "checked" : NULL
			) );
			$label		= HtmlTag::create( 'label', $checkbox.'&nbsp;'.$project->title, ['class' => 'checkbox'] );
			$label		= HtmlTag::create( 'div', $label, ['class' => 'autocut'] );
			$buttons	= HtmlTag::create( 'div', array(
				HtmlTag::create( 'button', 'nur dieses', array(
					'class'		=> 'btn btn-mini trigger-select-this',
//						'disabled'	=> 'disabled',
					'data-id'	=> $project->projectId,
				) ),
				HtmlTag::create( 'button', $iconEye, array(
					'class'	=> 'btn btn-small btn-info',
					'href'	=> './manage/project/view/'.$project->projectId,
				) ),
			), ['class' => 'pull-right'] );

			$list[]	= HtmlTag::create( 'tr', array(
				HtmlTag::create( 'td', $project->priority, array(
					'class'		=> 'project priority'.$project->priority,
					'style'		=> 'text-align: center; font-weight: lighter; font-size: 1.1em;',
				) ),
				HtmlTag::create( 'td', $label, array(
					'style'		=> 'padding: 1px;',
				) ),
				HtmlTag::create( 'td', $buttons, array(
					'style'		=> 'padding: 1px;',
				) ),
			) );
		}
		$buttonAll	= HtmlTag::create( 'a', $iconCheck, array(
		//	'id'	=> 'modal-work-mission-filter-projects-all',
			'class'	=> 'trigger-select-all',
			'href'	=> '#',
		) );
		$colgroup	= HtmlElements::ColumnGroup( "30px", "", "140px" );
		$tableHeads	= HtmlElements::tableHeads( [$iconWarning, $buttonAll."&nbsp;&nbsp;Projekt", ""] );
		$thead		= HtmlTag::create( 'thead', $tableHeads );
		$tbody		= HtmlTag::create( 'tbody', $list );
		$table		= HtmlTag::create( 'table', $colgroup.$thead.$tbody, array(
			'id'	=> 'filter-work-missions-projects-list',
			'class'	=> 'table table-condensed table-fixed'
		) );

		$modal			= new View_Helper_Modal( $this->env );
		$modal->setId( 'modal-work-mission-filter-projects' );
		$modal->setHeading( 'Filter: Projekte' );
		$modal->setBody( $table );
		$modal->setFade( FALSE );
		$this->modalRegistry->register( 'workMissionFilterProjects', $modal );

		$buttonIcon		= '';
		if( $this->env->getModules()->has( 'UI_Font_FontAwesome' ) )
			$buttonIcon		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-cube'] ).'&nbsp;';
		$labelFilter	= HtmlTag::create( 'span', $this->words['filters']['project'], ['class' => 'hidden-phone'] );
		$buttonLabel	= $labelFilter.'&nbsp;<span class="caret"></span>';
		$buttonAttr		= array(
			'class'	=> 'btn '.( count( $changedProjects ) ? "btn-info" : "" ),
		);
		$modalTrigger	= new View_Helper_ModalTrigger( $this->env );
		$modalTrigger->setId( 'modal-work-mission-filter-projects-trigger' );
		$modalTrigger->setModalId( 'modal-work-mission-filter-projects' );
		$modalTrigger->setLabel( $buttonIcon.$buttonLabel );
		$modalTrigger->setAttributes( $buttonAttr );
		return $modalTrigger->render();
	}
}
