<?php
declare(strict_types=1);

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Manage_My_Provision_License extends Hook
{
	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function onRegisterTab(): void
	{
		$logicAuth		= Logic_Authentication::getInstance( $this->env );
		$logicProvision = Logic_User_Provision::getInstance( $this->env );
		$nrLicenses		= count( $logicProvision->getUserLicensesFromUser( $logicAuth->getCurrentUserId() ) );
		$nrKeys			= count( $logicProvision->getUserLicenseKeysFromUser( $logicAuth->getCurrentUserId() ) );

		$this->context->registerTab( '', self::renderTabLabel( $this->env, 'index', $nrLicenses, 'euro' ) );
		$this->context->registerTab( 'key', self::renderTabLabel( $this->env, 'keys', $nrKeys, 'key' ) );
		$this->context->registerTab( 'add', self::renderTabLabel( $this->env, 'add', 0, 'plus' ) );
	}

	/*
	public function onMyUserRegisterTab(){
		$logicAuth		= Logic_Authentication::getInstance( $this->env );
		$logicProvision = Logic_Accounting::getInstance( $this->env );
		$nrLicenses		= count( $logicProvision->getUserLicensesFromUser( $logicAuth->getCurrentUserId() ) );
		$nrKeys			= count( $logicProvision->getUserLicenseKeysFromUser( $logicAuth->getCurrentUserId() ) );

		$this->context->registerTab( '../license', self::renderTabLabel( $this->env, 'index', $nrLicenses, 'euro' ) );
		$this->context->registerTab( '../license/key', self::renderTabLabel( $this->env, 'keys', $nrKeys, 'key' ) );
	}*/

	protected static function renderTabLabel( Environment $env, $labelKey, $count = 0, $icon = NULL )
	{
		$words	= (object) $env->getLanguage()->getWords( 'manage/my/provision' );					//  load words
		$label	= $words->tabs[$labelKey];
		if( $count )
			$label	.= '&nbsp;&nbsp;<span class="badge badge-info">'.$count.'</span>&nbsp;';
		if( $icon ){
			$icon	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-'.$icon] );
			$label	= $icon.'&nbsp;'.$label;
		}
		return $label;
	}
}