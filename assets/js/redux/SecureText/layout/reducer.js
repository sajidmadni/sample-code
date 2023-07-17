// @flow
import {
	SET_ACTIVE_TAB,
	OPEN_USER_PROFILE_SIDEBAR,
	CLOSE_USER_PROFILE_SIDEBAR,
	SET_CONVERSATION_NAME_IN_OPEN_CHAT,
	IS_NEW_THREAD_OPEN,
	IS_FIRST_TIME_THREAD_API_CALL_STATUS
} from "./constants";

const INIT_STATE = {
	activeTab : "chat",
	userSidebar : false,
	isNewThreadOpen : false,
	isfirstTimeThreadOpenApiFinished : true,
	conversationName : "Doris Brown"
};

const Layout = (state = INIT_STATE, action) => {
	switch (action.type) {
		case SET_ACTIVE_TAB:
			return {
				...state,
				activeTab: action.payload
			};

		case OPEN_USER_PROFILE_SIDEBAR:
			return {
				...state,
				userSidebar: true
			};

		case CLOSE_USER_PROFILE_SIDEBAR:
			return {
				...state,
				userSidebar: false
			};

		case SET_CONVERSATION_NAME_IN_OPEN_CHAT:
			return {
				...state,
				conversationName: action.payload
			};
		
		case IS_NEW_THREAD_OPEN:
			return {
				...state,
				isNewThreadOpen: action.payload
			};
		
		case IS_FIRST_TIME_THREAD_API_CALL_STATUS:
			return {
				...state,
				isfirstTimeThreadOpenApiFinished: action.payload
			};
		default:
			return state;
	}
};

export default Layout;
