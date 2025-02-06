<?php

namespace Give\Campaigns\Blocks\CampaignComments\Controller;

use Give\Campaigns\Blocks\CampaignComments\DataTransferObjects\BlockAttributes;
use Give\Campaigns\CampaignDonationQuery;
use Give\Campaigns\Repositories\CampaignRepository;
use Give\Donations\ValueObjects\DonationMetaKeys;

/**
 * @unreleased
 */
class BlockRenderController
{
    /**
     * @unreleased
     */
    public function render(array $attributes): string
    {
        $blockAttributes = BlockAttributes::fromArray($attributes);

        $encodedAttributes = json_encode($blockAttributes->toArray());
        $encodedData = json_encode($this->getCampaignCommentData($blockAttributes));

        return "<div class='givewp-campaign-comment-block' data-comments='{$encodedData}' data-attributes='{$encodedAttributes}'></div>";
    }

    /**
     * @unreleased
     * TODO:: Retrieve avatar from donor
     */
    public function getCampaignCommentData($attributes): array
    {
        $data = [];

        if ($attributes->campaignId === 'all') {
            $campaigns = give(CampaignRepository::class)->prepareQuery()->getAll();
            foreach ($campaigns as $campaign) {
                $donations = $this->getCampaignDonationMeta($attributes, $campaign);

                foreach ($donations as $donation) {
                    $data[] = [
                        'campaignId'    => $campaign->id,
                        'campaignTitle' => $campaign->title,
                        'donorName'     => $donation->donorName,
                        'comment'       => $donation->comment,
                        'date'          => $donation->date,
                        'avatar'        => '',
                    ];
                }
            }
        } else {
            $campaign = give(CampaignRepository::class)->getById($attributes->campaignId);
            $donation = $this->getCampaignDonationMeta($attributes, $campaign);
            $data = [
                'campaignId'    => $campaign->id,
                'campaignTitle' => $campaign->title,
                'donorName'     => $donation->donorName,
                'comment'       => $donation->comment,
                'avatar'        => '',
            ];
        }

        return $data;
    }

    /**
     * @unreleased
     */
    public function getCampaignDonationMeta($attributes, $campaign)
    {
        $query = (new CampaignDonationQuery($campaign))
            ->joinDonationMeta(DonationMetaKeys::DONOR_ID, 'donorIdMeta')
            ->joinDonationMeta(DonationMetaKeys::COMMENT, 'commentMeta')
            ->joinDonationMeta(DonationMetaKeys::ANONYMOUS, 'anonymousMeta')
            ->joinDonationMeta('_give_completed_date', 'dateMeta')
            ->leftJoin('give_donors', 'donorIdMeta.meta_value', 'donors.id', 'donors')
            ->limit($attributes->commentsPerPage);

        $query->select(
            'donorIdMeta.meta_value as donorId',
            'commentMeta.meta_value as comment',
            'anonymousMeta.meta_value as anonymous',
            'dateMeta.meta_value as date',
            'donors.name as donorName'
        );

        return $query->getAll();
    }
}
