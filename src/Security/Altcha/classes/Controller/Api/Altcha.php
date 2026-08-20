<?php

use AltchaOrg\Altcha\Altcha;
use AltchaOrg\Altcha\Algorithm\Pbkdf2;
use AltchaOrg\Altcha\CreateChallengeOptions;
use AltchaOrg\Altcha\ServerSignature;
use AltchaOrg\Altcha\VerifySolutionOptions;
use CeusMedia\HydrogenFramework\Controller\Api as ApiController;

class Controller_Api_Altcha extends ApiController
{
	protected Altcha $altcha;
	protected Pbkdf2 $pbkdf2;
	protected string $secret	= '';

	public function challenge(): void
	{
		$challenge = $this->altcha->createChallenge( new CreateChallengeOptions(
			algorithm: $this->pbkdf2,
			cost: 10000,
			expiresAt: time() + 120,
		) );
		$this->respondData( $challenge->toArray() );
	}

	public function verify(): void
	{
		$altchaField	= $this->request->get( 'altcha', '' ) ?? '';
		if( '' === $altchaField )
			$this->respondError( 'Missing "altcha" form field', 400 );

		// Decode just enough to auto-detect the payload type:
		//   Server signature: has "verificationData"
		//   Client solution:  has "challenge" + "solution"
		$decoded	= base64_decode( $altchaField, TRUE );
		$payload	= NULL;
		if( FALSE !== $decoded )
			$payload	= json_decode( $decoded, TRUE, 512, JSON_THROW_ON_ERROR );

		if( !is_array( $payload ) )
			$this->respondError( 'Invalid "altcha" field', 400 );

		try {
			$verified	= NULL;
			if( isset( $payload['verificationData'] ) ){
				$result		= ServerSignature::verifyServerSignature( $payload, $this->secret );
				$verified	= $result->verified;
			}
			else if( isset( $payload['challenge'], $payload['solution'] ) ){
				// The library accepts the raw base64 string, a decoded array, or a
				// Payload object directly — no manual parsing required.
				$result		= $this->altcha->verifySolution( new VerifySolutionOptions(
					payload: $payload,
					algorithm: $this->pbkdf2,
				) );
				$verified = $result->verified;
			}
			else
				$this->respondError( 'Unrecognized payload format', 0, 400 );
		}
		catch( InvalidArgumentException $e ){
			$this->respondError( 'Exception: '.$e->getMessage(), $e->getCode(), 400 );
		}

		if( !$verified )
			$this->respondError( 'Verification failed', 0, 403 );

		$this->respondData( ['verified' => $result] );

/*		// Payload verified — process form data
		$formData	= $this->env->getRequest()->getAllFromSource( 'POST' );
		$this->respondData( [
			'data'			=> $formData,
			'verification'	=> $result,
		] );*/
	}

	protected function __onInit(): void
	{
		$moduleConfig	= $this->env->getModules()->get( 'Security_Altcha' )->getConfigAsDictionary();
		$this->secret	= $moduleConfig->get( 'secret' );
		$this->altcha	= new Altcha( $this->secret );
		$this->pbkdf2	= new Pbkdf2();

	}
}