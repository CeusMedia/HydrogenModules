<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View\Helper\Abstraction;

class View_Helper_Work_Time_Modal_Assign extends Abstraction
{
	protected array $timers			= [];
	protected int|string|NULL $userId		= NULL;
	protected ?object $relation		= NULL;
	protected ?string $from			= NULL;

	/**
	 *	@param		Environment		$env
	 */
	public function __construct( Environment $env )
	{
		$this->env	= $env;
	}

	/**
	 *	@return		string
	 */
	public function render(): string
	{
		$words		= $this->env->getLanguage()->getWords( 'work/time' );
		$w			= (object) $words['assign'];

		if( !$this->relation )
			throw new RuntimeException( 'No module relation set' );

		if( $this->timers )
			$timers	= $this->timers;
		else{
			$modelTimer	= new Model_Work_Timer( $this->env );

			$conditions	= array( 'moduleId' => 0);
			if( $this->userId )
				$conditions['userId']	= $this->userId;
			$orders		= ['title' => 'ASC'];
			$timers		= $modelTimer->getAll( $conditions, $orders );
		}


		$listTimers		= [];
		foreach( $timers as $timer ){
			$checkbox		= HtmlTag::create( 'input', NULL, ['type' => 'checkbox', 'name' => 'timerIds[]', 'value' => $timer->workTimerId] );
			$label			= HtmlTag::create( 'label', $checkbox.'&nbsp;'.$timer->title, ['class' => 'checkbox'] );
			$listTimers[]	= HtmlTag::create( 'li', $label );
		}
		$listTimers		= HtmlTag::create( 'ul', $listTimers, ['class' => 'unstyled'] );

		return '
<form action="./work/time/assign" method="post">
	<input type="hidden" name="module" value="'.$this->relation->module.'"/>
	<input type="hidden" name="moduleId" value="'.$this->relation->moduleId.'"/>
	<input type="hidden" name="from" value="'.$this->from.'"/>
	<div id="myModalWorkTimeAssign" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			<h3 id="myModalLabel">'.$w->heading.'</h3>
		</div>
		<div class="modal-body">
			<div class="row-fluid">
				<div class="span12">
					'.$listTimers.'
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<button type="button" class="btn" data-dismiss="modal" aria-hidden="true"><i class="icon-arrow-left"></i>&nbsp;'.$w->buttonCancel.'</button>
			<button type="submit" name="save" class="btn btn-primary"><i class="icon-ok icon-white"></i>&nbsp;'.$w->buttonSave.'</button>
		</div>
	</div>
</form>
';
	}

	/**
	 *	@param		string		$from
	 *	@return		self
	 */
	public function setFrom( string $from ): self
	{
		$this->from		= $from;
		return $this;
	}

	/**
	 *	@param		string		$module
	 *	@param		string		$moduleId
	 *	@return		self
	 */
	public function setRelation( string $module, string $moduleId ): self
	{
		$this->relation	= (object) [
			'module'	=> $module,
			'moduleId'	=> $moduleId,
		];
		return $this;
	}

	/**
	 *	@param		array		$timers
	 *	@return		self
	 */
	public function setTimers( array $timers ): self
	{
		$this->timers	= $timers;
		return $this;
	}

	/**
	 *	@param		int|string		$userId
	 *	@return		self
	 */
	public function setUserId( int|string $userId ): self
	{
		$this->userId	= $userId;
		return $this;
	}
}
