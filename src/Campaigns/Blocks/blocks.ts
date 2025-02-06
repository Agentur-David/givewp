import * as campaignTitleBlock from './CampaignTitleBlock';
import * as campaignCover from './CampaignCover';
import * as campaignDonateButton from './DonateButton';
import * as campaignComments from './CampaignComments/resources';

const getAllBlocks = () => {
    return [campaignTitleBlock, campaignDonateButton, campaignCover, campaignComments];
};

getAllBlocks().forEach((block) => {
    block.init();
});
