import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';

registerBlockType('cwcc/checkout', {
    title: __('Steps Checkout', 'cwcc'),
    icon: 'cart',
    category: 'common',
    edit: () => {
        return (
            <div>
                <h2>{__('Checkout Block', 'cwcc')}</h2>
                <p>{__('This block will display the checkout form in steps.', 'cwcc')}</p>
            </div>
        );
    },
    save: () => {
        return null;
    },
});
