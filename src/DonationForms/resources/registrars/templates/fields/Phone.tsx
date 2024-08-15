import {PhoneProps} from '@givewp/forms/propTypes';
import {useEffect, useState} from 'react';
import IntlTelInput from 'intl-tel-input/react';
import 'intl-tel-input/build/css/intlTelInput.css';
import InputMask from 'react-input-mask';

import styles from '../styles.module.scss';

/**
 * @since 3.9.0
 */
export default function Phone({
    Label,
    ErrorMessage,
    placeholder,
    description,
    phoneFormat,
    inputProps,
    fieldError,
    intlTelInputSettings,
}: PhoneProps) {
    const FieldDescription = window.givewp.form.templates.layouts.fieldDescription;
    const {useFormContext} = window.givewp.form.hooks;
    const {setValue, setError, trigger} = useFormContext();

    const isIntlTelInput = intlTelInputSettings.initialCountry && intlTelInputSettings.utilsScriptUrl;

    useEffect(() => {
        if (!isIntlTelInput) {
            return;
        }

        // This timeout is necessary to properly load the utilsScript - without this, the autoPlaceholder feature doesn't work.
        const interval = setTimeout(() => {
            window.intlTelInputGlobals.loadUtils(intlTelInputSettings.utilsScriptUrl);
            // It's necessary to fix a missing left padding that can happen in certain cases.
            document.querySelectorAll('.iti__tel-input').forEach(function (input: HTMLInputElement) {
                // @ts-ignore
                const countryContainerWidth = document.querySelector('.iti__country-container').offsetWidth;
                input.style.paddingLeft = String(countryContainerWidth + 4) + 'px';
            });
        }, 100);

        return () => {
            clearInterval(interval);
        };
    }, []);

    const [country, setCountry] = useState<string>(intlTelInputSettings.initialCountry);

    const onChangeNumber = (number: string) => {
        if (number && !window.intlTelInputUtils.isValidNumber(number, country)) {
            const errorCode = window.intlTelInputUtils.getValidationError(number, country);
            setValue(inputProps.name, errorCode);
            setError(inputProps.name, {type: 'custom', message: intlTelInputSettings.errorMap[errorCode]});
        } else {
            setValue(inputProps.name, number);
            trigger(inputProps.name, {shouldFocus: false});
        }
    };

    return (
        <label>
            <Label />
            {description && <FieldDescription description={description} />}

            <div className={styles.phoneField}>
                {isIntlTelInput ? (
                    <>
                        <input type={'hidden'} placeholder={placeholder} {...inputProps} />
                        <IntlTelInput
                            onChangeCountry={setCountry}
                            onChangeNumber={onChangeNumber}
                            initOptions={{
                                initialCountry: intlTelInputSettings.initialCountry,
                                showSelectedDialCode: intlTelInputSettings.showSelectedDialCode,
                                strictMode: intlTelInputSettings.strictMode,
                                i18n: intlTelInputSettings.i18n,
                                useFullscreenPopup: intlTelInputSettings.useFullscreenPopup,
                            }}
                            inputProps={{
                                'aria-invalid': fieldError ? 'true' : 'false',
                            }}
                        />
                    </>
                ) : phoneFormat === 'domestic' ? (
                    <InputMask type={'phone'} {...inputProps} mask={'(999) 999-9999'} placeholder={placeholder} />
                ) : (
                    <input type={'phone'} placeholder={placeholder} {...inputProps} />
                )}

                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        fillRule="evenodd"
                        clipRule="evenodd"
                        d="M16.0554 21.8806C12.8621 20.9743 9.84919 19.2639 7.33755 16.7523C4.82592 14.2406 3.11557 11.2277 2.20923 8.03448C2.20386 8.01555 2.19853 7.99679 2.19324 7.97817C2.04573 7.45916 1.93134 7.05669 1.9297 6.50479C1.92782 5.87402 2.13333 5.08378 2.44226 4.53384C2.97354 3.58807 4.11431 2.37601 5.09963 1.87764C5.95097 1.44704 6.95637 1.44704 7.80771 1.87764C8.64995 2.30364 9.58794 3.2572 10.1109 4.06146C10.7573 5.0558 10.7573 6.33767 10.1109 7.33201C9.93761 7.59846 9.69068 7.84497 9.40402 8.13114C9.31476 8.22025 9.21651 8.28464 9.28176 8.42053C9.92958 9.76981 10.8131 11.0354 11.9337 12.1561C13.0544 13.2768 14.32 14.1603 15.6693 14.8081C15.81 14.8756 15.8654 14.7792 15.9587 14.6858C16.2449 14.3991 16.4914 14.1522 16.7578 13.979C17.7522 13.3325 19.034 13.3325 20.0284 13.979C20.8111 14.4879 21.7895 15.4465 22.2122 16.2821C22.6428 17.1335 22.6428 18.1389 22.2122 18.9902C21.7138 19.9755 20.5018 21.1163 19.556 21.6476C19.0061 21.9565 18.2158 22.162 17.585 22.1601C17.0331 22.1585 16.6307 22.0441 16.1117 21.8966C16.0931 21.8913 16.0743 21.886 16.0554 21.8806Z"
                        fill="rgb(102, 102, 102)"
                    />
                </svg>
            </div>
            <ErrorMessage />
        </label>
    );
}
