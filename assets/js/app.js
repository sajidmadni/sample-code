/*This is the base component*/
import "react-app-polyfill/ie11";
import "react-app-polyfill/stable";
import React, {Component} from 'react';
import ReactDOM from 'react-dom';
import SecureText from "./components/SecureText/SecureText";
import store from './redux/store';
import {Provider} from 'react-redux';
import {MemoryRouter} from "react-router-dom";
import Routes from './routes/SecureText/';
import TaskRoutes from './routes/Task/';
import {TASK_BASE_URI} from "./constants/general";

class App extends Component {
    render() {
        return (
            <Provider store={store}>
                {
                    (window.location.pathname == TASK_BASE_URI) ?
                    <TaskRoutes />
                    :
                    <Routes />
                }
            </Provider>
        );
    }
}

ReactDOM.render(<App/>, document.getElementById('root'));