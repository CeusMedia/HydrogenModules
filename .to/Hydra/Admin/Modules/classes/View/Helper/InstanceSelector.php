<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;

class View_Helper_InstanceSelector
{
	protected $env;
	protected $currentId;

	public function __construct( $env )
	{
		$this->env	= $env;
	}

	public function setCurrentId( $currentId ): self
	{
		$this->currentId	= $currentId;
		return $this;
	}

	public function render(): string
	{
		$model			= new Model_Instance( $this->env );
		$optInstance	= array( '' => '-');
		foreach( $model->getAll() as $instanceId => $instance )
			$optInstance[$instanceId]	= $instance->title;
		asort( $optInstance );
		$currentId	= $this->currentId;
		if( !$currentId )
			$currentId	= $this->env->getSession()->get( 'instanceId' );
		$optInstance	= HtmlElements::Options( $optInstance, $currentId );

		$path		= $this->env->getRequest()->get( '__path' );
		$linkReset	= '<a href="./?resetInstanceId">Instanz</a>';

		return '
		<div id="selector-instance">
			<label for="input_instanceId">'.$linkReset.':</label>&nbsp;
			<select id="input_instanceId" name="instanceId" onchange="selectInstanceId($(this).val(), \''.$path.'\');">'.$optInstance.'</select>
		</div>
		<script>
		function selectInstanceId(id, forward){
			var url = "./admin/instance/select/"+id;
			if(forward)
				url += "?forward="+forward;
			document.location.href = url;
		}
		</script>
';

	}
}
