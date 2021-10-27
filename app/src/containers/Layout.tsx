import React, { useContext, Suspense, useEffect, lazy } from 'react';
import { Switch, Route, Redirect, useLocation } from 'react-router-dom';
import routes from '../routes';

import Sidebar from '../components/Shared/Sidebar';
import Header from '../components/Shared/Header';
import Main from './Main';
import ThemedSuspense from '../components/ThemedSuspense';
import { SidebarContext } from '../context/SidebarContext';
import Footer from "../components/Shared/Footer";

const Page404 = lazy(() => import('../pages/404'));

function Layout() {
  const { isSidebarOpen, closeSidebar } = useContext(SidebarContext);
  let location = useLocation();

  useEffect(() => {
    closeSidebar()
  }, [location]);

  return (
    <div
      className={`flex h-screen bg-gray-50 dark:bg-gray-900 ${isSidebarOpen && 'overflow-hidden'}`}
    >
      <Sidebar />

      <div className="flex flex-col flex-1 w-full">
        <Header />
        <Main>
          <Suspense fallback={<ThemedSuspense />}>
            <Switch>
              {routes.map((route, i) => {
                return route.component ? (
                  <Route
                    key={i}
                    exact={true}
                    path={`${route.path}`}
                    render={(props) => <route.component />}
                  />
                ) : null
              })}
              <Redirect exact from="/" to="/dashboard" />
              <Route component={Page404} />
            </Switch>
          </Suspense>
        </Main>
        <Footer />
      </div>
    </div>
  );
};

export default Layout;
