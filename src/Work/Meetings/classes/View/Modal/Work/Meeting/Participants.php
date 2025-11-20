<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Modal_Work_Meeting_Participants extends View_Helper_Bootstrap_Modal
{
	public View_Helper_Bootstrap_Modal_Trigger $trigger;

	protected ?Entity_Work_Meeting $meeting		= NULL;
	protected array $groups						= [];
	protected array $roles						= [];
	protected array $users						= [];
	protected array $moduleWords;

	public function __construct( Environment $env )
	{
		parent::__construct( $env );
		$this->moduleWords	= $this->env->getLanguage()->getWords( 'work/meeting' );

		$iconCancel	= HtmlTag::create( 'i', '', ['class' => "fa fa-fw fa-arrow-left"] ).'&nbsp;';
		$iconSave	= HtmlTag::create( 'i', '', ['class' => "fa fa-fw fa-check"] ).'&nbsp;';

		$w	= (object) $this->moduleWords['modal-participants'];
		$this
			->setId( 'meeting-participants-add' )
			->setHeading( $w->heading )
			->setButtonLabelSubmit( $iconSave.'&nbsp;'.$w->buttonSave )
			->setButtonLabelCancel( $iconCancel.'&nbsp;'.$w->buttonCancel );

		$this->trigger	= new View_Helper_Bootstrap_Modal_Trigger( $env );
		$this->trigger->setModalId( $this->id );
		$this->trigger->setLabel( $w->buttonTrigger );
	}

	/**
	 *	@param		Entity_Work_Meeting	$meeting
	 *	@return		self
	 */
	public function setMeeting( Entity_Work_Meeting $meeting ): self
	{
		$this->meeting	= $meeting;
		$this->setFormAction( 'work/meeting/addParticipants/'.$this->meeting->meetingId );
		return $this;
	}

	/**
	 *	@param		array				$groups
	 *	@return		self
	 */
	public function setGroups( array $groups = [] ): self
	{
		$this->groups	= $groups;
		return $this;
	}

	/**
	 *	@param		array				$roles
	 *	@return		self
	 */
	public function setRoles( array $roles = [] ): self
	{
		$this->roles	= $roles;
		return $this;
	}

	/**
	 *	@param		array				$users
	 *	@return		self
	 */
	public function setUsers( array $users = [] ): self
	{
		$this->users	= $users;
		return $this;
	}

	/**
	 *	@return		string
	 */
	protected function renderListOfGroups(): string
	{
		$list	= [];
		foreach( $this->groups as $group ){
			if( 0 === $group->nrUsers )
				continue;
			$input	= HtmlTag::create( 'input', NULL, [
				'type'	=> 'checkbox',
				'name'	=> 'groupIds[]',
				'value'	=> $group->groupId,
			] );
			$nr			= HtmlTag::create( 'small', '('.$group->nrUsers.')', ['class' => 'muted'] );
			$label		= HtmlTag::create( 'label', $input.'&nbsp;'.$group->title.'&nbsp;'.$nr, ['class' => 'checkbox'] );
			$list[$group->title]		= HtmlTag::create( 'li', $label );
		}
		if( [] === $list )
			return '';

		uksort( $list, 'strnatcasecmp' );
		return HtmlTag::create( 'ul', $list, ['class' => 'unstyled'] );
	}

	/**
	 *	@return		string
	 */
	protected function renderListOfRoles(): string
	{
		$list	= [];
		foreach( $this->roles as $role ){
			if( 0 === $role->nrUsers )
				continue;
			$input	= HtmlTag::create( 'input', NULL, [
				'type'	=> 'checkbox',
				'name'	=> 'roleIds[]',
				'value'	=> $role->roleId,
			] );
			$nr			= HtmlTag::create( 'small', '('.$role->nrUsers.')', ['class' => 'muted'] );
			$label		= HtmlTag::create( 'label', $input.'&nbsp;'.$role->title.'&nbsp;'.$nr, ['class' => 'checkbox'] );
			$list[$role->title]		= HtmlTag::create( 'li', $label );
		}
		if( [] === $list )
			return '';

		uksort( $list, 'strnatcasecmp' );
		return HtmlTag::create( 'ul', $list, ['class' => 'unstyled'] );
	}

	/**
	 *	@return		string
	 */
	protected function renderListOfUsers(): string
	{
		$list	= [];
		foreach( $this->users as $user ){
			$input	= HtmlTag::create( 'input', NULL, [
				'type'	=> 'checkbox',
				'name'	=> 'userIds[]',
				'value'	=> $user->userId,
			] );
			$label		= HtmlTag::create( 'label', $input.'&nbsp;'.$user->username, ['class' => 'checkbox'] );
			$list[$user->username]		= HtmlTag::create( 'li', $label );
		}
		if( [] === $list )
			return '';

		uksort( $list, 'strnatcasecmp' );
		return HtmlTag::create( 'ul', $list, ['class' => 'unstyled'] );
	}

	/**
	 *	@return		string
	 */
	public function render(): string
	{
		$this->body	= $this->renderForm();
		return parent::render();
	}

	/**
	 *	@return		string
	 */
	public function renderForm(): string
	{
		$w		= (object) $this->moduleWords['modal-participants'];
		$listRoles	= $this->renderListOfRoles();
		$listGroups	= $this->renderListOfGroups();
		$listUsers	= $this->renderListOfUsers();

		$optionByRoles	= '';
		if( '' !== $listRoles )
			$optionByRoles	= '
			<div class="span4">
				<label class="radio">
					<input type="radio" name="source" value="roles" class="has-optionals" checked/>
					'.$w->labelSourceRoles.'
				</label>
			</div>';

		$optionByGroups	= '';
		if( '' !== $listGroups )
			$optionByGroups	= '
			<div class="span4">
				<label class="radio">
					<input type="radio" name="source" value="groups" class="has-optionals"/>
					'.$w->labelSourceGroups.'
				</label>
			</div>';

		$optionByUsers	= '';
		if( '' !== $listUsers )
			$optionByUsers	= '
			<div class="span4">
				<label class="radio">
					<input type="radio" name="source" value="users" class="has-optionals"/>
					'.$w->labelSourceUsers.'
				</label>
			</div>';

		$useRoles	= $this->env->getModules()->get( 'Work_Meetings' )->getConfigAsDictionary()->get( 'useRoles' );

		$fieldRole	= '';
		if( $useRoles ){
			$optType	= HtmlElements::Options( $this->moduleWords['types'], (string) Model_Work_Meeting_Participant::TYPE_CONTRIBUTOR );
			$fieldRole	= '
		<div class="row-fluid">
			<div class="span4">
				<label for="input_type">'.$w->labelRole.'</label>
				<select name="type" id="input_type">'.$optType.'</select>
			</div>
		</div>';
		}
		$form		= '
<!--		<input type="hidden" name="meetingId" value="'.$this->meeting->meetingId.'"/>-->
		'.$fieldRole.'
		<div class="row-fluid">
			<label>'.$w->labelSelectSource.'</label>
			'.$optionByRoles.'
			'.$optionByGroups.'
			'.$optionByUsers.'
		</div>
		<div class="row-fluid optional source source-roles">
			<label>'.$w->headingRoleUsers.'</label>
			<div class="boxed boxed-small">
				'.$listRoles.'
			</div>
		</div>
		<div class="row-fluid optional source source-groups">
			<label>'.$w->headingGroupUsers.'</label>
			<div class="boxed boxed-small">
				'.$listGroups.'
			</div>
		</div>
		<div class="row-fluid optional source source-users">
			<label>'.$w->headingUsers.'</label>
			<div class="boxed boxed-small">
				'.$listUsers.'
			</div>
		</div>
		';
		return $form;
	}
}
