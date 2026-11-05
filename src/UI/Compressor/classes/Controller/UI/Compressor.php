<?php

use CeusMedia\HydrogenFramework\Controller;

class Controller_UI_Compressor extends Controller
{
	public function flush()
	{
		$page	= $this->env->getPage();
		$page->js->clearCache();
		$page->css->primer->clearCache();
		$page->css->theme->clearCache();
		$this->env->getMessenger()->noteNotice( $this->getWords( 'msg', 'ui/compressor' )['noticeCacheFlushed'] );
		$request	= $this->env->getRequest();
		$redirect	= $request->has( 'from' ) ? $request->get( 'from' ) : NULL;
		$this->restart( $redirect );
	}
}
