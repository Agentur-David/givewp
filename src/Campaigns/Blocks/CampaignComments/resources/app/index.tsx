import {createRoot} from '@wordpress/element';
import App from './app';

const roots = document.querySelectorAll('.givewp-campaign-comment-block');

export default function initApp() {
    return roots.forEach((root) => {
        const attributes = root.getAttribute('data-attributes');
        const data = root.getAttribute('data-comments');

        createRoot(root).render(<App attributes={JSON.parse(attributes)} data={JSON.parse(data)} />);
    });
}
