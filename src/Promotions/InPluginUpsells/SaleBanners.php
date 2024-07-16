<?php

namespace Give\Promotions\InPluginUpsells;

use DateTimeImmutable;
use DateTimeZone;
use Exception;

/**
 * @unreleased create abstract class.
 * @since 2.17.0
 */
abstract class SaleBanners
{
    /**
     * @var string
     */
    private $optionName = 'give_hidden_sale_banners';

    /**
     * @var array
     */
    private $hiddenBanners;

    /**
     * @since 2.17.0
     */
    public function __construct()
    {
        $this->hiddenBanners = get_option($this->optionName, []);
    }

    /**
     * Get banners definitions/details.
     *
     * @unreleased
     */
    abstract public function getBanners(): array;

    /**
     * Pages/when to render banner.
     *
     * @unreleased
     */
    abstract public function render(): void;

    /**
     * Helper function to determine when to load/display the banner.
     *
     * @unreleased
     */
    abstract public static function isShowing(): bool;


    /**
     * Get the banners that should be displayed.
     *
     * @since 3.1.0 hide banners for users with Pro tier accounts.
     * @since 2.17.0
     */
    public function getVisibleBanners(): array
    {
        $currentDateTime = current_datetime();
        $currentUserId = get_current_user_id();
        $giveWPWebsiteTimezone = new DateTimeZone('America/Los_Angeles');

        return array_filter(
            $this->getBanners(),
            function ($banner) use ($currentDateTime, $currentUserId, $giveWPWebsiteTimezone) {
                $isHidden = in_array($banner['id'] . $currentUserId, $this->hiddenBanners, true);

                try {
                    $isFuture = $currentDateTime < new DateTimeImmutable($banner['startDate'], $giveWPWebsiteTimezone);
                    $isPast = $currentDateTime > new DateTimeImmutable($banner['endDate'], $giveWPWebsiteTimezone);
                } catch (Exception $exception) {
                    return false;
                }

                return !($isHidden || $isFuture || $isPast);
            }
        );
    }

    /**
     * Marks the given banner id as hidden for the current user, so it will not display again.
     *
     * @since 2.17.0
     *
     * @return void
     */
    public function hideBanner(string $banner)
    {
        $this->hiddenBanners[] = $banner;

        update_option(
            $this->optionName,
            array_unique($this->hiddenBanners)
        );
    }

    /**
     * Load scripts
     *
     * @since 2.17.0
     */
    public function loadScripts()
    {
        wp_enqueue_script(
            'give-in-plugin-upsells-sale-banners',
            GIVE_PLUGIN_URL . 'assets/dist/js/admin-upsell-sale-banner.js',
            [],
            GIVE_VERSION,
            true
        );

        wp_localize_script(
            'give-in-plugin-upsells-sale-banners',
            'GiveSaleBanners',
            [
                'apiRoot' => esc_url_raw(rest_url('give-api/v2/sale-banner')),
                'apiNonce' => wp_create_nonce('wp_rest'),
            ]
        );

        wp_enqueue_style('givewp-admin-fonts');
    }

    /**
     * @since 3.13.0 remove all_access_pass.
     * @since 3.1.0 retrieve licensed plugin slugs.
     */
    public static function getLicensedPluginSlugs(): array
    {
        $pluginSlugs = [];
        $licenses = get_option("give_licenses", []);

        foreach ($licenses as $license) {
            foreach ($license['download'] as $plugin) {
                $pluginSlugs[] = $plugin['plugin_slug'];
            }
        }

        return $pluginSlugs;
    }

    /**
     * @since 3.1.0 determines user pricing plan from licensed plugin slugs.
     */
    public static function getUserPricingPlan(): string
    {
        $plan = 'Free';

        $pricingPlans = [
            'Basic' => self::getBasicLicenseSlugs(),
            'Plus'  => self::getPlusLicenseSlugs(),
            'Pro'   => self::getProLicenseSlugs(),
        ];

        $licensedPluginSlugs = self::getLicensedPluginSlugs();

        foreach ($pricingPlans as $planName => $requiredLicenses) {
            $missingLicenses = array_diff($requiredLicenses, $licensedPluginSlugs);
            if (empty($missingLicenses)) {
                $plan = $planName;
            }
        }

        return $plan;
    }

    /**
     * @since 3.13.0 add type for $data.
     * @since 3.1.0 return data by user pricing plan.
     */
    public static function getDataByPricingPlan(array $data): string
    {
        $userPricingPlan = self::getUserPricingPlan();

        if (array_key_exists($userPricingPlan, $data)) {

            return $data[$userPricingPlan];
        }

        return $data['default'];
    }

    /**
     * @since 3.13.0
     *
     *  This method cycles through the visible banners, selecting the next banner in the list
     *  on each call. If no banners are visible, or if the session index is not set, it returns
     *  all visible banners.
     */
    public function alternateVisibleBanners(): array
    {
        $visibleBanners = $this->getVisibleBanners();
        $bannerCount = count($visibleBanners);

        if ($bannerCount > 0) {
            $currentIndex = $_SESSION['banner_index'] ?? 0;

            $selectedBanner = $visibleBanners[$currentIndex];

            $currentIndex = ($currentIndex + 1) % $bannerCount;

            $_SESSION['banner_index'] = $currentIndex;

            if( !$selectedBanner){
                $this->destroySession();
                return $visibleBanners;
            }

            return [$selectedBanner];
        }

        return $visibleBanners;
    }

    /**
     * @since 3.13.0
     */
    public function startSession(): void
    {
        if (!session_id()) {
            session_start();
        }
    }

    /**
     * @since 3.13.0
     */
    public function destroySession(): void
    {
        if (session_id()) {
            session_destroy();
        }
    }


    /**
     * @since 3.13.0
     */
    public static function getBasicLicenseSlugs(): array
    {
        return [
            'give-bitpay',
            'give-text-to-give',
            'give-activecampaign',
            'give-moneris',
            'give-square',
            'give-mollie',
            'give-payfast',
            'give-sofort',
            'give-americloud-payments',
            'give-paytm',
            'give-gocardless',
            'give-razorpay',
            'give-payumoney',
            'give-convertkit',
            'give-aweber',
            'give-per-form-gateways',
            'give-email-reports',
            'give-manual-donations',
            'give-zapier',
            'give-google-analytics',
            'ccavenue'            => 'give-ccavenue',
            'give-constant-contact',
            'give-braintree',
            'give-iats',
            'give-2checkout',
            'give-pdf-receipts',
            'give-paymill',
            'give-stripe',
            'give-authorize-net',
            'give-mailchimp',
        ];
    }

    /**
     * @since 3.13.0
     */
    public static function getPlusLicenseSlugs(): array
    {
        $basicLicenseSlugs = self::getBasicLicenseSlugs();

        $plusLicenseSlugs = [
            'give-webhooks',
            'give-salesforce',
            'give-funds',
            'give-annual-receipts',
            'give-currency-switcher',
            'give-donation-upsells-woocommerce',
            'give-tributes',
            'give-fee-recovery',
            'give-email-reports',
            'give-gift-aid',
            'give-recurring',
            'give-form-field-manager',
        ];

        return array_merge($basicLicenseSlugs, $plusLicenseSlugs);
    }

    /**
     * @since 3.13.0
     */
    public static function getProLicenseSlugs(): array
    {
        $plusLicenseSlugs = self::getPlusLicenseSlugs();

        $proLicenseSlugs = ['give-peer-to-peer'];

        return array_merge($plusLicenseSlugs, $proLicenseSlugs);
    }

}

