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
    REMOVE_GROUP, SET_POLL_DATA,
    SET_CURRENT_USER_ID,
    SET_MY_USER_ID,
    LOAD_GROUP, CLEAR_FROM_RECENT_CHAT,
    UPDATE_TEMP_SINGLE_CHAT, REMOVE_MEMBER_FROM_GROUP,
    TAB_OPEN_BY,
    USERS_LAST_UNREAD_MESSAGE_ID,
    USERS_LAST_TOP_MESSAGE_ID
} from './constants';
import store from './../../store';
import Chat from "./reducers";

export const setConversations = (data) =>{
    return {
        type: GET_CONVERSATIONS,
        users: data
    }
}

export const setGroups = (data) =>{
    return {
        type: GET_GROUPS,
        groups: data
    }
}

export  const fetchConversations = () => dispatch => {
    return fetch('/ajax_contacts_recent/')
        .then(data => data.json())
        .then(data => {
            data.contacts.forEach(function (element) {
                if(typeof element.messages === 'undefined'){ element.messages = []; }
                if(typeof element.members === 'undefined'){ element.members = []; }
                if(typeof element.priv === 'undefined'){ element.priv = [];}
                element.status = "online";
                if(typeof element.saved !== 'undefined'){
                    element.isGroup = true;
                }else{
                    element.isGroup = false;
                }
            });
            return dispatch(setConversations(data.contacts));
        });
}

export  const fetchGroups = () => dispatch => {
    return fetch('/ajax_group_list?type=GROUPS')
        .then(data => data.json())
        .then(data => {
            data.forEach(function (element) {
               if(typeof element.messages === 'undefined'){
                   element.messages = [];
               }
                element.isGroup = true;
            });
            return dispatch(setGroups(data));
        });
}

export const setGroupMembers = (data, id) =>{
    return {
        type: SET_GROUP_MEMBERS,
        members: data,
        groupId: id
    }
}

export const setGroupPriv = (data, id) =>{
    return {
        type: SET_GROUP_PRIV,
        priv: data,
        groupId: id
    }
}

export const setGroupMessagesThread = (data, id) =>{
    return {
        type: SET_GROUP_MESSAGES_THREAD,
        messages: data,
        groupId: id
    }
}

export const activeGroup = (id) => ({
    type: ACTIVE_GROUP,
    groupId : id
});

export const setMessagesThread = (data, id) =>{
    return {
        type: GET_MESSAGES_THREAD,
        messages: data,
        conversationId: id
    }
}

export const setUserMessages = (userId, messages) => ({
    type: ACTIVE_USER,
    payload : userId
});

export const activeUser = (userId) => ({
    type: ACTIVE_USER,
    payload : userId
});

export const setFullUser = (fullUser) => ({
    type: FULL_USER,
    payload : fullUser
});

export const addLoggedinUser = (userData) => ({
    type: ADD_LOGGED_USER,
    payload : userData
});

export const setGroup = (groupData) => ({
    type : CREATE_GROUP,
    payload : groupData
});

export const updateTempGroup = (groupData) => ({
    type : UPDATE_TEMP_GROUP,
    payload : groupData,
});

export const updateTempSingleChat = (chatData) => ({
    type : UPDATE_TEMP_SINGLE_CHAT,
    payload : chatData,
});

export const setTempGroup = (groupData) => ({
    type : CREATE_TEMP_GROUP,
    payload : groupData,
});
export  const createTempGroup = (groupData) => dispatch => {
    dispatch(activeUser(0));
    return dispatch(setTempGroup(groupData));
}

export const setTempSingleChat = (groupData) => ({
    type : CREATE_TEMP_SINGLE_CHAT,
    payload : groupData,
});
export  const createTempSingleChat = (contactData) => dispatch => {
    dispatch(activeUser(0));
    // Get messages thread(If already has)
    dispatch(fetchMessagesThread(contactData.id));
    return dispatch(setTempSingleChat(contactData));
}
export  const createGroup = (objData) => dispatch => {
    let formData = new FormData();

    // Get members of group & convert into comma separated string
    let selectedPhyIds = [];
    let selectedPhy = objData.members;
    let messageText = "";
    let isNewMsgScreen = true;
    Object.keys(selectedPhy).forEach(function(key) {
        if(selectedPhy[key]['id']){
            selectedPhyIds.push(selectedPhy[key]['id']);
        }
    });
    if(selectedPhyIds.length > 1){
        selectedPhyIds.toString();
        selectedPhy = selectedPhyIds;
        formData.append('text', messageText);
        formData.append('isNewMsgScreen', isNewMsgScreen);
    }
    let messagePath = `/ajax_send_group_message/null/${selectedPhy}`;

    const requestOptions = {
        method: 'POST',
        body: formData
    };

    return fetch(messagePath, requestOptions)
        .then(data => data.json())
        .then(data => {
            if(!data.error){
                // Update Group Name
                objData.name = data.groupName;
                objData.groupId = data.gid;
                dispatch(fetchConversations());
                dispatch(activeUser(0));
                return dispatch(setGroup(objData));
            }
        });
}

