import {
    ACTIVE_USER,
    FULL_USER,
    ADD_LOGGED_USER,
    CREATE_GROUP,
    CREATE_TEMP_GROUP,
    GET_CONVERSATIONS,
    GET_MESSAGES_THREAD,
    GET_RECIPIENTS_LIST,
    Active_Group_Read_Status,
    UPDATE_TEMP_GROUP,
    GET_GROUPS,
    SET_GROUP_MEMBERS,
    SET_GROUP_PRIV,
    SET_GROUP_MESSAGES_THREAD,
    ACTIVE_GROUP,
    CREATE_TEMP_SINGLE_CHAT,
    REMOVE_GROUP,
    SET_POLL_DATA,
    SET_MY_USER_ID,
    LOAD_GROUP, CLEAR_FROM_RECENT_CHAT, UPDATE_TEMP_SINGLE_CHAT, REMOVE_MEMBER_FROM_GROUP,
    TAB_OPEN_BY,
    USERS_LAST_UNREAD_MESSAGE_ID,
    USERS_LAST_TOP_MESSAGE_ID
} from './constants';
import data from "bootstrap/js/src/dom/data";

const INIT_STATE = {
	my_user : "",
	my_presence : "",
    my_name : "",
	active_user : -1,
	active_group : -1,
    activeGroupReadStatus : "",
    selectedContacts : [],
    users: [],
    groups : [],
    contacts : [
        { id : 1, name : "Albert Rodarte" },
        { id : 2, name : "Allison Etter" },
        { id : 3, name : "Craig Smiley" },
        { id : 4, name : "Daniel Clay" }
    ],
    tabOpenBy : "chats",
    page_per_limit: 10,
    usersLastUnreadMessageId: [],       // Used for bottom pagination
    usersLastTopPageMessageId: [],      // Used for top pagination
};
function filterRemovedContacts(state, usersList){
    let userID = state.my_user;
    let _usersList = usersList;
    if(localStorage['user_'+userID] && localStorage.getItem('user_'+userID) !== null) {
        let removedContacts = JSON.parse(localStorage.getItem('user_'+userID));
         _usersList = usersList.filter((user) => removedContacts.indexOf(user.id) == -1);
    }
    return _usersList;
}

