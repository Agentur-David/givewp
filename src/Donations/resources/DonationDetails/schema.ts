import Joi from 'joi';

/**
 *
 * @unreleased
 */

export const validationSchema = Joi.object().keys({
    totalDonation: Joi.string(),
    feeAmount: Joi.string(),
});
