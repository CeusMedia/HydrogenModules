<?php
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
		$iconEye			= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-eye' ) );
		$iconWarning		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-warning' ) );
		$iconCheck			= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check-square-o' ) );
		$changedProjects	= array_diff( array_keys( $this->values ), $this->selected );
		$list				= array();
		foreach( $this->values as $project ){
			$checkbox	= UI_HTML_Tag::create( 'input', NULL, array(
				'type'		=> 'checkbox',
				'name'		=> 'projects[]',
				'id'		=> 'project-'.$project->projectId,
				'value'		=> $project->projectId,
				'checked'	=> in_array( $project->projectId, $this->selected ) ? "checked" : NULL
			) );
			$label		= UI_HTML_Tag::create( 'label', $checkbox.'&nbsp;'.$project->title, array( 'class' => 'checkbox' ) );
			$label		= UI_HTML_Tag::create( 'div', $label, array( 'class' => 'autocut' ) );
			$buttons	= UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'button', 'nur dieses', array(
					'class'		=> 'btn btn-mini trigger-select-this',
//						'disabled'	=> 'disabled',
					'data-id'	=> $project->projectId,
				) ),
				UI_HTML_Tag::create( 'button', $iconEye, array(
					'class'	=> 'btn btn-small btn-info',
					'href'	=> './manage/project/view/'.$project->projectId,
				) ),
			), array( 'class' => 'pull-right' ) );

			$list[]	= UI_HTML_Tag::create( 'tr', array(
				UI_HTML_Tag::create( 'td', $project->priority, array(
					'class'		=> 'project priority'.$project->priority,
					'style'		=> 'text-align: center; font-weight: lighter; font-size: 1.1em;',
				) ),
				UI_HTML_Tag::create( 'td', $label, array(
					'style'		=> 'padding: 1px;',
				) ),
				UI_HTML_Tag::create( 'td', $buttons, array(
					'style'		=> 'padding: 1px;',
				) ),
			) );
		}
		$buttonAll	= UI_HTML_Tag::create( 'a', $iconCheck, array(
		//	'id'	=> 'modal-work-mission-filter-projects-all',
			'class'	=> 'trigger-select-all',
			'href'	=> '#',
		) );
		$colgroup	= UI_HTML_Elements::ColumnGroup( "30px", "", "140px" );
		$tableHeads	= UI_HTML_Elements::tableHeads( array( $iconWarning, $buttonAll."&nbsp;&nbsp;Projekt", "" ) );
		$thead		= UI_HTML_Tag::create( 'thead', $tableHeads );
		$tbody		= UI_HTML_Tag::create( 'tbody', $list );
		$table		= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array(
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
			$buttonIcon		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-cube' ) ).'&nbsp;';
		$labelFilter	= $this->words['filters']['project'];
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
