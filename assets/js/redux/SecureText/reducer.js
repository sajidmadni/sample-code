import { combineReducers } from 'redux';

import Chat from './chat/reducers';
import Layout from './layout/reducer';

export default combineReducers({
    Chat,
    Layout
});