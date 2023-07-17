import React, {Suspense} from 'react';
import { BrowserRouter, Switch, Route, Redirect } from 'react-router-dom';

//import routes
import { publicRoutes } from './routes';

//import layouts
import NonAuthLayout from "../../components/SecureText/layouts/NonAuth";

const AppRoute = ({ component: Component, layout: Layout, ...rest }) => {
    return <Route {...rest} render={props => {
        return <Layout><Component {...props} /></Layout>
    }} />  
}

/**
 * Main Route component
 */
const Routes = (props) => {
    return (
        <BrowserRouter>
            <React.Fragment>
            <Suspense fallback = {<div></div>} >
                <Switch>
                    {publicRoutes.map((route, idx) =>
                        <AppRoute path={route.path} layout={NonAuthLayout} component={route.component} key={idx}  />
                    )}
                </Switch>
                </Suspense>
            </React.Fragment>
        </BrowserRouter>
    );
}

export default Routes;