export const setRecipientsList = (data) =>{
    return {
        type: GET_RECIPIENTS_LIST,
        contacts: data
    }
}

export  const fetchRecipientsList = (term) => dispatch => {
    let formData = new FormData();
    formData.append('term', term);
    formData.append('react_page_limit', 20);    // Need to overirte the "page_limit" to pull less than 60 records
    const requestOptions = {
        method: 'POST',
        body: formData
    };

    return fetch('/ajax_staff_list', requestOptions)
        .then(data => data.json())
        .then(data => {
            const updatedContacts = data.contacts;
            updatedContacts.forEach(function (element) {
                if(element.presence !== 'group' && element.presence == 'dnd'){
                    element.presence = getPresenceType(element.id, element.presence, element.dndintervalstart, element.dndintervalend);
                } else if(element.presence !== 'group' && element.presence == 'leave_of_absence'){
                    element.presence = getPresenceType(element.id, element.presence, element.loaintervalstart, element.loaintervalend);
                }
            });
            return dispatch(setRecipientsList(updatedContacts));
        });
}

export const setActiveGroupReadStatus = (data) => ({
    type : Active_Group_Read_Status,
    payload : data
});

export const removeGroupFromList = (groupId) => ({
    type : REMOVE_GROUP,
    groupId : groupId
});

export const removeMemberFromGroup = (groupId, memberId) => ({
    type : REMOVE_MEMBER_FROM_GROUP,
    groupId : groupId,
    memberId : memberId
});

export const addMemberToGroup = (groupId, memberData) => ({
    type : ADD_MEMBER_TO_GROUP,
    groupId : groupId,
    memberData : memberData
});

export const clearFromRecentChatList = (chatId) => ({
    type : CLEAR_FROM_RECENT_CHAT,
    chatId : chatId
});

export const setPollData = (data) =>{
    return {
        type: SET_POLL_DATA,
        users: data
    }
}

export  const pollChatData = () => dispatch => {
    // Crashing when new message appear and then scroll top to get more old data. Need to fix that later.
    // if($(".unreadLabel") && $(".unreadLabel").length){
    //     $(".unreadLabel").closest("li").remove();
    // }

    let chatState = store.getState().secureText.Chat;
    return fetch('/ajax_contacts_recent/')
        .then(data => data.json())
        .then(data => {

            data.contacts.forEach(function (element) {
                if(typeof element.messages === 'undefined'){
                    element.messages = [
                    ];
                }
                if(typeof element.members === 'undefined'){
                    element.members = [
                    ];
                }
                if(typeof element.priv === 'undefined'){
                    element.priv = [
                    ];
                }
                element.status = "online";
                if(typeof element.saved !== 'undefined'){
                    element.isGroup = true;
                }else{
                    element.isGroup = false;
                    if(element.presence == "leave_of_absence"){
                        element.presence = getPresenceType(element.id, element.presence, element.loaintervalstart, element.loaintervalend, element.createdAt);
                    } else {
                        element.presence = getPresenceType(element.id, element.presence, element.dndintervalstart, element.dndintervalend, element.createdAt);
                    }
                    
                }
            });

            var contactsList = data.contacts;
            if (chatState.users !== contactsList) {
                let _newConversationsPoll = contactsList;
                if(chatState.active_user != -1){
                    let currActiveUSer = chatState.users[chatState.active_user];
                        _newConversationsPoll = contactsList.map( conversation => {
                            return conversation.id == currActiveUSer.id
                                ? Object.assign({}, conversation, {messages: currActiveUSer.messages, priv: conversation.priv})
                                : conversation
                    });
                    contactsList = _newConversationsPoll;
                }
            }
            const userId = $("a[name='myPresence']").attr("id");
            let userTempGroups = JSON.parse(localStorage.getItem('temp_groups_user_'+userId));
            if(userTempGroups){
                contactsList = [...userTempGroups, ...contactsList];
            }
            return dispatch(setPollData(contactsList));
        });
}

export const setMyUserId = (id, userPresence, userName, isCovered) => ({
    type: SET_MY_USER_ID,
    userId: id,
    userName: userName,
    userPresence: userPresence,
    isCovered: isCovered
});

export const loadGroup = (messages, members, priv, id, owner, isUpdatedMessageThread = true) =>{
    members.forEach(function (element) {
        if(element.presence !== 'group' && element.presence == 'dnd'){
            if(element.hasOwnProperty("isDnd") && element.isDnd){
                element.presence = "dnd";
            } else if(element.hasOwnProperty("isDnd") && !element.isDnd){
                element.presence = "available";
            } else {
                element.presence = getPresenceType(element.id, element.presence, element.dndintervalstart, element.dndintervalend);
            }
        } else if(element.presence !== 'group' && element.presence == 'leave_of_absence'){
            let hospitalTimeZone = element.hospitalTimeZone ? element.hospitalTimeZone : "America/New_York";
            element.presence = getPresenceType(element.id, element.presence, element.loaintervalstart, element.loaintervalend, null, hospitalTimeZone);
        }
    });

    return {
        type: LOAD_GROUP,
        messages: messages,
        members: members,
        priv: priv,
        conversationId: id,
        owner: owner,
        isUpdatedMessageThread: isUpdatedMessageThread
    }
}

