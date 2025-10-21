<?php /** @noinspection ALL */

use CeusMedia\HydrogenFramework\Environment\Resource\Module\Definition as ModuleDefinition;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class View_Helper_Manage_Group_EntityRelationEditor
{
	protected WebEnvironment		$env;
	protected bool $enabled			= FALSE;
	protected string $moduleId		= '';
	protected int|string $entityId	= 0;
	protected string $from			= '';

	/**
	 *	@param		WebEnvironment		$env
	 */
	public function __construct( WebEnvironment $env )
	{
		$this->env	= $env;
	}

	/**
	 *	@param		bool		$switch
	 *	@return		self
	 */
	public function enable( bool $switch = TRUE ): self
	{
		$this->enabled	= $switch;
		return $this;
	}

	/**
	 *	@return		string
	 *	@throws		ReflectionException
	 */
	public function render(): string
	{
		if( !$this->enabled )
			return '';
		$panelGroups	= '';
		$logicUser			= Logic_User::getInstance( $this->env );
		$groupsAssigned		= Logic_GroupRelation::getInstance( $this->env )->getGroups( $this->moduleId, $this->entityId );
		$groupsAvailable	= [];

		foreach( $logicUser->getGroups( [/*'status' => Model_Group::STATUS_ENABLED*/] ) as $group )
			$groupsAvailable[$group->groupId]	= $group;

		$iconAdd		= HtmlTag::create( 'i', '', ['class' => 'fa fa-plus'] );
		$iconRemove		= HtmlTag::create( 'i', '', ['class' => 'fa fa-minus'] );
		$listGroupsAssigned		= [];
		$listGroupsAvailable	= [];
		$listGroups	= [];
		foreach( $groupsAvailable as $availableGroupId => $availableGroup ){
			if( in_array( $availableGroupId, array_keys( $groupsAssigned ) ) ){
				$buttonRemove	= HtmlTag::create( 'a', $iconRemove, [
					'href'	=> 'manage/group/relation/remove/'.$availableGroup->groupId.'/'.$this->moduleId.'/'.$this->entityId.'/'.base64_encode( $this->from ),
					'class'	=> 'btn not-btn-danger btn-mini btn-micro',
				] );
				$listGroupsAssigned[]	= '<div class="list-group">'.$buttonRemove.'&nbsp;&nbsp;'.$availableGroup->title.'</div>';
			}
			else {
				$buttonAdd	= HtmlTag::create( 'a', $iconAdd, [
					'href'	=> 'manage/group/relation/add/'.$availableGroup->groupId.'/'.$this->moduleId.'/'.$this->entityId.'/'.base64_encode( $this->from ),
					'class'	=> 'btn not-btn-success btn-mini btn-micro',
				] );
				$listGroupsAvailable[]	= '<div class="list-group">'.$buttonAdd.'&nbsp;&nbsp;'.$availableGroup->title.'</div>';
			}
		}
		$iconVisible	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-eye'] );
		$iconHidden		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-eye-slash'] );

		return '
			<div class="content-panel">
				<h3>Gruppensichtbarkeit</h3>
				<div class="content-panel-inner">
					<div class="row-fluid">
						<div class="span6">
							<h5>'.$iconVisible.'&nbsp;Sichtbar für</h5>
							'.join( '', $listGroupsAssigned ).'
						</div>
						<div class="span6">
							<h5>'.$iconHidden.'&nbsp;Unsichtbar für</h5>
							'.join( '', $listGroupsAvailable ).'
						</div>
					</div>
				</div>
			</div>';
	}

	/**
	 *	@param		int|string		$entityId
	 *	@return		self
	 */
	public function setEntityId( int|string $entityId ): self
	{
		$this->entityId = $entityId;
		return $this;
	}

	/**
	 *	@param		string		$from
	 *	@return		self
	 */
	public function setFrom( string $from ): self
	{
		$this->from	= $from;
		return $this;
	}

	/**
	 *	@param		ModuleDefinition|string		$module
	 *	@return		self
	 */
	public function setModule( ModuleDefinition|string $module ): self
	{
		$this->moduleId	= is_object( $module ) ? $module->id : $module;
		return $this;
	}
}
