import React from "react";

// lazy load all the views
const Dashboard = React.lazy(() => import("../../components/Task/pages/dashboard/index"));

const publicRoutes = [
  { path: "/", component: Dashboard },

];

const routes = [ ...publicRoutes];

export { publicRoutes, routes };
