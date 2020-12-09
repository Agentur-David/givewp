<?php

namespace Give\DonorProfiles\Routes;

use WP_REST_Request;
use Give\API\RestRoute;
use Give\DonorProfiles\Profile as Profile;

/**
 * @since 2.10.0
 */
class ProfileRoute implements RestRoute {

	/** @var string */
	protected $endpoint = 'donor-profile/profile';

	/**
	 * @inheritDoc
	 */
	public function registerRoute() {
		register_rest_route(
			'give-api/v2',
			$this->endpoint,
			[
				[
					'methods'             => 'POST',
					'callback'            => [ $this, 'handleRequest' ],
					'permission_callback' => function() {
						return is_user_logged_in();
					},
				],
				'args' => [
					'data' => [
						'type'              => 'string',
						'required'          => true,
						'sanitize_callback' => [ $this, 'sanitizeData' ],
					],
					'id'   => [
						'type'     => 'int',
						'required' => true,
					],
				],
			]
		);
	}

	public function sanitizeData( $value, $request, $param ) {

		$sanitizeHelper = '\Give\DonorProfiles\Helpers\SanitizeProfileData';
		$sanitizedValue = json_decode( $value );

		$attributesMap = [
			'firstName'           => [
				'sanitizeCallback' => 'sanitize_text_field',
				'default'          => '',
			],
			'lastName'            => [
				'sanitizeCallback' => 'sanitize_text_field',
				'default'          => '',
			],
			'additionalEmails'    => [
				'sanitizeCallback' => [ $sanitizeHelper, 'sanitizeAdditionalEmails' ],
				'default'          => [],
			],
			'additionalAddresses' => [
				'sanitizeCallback' => [ $sanitizeHelper, 'sanitizeAdditionalAddresses' ],
				'default'          => [],
			],
			'primaryEmail'        => [
				'sanitizeCallback' => 'sanitize_email',
				'default'          => '',
			],
			'primaryAddress'      => [
				'sanitizeCallback' => [ $sanitizeHelper, 'sanitizeAddress' ],
				'default'          => [],
			],
			'titlePrefix'         => [
				'sanitizeCallback' => 'sanitize_text_field',
				'default'          => '',
			],
			'avatarId'            => [
				'sanitizeCallback' => [ $sanitizeHelper, 'sanitizeInt' ],
				'default'          => 0,
			],
		];

		foreach ( $attributesMap as $key => $value ) {
			$sanitizedValue->{$key} = $this->sanitizeValue( $sanitizedValue->{$key}, $value );
		}

		return $sanitizedValue;

	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return array
	 *
	 * @since 2.10.0
	 */
	public function handleRequest( WP_REST_Request $request ) {
		return $this->updateProfile( $request->get_param( 'data' ), $request->get_param( 'id' ) );
	}

	/**
	 * @return array
	 *
	 * @since 2.10.0
	 */
	protected function updateProfile( $data, $id ) {
		$profile = new Profile( $id );
		$profile->update( $data );
		return [
			'profile' => $profile->getProfileData(),
		];
	}

	protected function sanitizeValue( $value, $config ) {
		if ( ! empty( $value ) && is_callable( $config['sanitizeCallback'] ) ) {
			return $config['sanitizeCallback']( $value );
		} else {
			return $config['default'];
		}
	}
}
