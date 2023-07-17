import {
	SET_ACTIVE_TAB,
	OPEN_USER_PROFILE_SIDEBAR,
	CLOSE_USER_PROFILE_SIDEBAR,
	SET_CONVERSATION_NAME_IN_OPEN_CHAT,
	IS_NEW_THREAD_OPEN,
	IS_FIRST_TIME_THREAD_API_CALL_STATUS
} from "./constants";

export const setActiveTab = (tabId) => ({
	type: SET_ACTIVE_TAB,
	payload: tabId
});

export const openUserSidebar = () => ({
	type: OPEN_USER_PROFILE_SIDEBAR
});

export const closeUserSidebar = () => ({
	type: CLOSE_USER_PROFILE_SIDEBAR
});

export const setconversationNameInOpenChat = (conversationName) => ({
	type: SET_CONVERSATION_NAME_IN_OPEN_CHAT,
	payload: conversationName
});

export const openNewThread = (data) => ({
	type: IS_NEW_THREAD_OPEN,
	payload: data
});

export const setFirstTimeThreadAPicallStatus = (data) => ({
	type: IS_FIRST_TIME_THREAD_API_CALL_STATUS,
	payload: data
});
