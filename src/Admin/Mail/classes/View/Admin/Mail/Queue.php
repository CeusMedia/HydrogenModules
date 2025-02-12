<?php

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Exception\Page as HtmlExceptionPage;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\View;

class View_Admin_Mail_Queue extends View
{
	public function enqueue(): void
	{
	}

	public function html(): void
	{
		try{
			/** @var Entity_Mail $mail */
			$mail	= $this->getData( 'mail' );
			$helper	= new View_Helper_Mail_View_HTML( $this->env );
			$helper->setMailObjectInstance( $mail->objectInstance );
			print( $helper->render() );
		}
		catch( Exception $e ){
			HtmlExceptionPage::display( $e );
		}
		exit;
	}

	public function index(): void
	{
		$script	= 'ModuleAdminMail.Queue.init();';
		$this->env->getPage()->js->addScriptOnReady( $script );
	}

	public function renderFact( string $key, $value ): string
	{
		$words	= $this->env->getLanguage()->getWords( 'admin/mail/queue' );
		if( $key === 'object')
			return '';
		if( $key === 'status' ){
			$value = $words['states'][$value].' <small class="muted">('.$value.')</small>';
		}
		else if( $key === 'mailClass' ){
			$original	= $value;
			$value	= preg_replace( '/^Mail_/', '', $value );
			$value	= preg_replace( '/_/', ':', $value );
			$value	= HtmlTag::create( 'abbr', $value, ['title' => $original] );
		}
		else if( str_ends_with( $key, 'At' ) ){
			if( !( (int) $value ) )
				return '';
			$helper	= new View_Helper_TimePhraser( $this->env );
			$date	= date( 'Y-m-d H:i:s', $value );
			$phrase	= $helper->convert( $value, TRUE, 'vor ' );
			$value	= $phrase.'&nbsp;<small class="muted">('.$date.')</small>';
		}
		else if( str_ends_with( $key, 'Id' ) ){
			if( (int) $value === 0 )
				return '';
		}
		else if( str_contains( $key, 'Address' ) && strlen( $value ) ){
			$icon	= HtmlTag::create( 'i', '', ['class' => 'icon-envelope'] );
			$link	= HtmlTag::create( 'a', $value, ['href' => 'mailto:'.$value] );
			$value	= $icon.'&nbsp;'.$link;
		}
		else if( '' === ($value ?? '' ) )
			return '';

		$label	= $words['view-facts']['label'.ucfirst( $key )];
		$term	= HtmlTag::create( 'dt', $label );
		$def	= HtmlTag::create( 'dd', $value.'&nbsp;' );
		return $term.$def;
	}

	public function view(): void
	{
	}

	protected function __onInit(): void
	{
		$this->env->getPage()->addCommonStyle( 'module.admin.mail.css' );
		$this->env->getPage()->js->addModuleFile( 'module.admin.mail.js' );
	}
}
