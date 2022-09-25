<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Work_Time_Modal_Assign extends CMF_Hydrogen_View_Helper_Abstract
{
	protected $from;
	protected $module;
	protected $moduleId;
	protected $timers	= [];
	protected $userId;

	public function __construct( Environment $env )
	{
		$this->env	= $env;
	}

	public function render()
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
			$orders		= array( 'title' => 'ASC' );
			$timers		= $modelTimer->getAll( $conditions, $orders );
		}


		$listTimers		= [];
		foreach( $timers as $timer ){
			$checkbox		= HtmlTag::create( 'input', NULL, array( 'type' => 'checkbox', 'name' => 'timerIds[]', 'value' => $timer->workTimerId ) );
			$label			= HtmlTag::create( 'label', $checkbox.'&nbsp;'.$timer->title, array( 'class' => 'checkbox' ) );
			$listTimers[]	= HtmlTag::create( 'li', $label );
		}
		$listTimers		= HtmlTag::create( 'ul', $listTimers, array( 'class' => 'unstyled' ) );

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

	public function setFrom( $from ): self
	{
		$this->from		= $from;
		return $this;
	}

	public function setRelation( $module, $moduleId ): self
	{
		$this->relation	= (object) array(
			'module'	=> $module,
			'moduleId'	=> $moduleId,
		);
		return $this;
	}

	public function setTimers( $timers ): self
	{
		$this->timers	= $timers;
		return $this;
	}

	public function setUserId( $userId ): self
	{
		$this->userId	= $userId;
		return $this;
	}
}
