import {InspectorControls, useBlockProps} from '@wordpress/block-editor';
import {__} from '@wordpress/i18n';
import {PanelBody, SelectControl, TextControl, ToggleControl} from '@wordpress/components';
import {BlockEditProps} from '@wordpress/blocks';
import CampaignCommentCard from '../shared/components/CommentCard';
import useCampaigns from '../../../shared/hooks/useCampaigns';

export const data = [
    {
        campaignId: '1',
        comment:
            'Description of campaign goes here. It can be a lengthy or short description. This text is repeated across the section. Description of campaign goes here. It can be a lengthy or short description.',
        date: 'Today',
        campaignTitle: 'To Campaign name',
        donorName: 'Name of donor',
        avatarUrl: 'https://www.gravatar.com/avatar/',
    },
];

export default function Edit({
    attributes,
    setAttributes,
}: BlockEditProps<{
    campaignIds: string;
    title: string;
    showAnonymous: boolean;
    showAvatar: boolean;
    showDate: boolean;
    showName: boolean;
    commentLength: number;
    readMoreText: string;
    commentsPerPage: number;
}>) {
    const blockProps = useBlockProps();
    const {campaigns, hasResolved} = useCampaigns();

    const normalizedData = Array.isArray(data) ? data : [data];

    return (
        <figure {...blockProps}>
            {normalizedData?.map((item) => (
                <CampaignCommentCard key={item.campaignId} attributes={attributes} data={item} />
            ))}

            {hasResolved && (
                <InspectorControls>
                    <PanelBody title={__('Linked Campaign', 'give')} initialOpen={true}>
                        <SelectControl
                            label={__('Select Campaign', 'give')}
                            value={attributes.campaignIds}
                            help={__(
                                'Only comments associated with the campaign will be displayed in this block.',
                                'give'
                            )}
                            options={[
                                {
                                    label: 'All campaigns',
                                    value: 'all',
                                },
                                ...campaigns?.map((campaign) => ({label: campaign.title, value: String(campaign.id)})),
                            ]}
                            onChange={(value: string) => setAttributes({campaignIds: value})}
                        />
                    </PanelBody>
                    <PanelBody title={__('Display Elements', 'give')} initialOpen={true}>
                        <TextControl
                            label={__('Title', 'give')}
                            value={attributes.title}
                            onChange={(value: string) => setAttributes({title: value})}
                        />
                        <ToggleControl
                            label={__('Show Anonymous', 'give')}
                            checked={attributes.showAnonymous}
                            onChange={(value: boolean) => setAttributes({showAnonymous: value})}
                        />
                        <ToggleControl
                            label={__('Show Avatar', 'give')}
                            checked={attributes.showAvatar}
                            onChange={(value: boolean) => setAttributes({showAvatar: value})}
                        />
                        <ToggleControl
                            label={__('Show Date', 'give')}
                            checked={attributes.showDate}
                            onChange={(value: boolean) => setAttributes({showDate: value})}
                        />
                        <ToggleControl
                            label={__('Show Name', 'give')}
                            checked={attributes.showName}
                            onChange={(value: boolean) => setAttributes({showName: value})}
                        />
                    </PanelBody>
                    <PanelBody title={__('Comment Settings', 'give')} initialOpen={true}>
                        <TextControl
                            label={__('Comment Length', 'give')}
                            help={__(
                                'Limits the amount of characters to be displayed on donations with comments.',
                                'give'
                            )}
                            value={String(attributes.commentLength)}
                            onChange={(value: string) => setAttributes({commentLength: Number(value)})}
                        />
                        <TextControl
                            label={__('Read More Text', 'give')}
                            value={attributes.readMoreText}
                            onChange={(value: string) => setAttributes({readMoreText: value})}
                        />
                        <TextControl
                            label={__('Comments Per Page', 'give')}
                            help={__('Set the number of comments to be displayed on the first page load.', 'give')}
                            value={String(attributes.commentsPerPage)}
                            onChange={(value: string) => setAttributes({commentsPerPage: Number(value)})}
                        />
                    </PanelBody>
                </InspectorControls>
            )}
        </figure>
    );
}
