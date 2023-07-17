import {
    CREATE_TASK,
    GET_TASKS_LIST,
    COMPLETE_TASK,
    SET_ACTIVE_TAB_TASK,
    FILTER_TASKS_BY_SELECTION,
    ASSIGNED_TO_OTHERS,
    TASKS_FOR_ME,
    PENDING_TASKS,
    COMPLETED_TASKS,
    CREATED_TODAY,
    CREATED_THIS_WEEK,
    CREATED_LAST_WEEK, PRIORITY_TASKS, SET_CURRENT_USER_ID
} from './constants';
import moment from 'moment';

export const setSmartTasks = (data) =>{
    return {
        type: GET_TASKS_LIST,
        tasks: data
    }
}

export  const fetchSmartTasks = () => dispatch => {
    let formData = new FormData();
    const requestOptions = {
        method: 'POST',
        body: formData
    };
    return fetch('/smart/list-tasks', requestOptions)
        .then(data => data.json())
        .then(data => {
            let tasksList = data.response;
            return dispatch(setSmartTasks(tasksList));
        });
}

export const setSmartTask = (task) => ({
    type : CREATE_TASK,
    payload : task,
});
export  const createSmartTask = (taskData) => dispatch => {
    let formData = new FormData();
    formData.append('description', taskData.description);
    formData.append('is_stat', taskData.isStat);
    formData.append('assignee', taskData.assigneeId);

    const requestOptions = {
        method: 'POST',
        body: formData
    };
    return fetch('/smart/create-task', requestOptions)
        .then(data => data.json())
        .then(data => {
            return dispatch(setSmartTask(data.response));
        });
}

export const setCompleteSmartTask = (task) => ({
    type : COMPLETE_TASK,
    payload : task,
});
export  const completeSmartTask = (taskData) => dispatch => {
    let formData = new FormData();
    formData.append('task_id', taskData.task_id);
    formData.append('remarks', taskData.remarks);
    if(taskData.isSeparate){
        formData.append('is_separate', taskData.isSeparate);
    }

    const requestOptions = {
        method: 'POST',
        body: formData
    };
    return fetch('/smart/complete-task', requestOptions)
        .then(data => data.json())
        .then(data => {
            if(data.error){
            } else {
                return dispatch(setCompleteSmartTask(data.response));
            }

        });
}

export const setActiveTab = (tabId) => ({
    type: SET_ACTIVE_TAB_TASK,
    payload: tabId
});

export const setFilteredTasks = (filteredTasksList, filterCriteria) => ({
    type: FILTER_TASKS_BY_SELECTION,
    filterCriteria: filterCriteria,
    filteredTasksList: filteredTasksList
});

export const filterTasksBySelection = (taskList, filterCriteria, current_user_id) => dispatch => {
    let filteredTasksList = [];
    switch (filterCriteria) {
        case TASKS_FOR_ME:
            filteredTasksList = taskList.filter(task => task.assignee == current_user_id);
            break;
        case ASSIGNED_TO_OTHERS:
            filteredTasksList = taskList.filter(task => task.assignee != current_user_id);
            break;
        case PENDING_TASKS:
            filteredTasksList = taskList.filter(task => task.completed_at == null);
            break;
        case PRIORITY_TASKS:
            filteredTasksList = taskList.filter(task => task.is_stat == true);
            break;
        case COMPLETED_TASKS:
            filteredTasksList = taskList.filter(task => task.completed_at != null);
            break;
        case CREATED_TODAY:
            taskList.filter(function(task) {
                if(new moment(task.created_at).isSame(moment(), 'day')){
                    filteredTasksList.push(task);
                }
            });
            break;
        case CREATED_THIS_WEEK:
            var now = moment();
            taskList.filter(function(task) {
                var input = moment(task.created_at);
                var isThisWeek = (now.isoWeek() == input.isoWeek())
                if(isThisWeek){
                    filteredTasksList.push(task);
                }
            });
            break;
        case CREATED_LAST_WEEK:
            var now = moment();
            taskList.filter(function(task) {
                var input = moment(task.created_at);
                var isThisWeek = (now.isoWeek()-1 == input.isoWeek())
                if(isThisWeek){
                    filteredTasksList.push(task);
                }
            });
            break;
        default:
            filteredTasksList = taskList;
    }
    return dispatch(setFilteredTasks(filteredTasksList, filterCriteria));
}


export const setCurrentUserId = (id) => ({
    type: SET_CURRENT_USER_ID,
    userId: id,
});