import {
    CREATE_TASK,
    GET_TASKS_LIST,
    COMPLETE_TASK,
    SET_ACTIVE_TAB_TASK,
    FILTER_TASKS_BY_SELECTION, SET_CURRENT_USER_ID, ALL
} from './constants';
import {quotesList} from "../../../constants/quotesList";
import {setCurrentUserId} from "./actions";

const INIT_STATE = {
    current_user_id : "",
    activeTab : "all",
    tasks : [],
    filteredTasksList : [],
    quotes : quotesList,
    filterCriteria:ALL
};

const Task = (state = INIT_STATE, action) => {
    switch (action.type) {
        case SET_ACTIVE_TAB_TASK:
            return {
                ...state,
                activeTab: action.payload
            };

        case CREATE_TASK:
            const newTask =  action.payload
            let newTaskList = [   ...state.tasks, newTask ];
            return {
                ...state,
                tasks : newTaskList,
                filteredTasksList : newTaskList
            };

        case GET_TASKS_LIST:
            return {
                ...state,
                tasks : action.tasks,
                filteredTasksList : action.tasks
            };

        case FILTER_TASKS_BY_SELECTION:
            return {
                ...state,
                filteredTasksList : action.filteredTasksList,
                filterCriteria : action.filterCriteria
            };

        case SET_CURRENT_USER_ID:
            return {
                ...state,
                current_user_id : action.userId
            };

        case COMPLETE_TASK:
            const taskData =  action.payload
            let taskId = taskData['id'];
            let completedAt = taskData['completed_at'];
            let completedBy = taskData['completed_by'];
            let isStat = taskData['is_stat'];
            let remarks = taskData['completion_remarks'];
            return {
                ...state,
                tasks: state.tasks.map(task => task.id === taskId ?
                    // transform the one with a matching id
                    { ...task,
                        id: taskId,
                        completed_at: completedAt,
                        completion_remarks: remarks,
                        completed_by: completedBy,
                        is_stat: isStat
                    } :
                    // otherwise return original user
                    task
                ),
                filteredTasksList: state.filteredTasksList.map(filterTask => filterTask.id === taskId ?
                    // transform the one with a matching id
                    { ...filterTask,
                        id: taskId,
                        completed_at: completedAt,
                        completion_remarks: remarks,
                        completed_by: completedBy,
                        is_stat: isStat
                    } :
                    // otherwise return original user
                    filterTask
                )
            };

         default: return { ...state };
    }
}

export default Task;