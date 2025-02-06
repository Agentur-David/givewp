import './styles.scss';
import {FallbackAvatarIcon} from '../Icons';
import {useState} from 'react';

export type attributeProps = {
    title: string;
    commentLength: number;
    readMoreText: string;
    showAvatar: boolean;
    showDate: boolean;
    showName: boolean;
    showAnonymous: boolean;
};

export type dataProps = {
    campaignId: string;
    comment: string;
    date: string;
    campaignTitle: string;
    donorName: string;
};

type CampaignCommentCardProps = {attributes: attributeProps; data: dataProps; key: string};

export default function CampaignCommentCard({attributes, data, key}: CampaignCommentCardProps) {
    const [fullComment, setFullComment] = useState(false);
    const {comment, date, campaignTitle, donorName} = data;
    const {title, commentLength, readMoreText, showAvatar, showDate, showName, showAnonymous} = attributes;

    const truncatedComment = comment
        ? comment.split(' ').slice(0, commentLength).join(' ') +
          (comment.split(' ').length > commentLength ? '...' : '')
        : '';

    return (
        <div className={'givewp-campaign-comment-block'} key={key}>
            <h4 className={'givewp-campaign-comment-block__title'}>{title}</h4>
            <div className={'givewp-campaign-comment-block__card'}>
                {/*TODO:: display avatar data*/}
                {showAvatar && <div className="givewp-campaign-comment-block__avatar">{<FallbackAvatarIcon />}</div>}
                <div className={'givewp-campaign-comment-block__content'}>
                    {showName && <p className={'givewp-campaign-comment-block__donor-name'}>{donorName}</p>}
                    <p className={'givewp-campaign-comment-block__details'}>
                        {campaignTitle}
                        <span className={'givewp-campaign-comment-block__ellipse'} />
                        {showDate && date}
                    </p>
                    <p className={'givewp-campaign-comment-block__comment'}>
                        {fullComment ? comment : truncatedComment}
                    </p>
                    {comment?.length > commentLength && fullComment === false && (
                        <button
                            className={'givewp-campaign-comment-block__read-more'}
                            onClick={() => setFullComment(!fullComment)}
                        >
                            {readMoreText}
                        </button>
                    )}
                </div>
            </div>
        </div>
    );
}
