<?php
declare(strict_types=1);

use AltchaOrg\Altcha\Altcha;
use AltchaOrg\Altcha\Algorithm\Pbkdf2;
use AltchaOrg\Altcha\Challenge;
use AltchaOrg\Altcha\CreateChallengeOptions;
use AltchaOrg\Altcha\ServerSignature;
use AltchaOrg\Altcha\ServerSignatureVerification;
use AltchaOrg\Altcha\VerifySolutionOptions;
use AltchaOrg\Altcha\VerifySolutionResult;
use CeusMedia\Common\Exception\Data\Missing as DataMissingException;
use CeusMedia\Common\Exception\NotSupported as NotSupportedException;
use CeusMedia\HydrogenFramework\Logic\Shared as SharedLogic;

class Logic_Altcha extends SharedLogic
{
	protected Altcha $altcha;
	protected Pbkdf2 $pbkdf2;
	protected string $secret	= '';

	public function challenge(): Challenge
	{
		return $this->altcha->createChallenge( new CreateChallengeOptions(
			algorithm: $this->pbkdf2,
			cost: 10000,
			expiresAt: time() + 120,
		) );
	}

	/**
	 *	@param		string $altcha
	 *	@return		ServerSignatureVerification|VerifySolutionResult
	 *	@throws		JsonException
	 *	@throws		DataMissingException
	 *	@throws		NotSupportedException
	 */
	public function verify( string $altcha ): ServerSignatureVerification|VerifySolutionResult
	{
		// Decode just enough to auto-detect the payload type:
		//   Server signature: has "verificationData"
		//   Client solution:  has "challenge" + "solution"
		$decoded	= base64_decode( $altcha, TRUE );
		$payload	= NULL;
		if( FALSE !== $decoded )
			$payload	= json_decode( $decoded, TRUE, 512, JSON_THROW_ON_ERROR );

		if( !is_array( $payload ) )
			throw new DataMissingException( 'Invalid "altcha" field' );

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
		}
		else
			throw new NotSupportedException( 'Unrecognized payload format' );

		return $result;
	}

	protected function __onInit(): void
	{
		$moduleConfig	= $this->env->getModules()->get( 'Security_Altcha' )->getConfigAsDictionary();
		$this->secret	= $moduleConfig->get( 'secret' );
		$this->altcha	= new Altcha( $this->secret );
		$this->pbkdf2	= new Pbkdf2();
	}
}
