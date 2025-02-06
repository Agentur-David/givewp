import CampaignCommentCard, {attributeProps, dataProps} from '../shared/components/CommentCard';

interface AppProps {
    data: dataProps[];
    attributes: attributeProps;
}

export default function App({data, attributes}: AppProps) {
    const normalizedData = Array.isArray(data) ? data : [data];

    return (
        <>
            {normalizedData?.map((item) => (
                <CampaignCommentCard key={item.campaignId} attributes={attributes} data={item} />
            ))}
        </>
    );
}
