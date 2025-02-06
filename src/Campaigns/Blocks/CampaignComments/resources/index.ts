import metadata from '../block.json';
import Edit from './editor/edit';
import initBlock from '../../shared/utils/init-block';
import initApp from './app';

const {name} = metadata;

export {metadata, name};
export const settings = {
    edit: Edit,
};

export const init = () => {
    initBlock({metadata, settings, name});
    initApp();
};
