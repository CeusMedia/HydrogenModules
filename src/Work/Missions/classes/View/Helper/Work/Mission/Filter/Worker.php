<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

class View_Helper_Work_Mission_Filter_Worker
{
	protected WebEnvironment $env;
	protected array $words;
	protected ?View_Helper_ModalRegistry $modalRegistry		= NULL;
	protected array $values									= [];
	protected array $selected								= [];
	protected ?string $userId								= NULL;

	public function __construct( WebEnvironment $env )
	{
		$this->env		= $env;
		$this->words	= $this->env->getLanguage()->getWords( 'work/mission' );
		$this->userId	= $this->env->getSession()->get( 'auth_user_id' );
	}

	public function setModalRegistry( View_Helper_ModalRegistry $modalRegistry ): self
	{
		$this->modalRegistry	= $modalRegistry;
		return $this;
	}

	public function setValues( array $all, array $selected ): self
	{
		$this->values	= $all;
		$this->selected	= $selected;
		return $this;
	}

	public function render(): string
	{
		if( empty( $this->values ) )
			return '';
		$helperMember		= new View_Helper_Member( $this->env );
//		$helperMember->setMode( 'bar' );
		$iconEye			= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-eye'] );
		$iconWarning		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-warning'] );
		$iconCheck			= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check-square-o'] );
		$changedWorkers	= array_diff( array_keys( $this->values ), $this->selected );
		$list	= [];
		foreach( $this->values as $worker ){
			$checkbox	= HtmlTag::create( 'input', NULL, [
				'type'		=> 'checkbox',
				'name'		=> 'workers[]',
				'id'		=> 'worker-'.$worker->userId,
				'value'		=> $worker->userId,
				'checked'	=> in_array( $worker->userId, $this->selected ) ? "checked" : NULL
			] );
//			$label		= HtmlTag::create( 'label', $checkbox.'&nbsp;'.$worker->username, ['class' => 'checkbox'] );

			$helperMember->setUser( $worker );
			$label		= $helperMember->render();
			$label		= HtmlTag::create( 'label', $checkbox.'&nbsp;'.$label, [
				'class'	=> 'checkbox',
				'for'	=> 'worker-'.$worker->userId,
				'style'	=> 'margin-left: 6px;',
			] );

			$buttons	= HtmlTag::create( 'div', [
				HtmlTag::create( 'button', 'nur dieser', [
					'class'		=> 'btn btn-mini trigger-select-this',
//						'disabled'	=> 'disabled',
					'data-id'	=> $worker->userId,
				] ),
				HtmlTag::create( 'button', $iconEye, [
					'class'	=> 'btn btn-small btn-info',
					'href'	=> './member/view/'.$worker->userId,
				] ),
			], ['class' => 'pull-right'] );

			$list[]	= HtmlTag::create( 'tr', [
				HtmlTag::create( 'td', $label, [
					'style'		=> 'padding: 1px;',
				] ),
				HtmlTag::create( 'td', $buttons, [
					'style'		=> 'padding: 1px;',
				] ),
			] );
		}

		$buttonAll	= HtmlTag::create( 'a', $iconCheck, [
		//	'id'	=> 'modal-work-mission-filter-workers-all',
			'class'	=> 'trigger-select-all',
			'href'	=> '#',
		] );

		$buttonUser	= HtmlTag::create( 'button', '<i class="fa fa-fw fa-user"></i>&nbsp;<b>nur ich</b>', [
			'class'		=> 'btn btn-small trigger-select-this',
			'data-id'	=> $this->userId,
		] );
		$buttonAll	= HtmlTag::create( 'button', '<i class="fa fa-fw fa-users"></i>&nbsp;<b>alle</b>', [
			'class'		=> 'btn btn-small trigger-select-all',
			'href'		=> '#',
		] );
		$buttons	= HtmlTag::create( 'div', [$buttonUser, $buttonAll], ['class' => 'btn-group'] );
		$colgroup	= HtmlElements::ColumnGroup( "", "160px" );
		$tableHeads	= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'th', "Bearbeiter" ),
			HtmlTag::create( 'th', $buttons, array( 'style' => 'text-align: right') )
		) );
		$thead		= HtmlTag::create( 'thead', $tableHeads );
		$tbody		= HtmlTag::create( 'tbody', $list );
		$table		= HtmlTag::create( 'table', $colgroup.$thead.$tbody, [
			'id'	=> 'filter-work-missions-workers-list',
			'class'	=> 'table table-condensed table-fixed'
		] );

		$modal			= new View_Helper_Modal( $this->env );
		$modal->setId( 'modal-work-mission-filter-workers' );
		$modal->setHeading( 'Filter: Bearbeiter' );
		$modal->setBody( $table );
		$modal->setFade( FALSE );
		if( NULL !== $this->modalRegistry )
			$this->modalRegistry->register( 'workMissionFilterWorkers', $modal );

		$buttonIcon		= '';
		if( $this->env->getModules()->has( 'UI_Font_FontAwesome' ) )
			$buttonIcon		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-user'] ).'&nbsp;';
		$labelFilter	= HtmlTag::create( 'span', $this->words['filters']['worker'], ['class' => 'hidden-phone'] );
		$buttonLabel	= $labelFilter.'&nbsp;<span class="caret"></span>';
		$buttonAttr		= [
			'class'	=> 'btn '.( count( $changedWorkers ) ? "btn-info" : "" ),
		];
		$modalTrigger	= new View_Helper_ModalTrigger( $this->env );
		$modalTrigger->setId( 'modal-work-mission-filter-workers-trigger' );
		$modalTrigger->setModalId( 'modal-work-mission-filter-workers' );
		$modalTrigger->setLabel( $buttonIcon.$buttonLabel );
		$modalTrigger->setAttributes( $buttonAttr );
		return $modalTrigger->render();
	}
}
