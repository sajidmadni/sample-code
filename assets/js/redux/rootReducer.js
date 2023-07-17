import { combineReducers } from "redux";
import secureTextReducer from "./SecureText/reducer";
import smartTaskReducer from "./Task/reducer";

const rootReducer = combineReducers({
    secureText: secureTextReducer,
    smartTasks: smartTaskReducer
});

export default rootReducer;