const Chat = (state = INIT_STATE, action) => {
    switch (action.type) {
        case GET_CONVERSATIONS:
            return {
                ...state,
                users : filterRemovedContacts(state, action.users)
            };

        case GET_GROUPS:
            return {
                ...state,
                groups : action.groups
            };

        case GET_MESSAGES_THREAD:
            const _newConversations = state.users.map( conversation => {
                return conversation.id == action.conversationId
                    ? Object.assign({}, conversation, {messages: action.messages})
                    : conversation
            });

            return {
                ...state,
                users: _newConversations
            };

        case LOAD_GROUP:
            const _newGroupConversations = state.users.map( conversation => {
                if(action.isUpdatedMessageThread){
                    return conversation.id == action.conversationId
                        ? Object.assign({}, conversation, {messages: action.messages, members: action.members, priv: action.priv, owner: action.owner})
                        : conversation
                } else {
                    return conversation.id == action.conversationId
                        ? Object.assign({}, conversation, {members: action.members, priv: action.priv, owner: action.owner})
                        : conversation
                }
            });

            const _newGroupsConversation = state.groups.map( group => {
                if(action.isUpdatedMessageThread){
                    return group.id == action.conversationId
                        ? Object.assign({}, group, {messages: action.messages, members: action.members, priv: action.priv, owner: action.owner})
                        : group
                } else {
                    return group.id == action.conversationId
                        ? Object.assign({}, group, {members: action.members, priv: action.priv, owner: action.owner})
                        : group
                }
            });
            return {
                ...state,
                users: _newGroupConversations,
                groups: _newGroupsConversation
            };

        case SET_GROUP_MESSAGES_THREAD:
            const _newGroupsForMessages = state.groups.map( group => {
                return group.id == action.groupId
                    ? Object.assign({}, group, {messages: action.messages})
                    : group
            });

            const _newUsersForMessages = state.users.map( user => {
                return user.id == action.groupId
                    ? Object.assign({}, user, {messages: action.messages})
                    : user
            });

            return {
                ...state,
                groups: _newGroupsForMessages,
                users: _newUsersForMessages
            };

        case ACTIVE_GROUP:
            return {
                ...state,
                active_group : action.groupId };

        case Active_Group_Read_Status:
            return {
                ...state,
                activeGroupReadStatus : action.payload
            };

        case ACTIVE_USER:
            return {
            	...state,
                active_user : action.payload };

        case FULL_USER:
            return { 
            	...state,
                users : action.payload };

        case ADD_LOGGED_USER:
            const newUser =  action.payload
            return{
                ...state, users : [
                    ...state.users, newUser
                ]
            };

        case CREATE_GROUP :
            const newGroup =  action.payload
            return {
                ...state, groups : [
                    ...state.groups, newGroup
                ]
            }

        case CREATE_TEMP_GROUP :
            const newTempGroup =  action.payload

            // Update the temp group storage
            let userTempGroups = JSON.parse(localStorage.getItem('temp_groups_user_'+state.my_user));
            if(!userTempGroups){
                userTempGroups = [];
            }
            userTempGroups.unshift(newTempGroup);
            localStorage.setItem('temp_groups_user_'+state.my_user, JSON.stringify(userTempGroups));

            // Add into the conversation
            return {
                ...state, groups : [
                    ...state.groups, newTempGroup
                ],
                ...state, users : [
                    newTempGroup, ...state.users
                ]
            }

        case CREATE_TEMP_SINGLE_CHAT :
            const newTempChat =  action.payload
            // Add into the conversation
            return {
                ...state, groups : [
                    ...state.groups, newTempChat
                ],
                ...state, users : [
                    newTempChat, ...state.users
                ]
            }

        case GET_RECIPIENTS_LIST :
            const newContacts =  action.contacts
            return {
                ...state,
                contacts: newContacts
            }
        
        case UPDATE_TEMP_SINGLE_CHAT:
            const userChatData =  action.payload
            let singleUserThreadIndex = userChatData['index'];
            let newChatData = userChatData['messageObj'] ? userChatData['messageObj'] : [];
            let singleUsersUpdate = state.users;
            $.each( singleUsersUpdate, function( index, threadValue ){
                if(threadValue.id == singleUserThreadIndex){
                    singleUserThreadIndex = index;
                }
            });
            singleUsersUpdate[singleUserThreadIndex].isTempSingle = false;
            if(newChatData){
                singleUsersUpdate[singleUserThreadIndex].messages = [...singleUsersUpdate[singleUserThreadIndex].messages, newChatData];    //  Update last message under recent message thread
            }
            
            return {
                ...state,
                users: singleUsersUpdate
            };

        case UPDATE_TEMP_GROUP:
            const groupData =  action.payload
            let groupId = groupData['groupId'];
            let groupName = groupData['groupName'];
            let threadId = groupData['threadId'];
            let newGroupChatData = [];
            if(groupData['messageObj'] !== undefined){
                newGroupChatData = groupData['messageObj'];
            }
            let usersUpdate = state.users;
            let threadIndex = 0;
            $.each( usersUpdate, function( index, threadValue ){
                // before fix: threadValue.id == threadId
                // Fix issues by changing codition: When we change the group name, the another group name is getting change + When we change the Group name it got changed at the left hand side of the Group but on header it didn't got change
                if(threadValue.id == threadId){
                    threadIndex = index;
                }
            });
            usersUpdate[threadIndex].name = groupName;
            usersUpdate[threadIndex].id = groupId;
            usersUpdate[threadIndex].saved = true;
            usersUpdate[threadIndex].owner = state.my_user;
            if(newGroupChatData){
                usersUpdate[threadIndex].messages = [...usersUpdate[threadIndex].messages, newGroupChatData];    //  Update last message under recent message thread
            }
            

            const _newGroupsN = state.groups.map( group => {
                return group.id == groupId
                    ? Object.assign({}, group, {name: groupName})
                    : group
            });

            return {
                ...state,
                users: usersUpdate,
                groups: _newGroupsN
            };

        case REMOVE_GROUP:
            const _groupList = state.groups.filter((group) => group.id !== action.groupId);
            const _usersList = state.users.filter((user) => user.id !== action.groupId);
            return {
                ...state,
                groups: _groupList,
                users: _usersList
            };

        case REMOVE_MEMBER_FROM_GROUP:
            const _updateGroups = state.groups.map( group => {
                return group.id == action.groupId
                    ? Object.assign({}, group, {members: group.members.filter((member) => member.id !== action.memberId)})
                    : group
            });


            const _updateRecentGroup = state.users.map( user => {
                return user.id == action.groupId
                    ? Object.assign({}, user, {members: user.members.filter((member) => member.id !== action.memberId)})
                    : user
            });

            //update local storage
            let userTempGrp_ = JSON.parse(localStorage.getItem('temp_groups_user_'+state.my_user));
            if(userTempGrp_){
                const _newTempGrp_ = userTempGrp_.map( group => {
                    return group.id == action.groupId
                        ? Object.assign({}, group, {members: group.members.filter((member) => member.id !== action.memberId)})
                        : group
                });
                localStorage.setItem('temp_groups_user_'+state.my_user, JSON.stringify(_newTempGrp_));
            }

            return {
                ...state,
                groups: _updateGroups,
                users: _updateRecentGroup
            };

        case SET_GROUP_MEMBERS:
            const _addMemGroups = state.groups.map( group => {
                return group.id == action.groupId
                    ? Object.assign({}, group, {members: action.members})
                    : group
            });
            const _addMemRecentGroup = state.users.map( user => {
                return user.id == action.groupId
                    ? Object.assign({}, user, {members: action.members})
                    : user
            });

            //update local storage
            let userTempGroup_ = JSON.parse(localStorage.getItem('temp_groups_user_'+state.my_user));
            if(userTempGroup_){
                const __newTempGrp = userTempGroup_.map( group => {
                    return group.id == action.groupId
                        ? Object.assign({}, group, {members: action.members})
                        : group
                });
                localStorage.setItem('temp_groups_user_'+state.my_user, JSON.stringify(__newTempGrp));
            }

            return {
                ...state,
                groups: _addMemGroups,
                users: _addMemRecentGroup
            };

        case CLEAR_FROM_RECENT_CHAT:

            let userTempGrp = JSON.parse(localStorage.getItem('temp_groups_user_'+state.my_user));
            if(userTempGrp){
                const _newTempGrp = userTempGrp.filter((group) => group.id !== action.chatId);
                localStorage.setItem('temp_groups_user_'+state.my_user, JSON.stringify(_newTempGrp));
            }

            const _usersListCleared = state.users.filter((user) => user.id !== action.chatId);
            return {
                ...state,
                active_user: -1,
                users: _usersListCleared
            };

        case SET_POLL_DATA:
            if(state.users.length <1){
                return {
                    ...state,
                    users : filterRemovedContacts(state, action.users)
                };
            }
            let userID = state.my_user;
            let currentActiveTabId = state.active_user;
            let currentActiveUserId = -1;
            if(currentActiveTabId != -1){
                currentActiveUserId = state.users[currentActiveTabId].id;
            }

            let removedCon = [];
            let _newPollData = action.users;
            if(localStorage['user_'+userID] && localStorage.getItem('user_'+userID) !== null) {
                _newPollData = [];
                let removedCon = JSON.parse(localStorage.getItem('user_'+userID));
                action.users.map( (conversation, key) => {
                    if(!removedCon.includes(conversation.id)){
                        _newPollData.push(conversation);
                    }
                });
            }

            _newPollData = _newPollData.map( (conversation, key) => {
                let _oldUserData = state.users.find(e => e.id === conversation.id);
                if(conversation.id == currentActiveUserId){
                    currentActiveTabId = key;
                }
                return _oldUserData && conversation.id == _oldUserData.id
                    ? Object.assign({}, conversation, {messages: conversation.messages, members: _oldUserData.members, priv: conversation.priv, owner: _oldUserData.owner})
                    : conversation
            });

            if(_newPollData != state.users){
                return {
                    ...state,
                    active_user: currentActiveTabId,
                    users : filterRemovedContacts(state, _newPollData)
                };
            }else {
                return {
                    ...state
                };
            }

        case SET_MY_USER_ID:
            return {
                ...state,
                my_user : action.userId,
                my_presence : action.userPresence,
                my_name : action.userName,
                isCovered : action.isCovered,
            };
        case TAB_OPEN_BY:
            return {
                ...state,
                tabOpenBy: action.payload
            };
        case USERS_LAST_UNREAD_MESSAGE_ID:
            let usersArrIndex = action.userIndex;
            let unreadMessageId = action.unreadMessageId;
            let usersUnreadMessageArr = state.usersLastUnreadMessageId;
            usersUnreadMessageArr[usersArrIndex] = unreadMessageId;
            return {
                ...state,
                usersLastUnreadMessageId: usersUnreadMessageArr,
            };
        case USERS_LAST_TOP_MESSAGE_ID:
            let usersTopArrIndex = action.userIndex;
            let topMessageId = action.topMessageId;
            let isClearArr = action.isClearArr;
            for( var i = 0; i < state.usersLastTopPageMessageId.length; i++){
                if ( state.usersLastTopPageMessageId[i] != usersTopArrIndex) {
                    state.usersLastTopPageMessageId.splice(i, 1);
                    i--;
                }
            }
            if(isClearArr){
                state.usersLastTopPageMessageId = [];
            }
            let usersTopMessageArr = state.usersLastTopPageMessageId;
            usersTopMessageArr[usersTopArrIndex] = topMessageId;
            return {
                ...state,
                usersLastTopPageMessageId: usersTopMessageArr,
            };
    default: return { ...state };
    }
}

export default Chat;