<?php

use CeusMedia\HydrogenFramework\Hook;

class Hook_UI_Bootstrap_Switch extends Hook
{
	public function onPageApplyModules(): void
	{
		$script	= '
$(":input[type=checkbox].shiftbox").bootstrapSwitch();
$(":input[type=checkbox].shiftbox").bind("change",function(e){
//	console.log($(this).attr("id")+": "+$(this).is(":checked"))
});';
		$this->context->addScriptOnReady( $script );
	}
}