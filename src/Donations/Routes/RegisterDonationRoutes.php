<?php

namespace Give\Donations\Routes;

use Give\Donations\Controllers\DonationRequestController;
use Give\Donations\ValueObjects\DonationRoute;
use WP_REST_Request;
use WP_REST_Server;

/**
 * @unreleased
 */
class RegisterDonationRoutes
{
    const SORTABLE_COLUMNS = [
        'id',
        'createdAt',
        'updatedAt',
        'status',
        'amount',
        'feeAmountRecovered',
        'donorId',
        'firstName',
        'lastName',
    ];

    /**
     * @var DonationRequestController
     */
    protected $donationRequestController;

    /**
     * @unreleased
     */
    public function __construct(DonationRequestController $donationRequestController)
    {
        $this->donationRequestController = $donationRequestController;
    }

    /**
     * @unreleased
     */
    public function __invoke()
    {
        $this->registerGetDonation();
        $this->registerGetDonations();
    }

    /**
     * Get Donation route
     *
     * @unreleased
     */
    public function registerGetDonation()
    {
        register_rest_route(
            DonationRoute::NAMESPACE,
            DonationRoute::DONATION,
            [
                [
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => function (WP_REST_Request $request) {
                        return $this->donationRequestController->getDonation($request);
                    },
                    'permission_callback' => '__return_true',
                ],
                'args' => [
                    'id' => [
                        'type' => 'integer',
                        'required' => true,
                    ],
                ],
            ]
        );
    }

    /**
     * Get Donations route
     *
     * @unreleased
     */
    public function registerGetDonations()
    {
        register_rest_route(
            DonationRoute::NAMESPACE,
            DonationRoute::DONATIONS,
            [
                [
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => function (WP_REST_Request $request) {
                        return $this->donationRequestController->getDonations($request);
                    },
                    'permission_callback' => '__return_true',
                ],
                'args' => [
                    'page' => [
                        'type' => 'integer',
                        'default' => 1,
                        'minimum' => 1,
                    ],
                    'per_page' => [
                        'type' => 'integer',
                        'default' => 30,
                        'minimum' => 1,
                        'maximum' => 100,
                    ],
                    'sort' => [
                        'validate_callback' => function ($param) {
                            if (empty($param)) {
                                return true;
                            }

                            return in_array($param, self::SORTABLE_COLUMNS, true);
                        },
                        'default' => 'id',
                    ],
                    'direction' => [
                        'validate_callback' => function ($param) {
                            if (empty($param)) {
                                return true;
                            }

                            return in_array(strtoupper($param), ['ASC', 'DESC'], true);
                        },
                        'default' => 'ASC',
                    ],
                    'campaignId' => [
                        'type' => 'integer',
                        'required' => false,
                        'default' => 0,
                    ],
                    'hideAnonymousDonations' => [
                        'type' => 'boolean',
                        'required' => false,
                        'default' => true,
                    ],
                ],
            ]
        );
    }
}
