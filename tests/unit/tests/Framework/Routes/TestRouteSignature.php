<?php

namespace unit\tests\Framework\Routes;

use Give\Framework\PaymentGateways\Routes\RouteSignature;
use PHPUnit\Framework\TestCase;

/**
 * @since 2.19.0
 *
 * @coversDefaultClass RouteSignature
 */
class TestRouteSignature extends TestCase
{
    /**
     * @unreleased add expiration
     * @since 2.19.4 replace RouteSignature args with unique donationId
     * @since 2.19.0
     *
     * @return void
     */
    public function testRouteSignatureReturnsValidString()
    {
        $gatewayId = 'test-gateway';
        $gatewayMethod = 'secureMethod';
        $donationId = 1;
        $expiration = RouteSignature::createExpirationTimestamp();

        $signature = new RouteSignature($gatewayId, $gatewayMethod, $donationId, $expiration);

        $string = "$gatewayId@$gatewayMethod:$donationId|$expiration";

        $this->assertEquals($signature->toString(), $string);
    }

    /**
     * @unreleased
     *
     * @return void
     */
    public function testRouteSignatureReturnsValidHash()
    {
        $gatewayId = 'test-gateway';
        $gatewayMethod = 'secureMethod';
        $donationId = 1;
        $expiration = RouteSignature::createExpirationTimestamp();

        $signature = new RouteSignature($gatewayId, $gatewayMethod, $donationId, $expiration);

        $this->assertTrue(
            hash_equals(
                $signature->toHash(),
                wp_hash($signature->toString())
            )
        );
    }

    /**
     * @unreleased
     *
     * @return void
     */
    public function testRouteSignatureIsValidReturnsTrue()
    {
        $gatewayId = 'test-gateway';
        $gatewayMethod = 'secureMethod';
        $donationId = 1;
        $expiration = RouteSignature::createExpirationTimestamp();

        $signature = new RouteSignature($gatewayId, $gatewayMethod, $donationId, $expiration);

        $suppliedSignature = wp_hash("$gatewayId@$gatewayMethod:$donationId|$expiration");

        $this->assertTrue(
            $signature->isValid($suppliedSignature, $expiration)
        );
    }

    /**
     * @unreleased
     *
     * @return void
     */
    public function testRouteSignatureIsValidReturnsFalseFromExpiration()
    {
        $gatewayId = 'test-gateway';
        $gatewayMethod = 'secureMethod';
        $donationId = 1;
        $yesterday = (string)current_datetime()->modify('-1 day')->getTimestamp();

        $signature = new RouteSignature($gatewayId, $gatewayMethod, $donationId, $yesterday);

        $suppliedSignature = wp_hash("$gatewayId@$gatewayMethod:$donationId|$yesterday");

        $this->assertFalse(
            $signature->isValid($suppliedSignature, $yesterday)
        );
    }

    /**
     * @unreleased
     *
     * @return void
     */
    public function testRouteSignatureIsValidReturnsFalseFromIntegrity()
    {
        $gatewayId = 'test-gateway';
        $gatewayMethod = 'secureMethod';
        $donationId = 1;
        $expiration = RouteSignature::createExpirationTimestamp();

        $signature = new RouteSignature($gatewayId, $gatewayMethod, $donationId, $expiration);

        $suppliedSignature = wp_hash("$gatewayId@$gatewayMethod:2|$expiration");

        $this->assertFalse(
            $signature->isValid($suppliedSignature, $expiration)
        );
    }
}
