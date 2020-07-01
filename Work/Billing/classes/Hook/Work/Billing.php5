<?php
class Hook_Work_Billing extends CMF_Hydrogen_Hook
{
	static public function onPageApplyModules( CMF_Hydrogen_Environment $env, $context, $module, $data = array() )
	{
		$context->js->addScriptOnReady("WorkBilling.init()");
	}
}