export const fetchMessagesThread = (id) => dispatch => {
    let formData = new FormData();
    formData.append('group', 'STAFF');
    formData.append('id', id);
    const requestOptions = {
        method: 'POST',
        body: formData
    };
    let markedReadPoint = false;
    return fetch('/msg/ajax_filter_msg_group'+'?new_msg=true', requestOptions)
        .then(data => data.json())
        .then(data => {
            let conversations = [];
            Object.keys(data).forEach(function(key) {
                if(!markedReadPoint && data[key]['outbound']==false && data[key]['detail']["confirmedAt"] == null ){
                    markedReadPoint = true;
                    conversations.push({ id : 33, customLabel:true, label: /*chat.msgNew+*/" New message(s)", customClass : "unreadLabel" });
                }
                if(data[key]['detail']["createdAt"]){

                }
                let checkImageMessage = (/(gif|jpg|jpeg|tiff|png|webp|bmp)$/i).test(data[key]['detail']['file_ext']);

                let messageData = {
                    id: data[key]['detail']['id'],
                    message: data[key]['detail']['text'],
                    time: data[key]['detail']['createdAt'],
                    userType: (data[key]['outbound']) ?"sender":"receiver",
                    sender : data[key]['from']['id'],
                    senderName : data[key]['from']['name'],
                    receiver : data[key]['to']['id'],
                    receiverName : data[key]['to']['name'],
                    isImageMessage : checkImageMessage ? true : false,                    
                    isFileMessage : checkImageMessage ? false : true,
                };
                if(checkImageMessage){
                    messageData.imageMessage = (data[key]['detail']["image"]) ? [ { image : data[key]['detail']["image"] } ] : "";
                } else {
                    messageData.fileMessage = (data[key]['detail']["image"]) ? [ { image : data[key]['detail']["image"] } ] : "";
                }
                conversations.push(messageData);
            });
            return dispatch(setMessagesThread(conversations, id));
        });
}

export const markAllMessagesRead = () => dispatch => {
    const requestOptions = {
        method: 'POST'
    };

    return fetch('/pm/confirm-all-receipts', requestOptions)
        .then(data => data.json())
        .then(data => {
            return data;
        });
}

export const setTabOpenType = (data) => ({
	type: TAB_OPEN_BY,
	payload: data
});

export const setUsersLastUnreadMessageId = (userIndex, unreadMessageId) => ({
    type : USERS_LAST_UNREAD_MESSAGE_ID,
    userIndex : userIndex,
    unreadMessageId : unreadMessageId
});

export const setUsersLastTopMessageId = (userIndex, topMessageId, isClearArr = false) => ({
    type : USERS_LAST_TOP_MESSAGE_ID,
    userIndex : userIndex,
    topMessageId : topMessageId,
    isClearArr: isClearArr
});

function getPresenceType(id, presenceType, loaintervalstart = null, loaintervalend = null, createdAt = null, hospitalTimeZone = null){
    if( (presenceType == "leave_of_absence" || presenceType == "dnd") && loaintervalstart != null && loaintervalend != null){
        let timeZoneName = hospitalTimeZone != null ? hospitalTimeZone : loaintervalstart.timezone;
        let loaStartDate = loaintervalstart;
        let loaEndtDate = loaintervalend;
        if(presenceType == "leave_of_absence" && loaintervalstart.date && loaintervalend.date){
            let setCurrentDateTime = new Date().
                                toLocaleString('en-CA', {
                                year: 'numeric',
                                month: '2-digit',
                                day: '2-digit',
                                hour12: false,
                                hour: '2-digit',
                                minute: '2-digit',
                                second: '2-digit',
                                timeZone: timeZoneName,
                            }).replace(',','');
            loaStartDate = loaintervalstart.date.substring(0,19);
            loaEndtDate =  loaintervalend.date.substring(0,10)+" 23:59:59";
            presenceType = (setCurrentDateTime >= loaStartDate && setCurrentDateTime <= loaEndtDate) ? presenceType : "available";
        } else if(presenceType == "dnd" && loaintervalstart &&  loaintervalend){
            if(hospitalTimeZone != null){
                createdAt = hospitalTimeZone;
            } else if(createdAt == null){
                createdAt = "America/New_York";
            } else {
                createdAt = createdAt.timezone;
            }
            let setCurrentDateTime = new Date().
                                toLocaleString('en-CA', {
                                hour12: false,
                                hour: '2-digit',
                                minute: '2-digit',
                                timeZone: createdAt,
                            }).replace(',','');
            loaStartDate = loaintervalstart;
            loaEndtDate =  loaintervalend;
            let currentTime = setCurrentDateTime.replace(":","");
            presenceType = (currentTime >= loaStartDate && loaEndtDate >= currentTime) ? presenceType : "available";
        }
    }
    return presenceType;
}