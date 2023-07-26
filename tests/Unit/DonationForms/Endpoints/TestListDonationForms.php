<?php

namespace Give\Tests\Unit\DonationForms\Endpoints;

use Exception;
use Give\DonationForms\V2\Endpoints\ListDonationForms;
use Give\DonationForms\V2\ListTable\DonationFormsListTable;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\Unit\DonationForms\TestTraits\LegacyDonationFormAdapter;
use WP_REST_Request;
use WP_REST_Server;

class TestListDonationForms extends TestCase
{
    use RefreshDatabase;
    use LegacyDonationFormAdapter;

    private $donationForms = [];

    public function setUp()
    {
        parent::setUp();

        $formLevelTypes = ['multi', 'simple'];
        for ( $count = 1; $count <= 5; $count++ ) {
            $formLevelType = $formLevelTypes[round(rand(0, 1))];
            $this->donationForms[] = $formLevelType === 'multi' ? $this->createMultiLevelDonationForm() : $this->createSimpleDonationForm();
        }
    }

    /**
     * @since 2.25.0
     *
     * @return void
     * @throws Exception
     */
    public function testShouldReturnListWithSameSize()
    {
        $mockRequest = $this->getMockRequest();
        // set_params
        $mockRequest->set_param('page', 1);
        $mockRequest->set_param('perPage', 30);
        $mockRequest->set_param('locale', 'us-US');
        $mockRequest->set_param('status', 'any');

        $listDonationForms = new ListDonationForms();
        $response = $listDonationForms->handleRequest($mockRequest);

        $this->assertSameSize($this->donationForms, $response->data['items']);
    }

    /**
     * @since 2.25.0
     *
     * @return void
     * @throws Exception
     */
    public function testShouldReturnListWithSameData()
    {
        $sortDirection = ['asc', 'desc'][round(rand(0, 1))];
        $mockRequest = $this->getMockRequest();
        // set_params
        $mockRequest->set_param('page', 1);
        $mockRequest->set_param('perPage', 30);
        $mockRequest->set_param('locale', 'us-US');
        $mockRequest->set_param('sortColumn', 'id');
        $mockRequest->set_param('sortDirection', $sortDirection);
        $mockRequest->set_param('status', 'any');

        $expectedItems = $this->getMockColumns($this->donationForms, $sortDirection);

        $listDonationForms = new ListDonationForms();
        $response = $listDonationForms->handleRequest($mockRequest);

        $this->assertSame($expectedItems, $response->data['items']);
    }

    /**
     * @since 2.25.0
     *
     * @return WP_REST_Request
     */
    public function getMockRequest(): WP_REST_Request
    {
        return new WP_REST_Request(
            WP_REST_Server::READABLE,
            '/wp/v2/admin/forms'
        );
    }

    /**
     * @since 2.25.0
     *
     * @param array  $donationForms
     * @param string $sortDirection
     *
     * @return array
     */
    public function getMockColumns(array $donationForms, string $sortDirection = 'desc'): array
    {
        $listTable = new DonationFormsListTable();
        $columns = $listTable->getColumns();

        $expectedItems = [];
        foreach ( $donationForms as $donationForm ) {
            $expectedItem = [];
            foreach ( $columns as $column ) {
                $expectedItem[$column::getId()] = $column->getCellValue($donationForm);
            }
            $v2form = defined('GIVE_NEXT_GEN_VERSION') && ! get_post_meta($donationForm->id, 'formBuilderFields');
            $migrate = $v2form
                       && ! get_post_meta($donationForm->id, 'migratedFormId');

            $transfer = $v2form
                        && get_post_meta($donationForm->id, 'migratedFormId')
                        && ! get_post_meta($donationForm->id, 'transferredFormId');

            $expectedItem['name'] = $donationForm->title;
            $expectedItem['edit'] = get_edit_post_link($donationForm->id, 'edit');
            $expectedItem['permalink'] = get_permalink($donationForm->id);
            $expectedItem['migrate'] = $migrate;
            $expectedItem['transfer'] = $transfer;
            $expectedItem['v2form'] = $v2form;
            $expectedItems[] = $expectedItem;
        }

        if ($sortDirection === 'desc') {
            $expectedItems = array_reverse($expectedItems);
        }

        return $expectedItems;
    }
}

