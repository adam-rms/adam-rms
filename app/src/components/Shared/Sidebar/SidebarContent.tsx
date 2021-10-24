import React from 'react';
import routes from '../../../routes/sidebar';
import { NavLink, Route } from 'react-router-dom';
import * as Icons from '../../../icons';
import SidebarSubmenu from './SidebarSubmenu';
import { Button } from '@windmill/react-ui';
import { IIcon } from "../../../utils/demo";


function Icon({ icon, ...props }: IIcon){
  // @ts-ignore
  const Icon= Icons[icon];
  return <Icon {...props} />
}

function SidebarContent() {
  return (
    <div className="py-4 text-gray-500 dark:text-gray-400">
      <a href="/#">
        <div className="flex items-center ml-3">
          <img src="https://cdn.adam-rms.com/img/logoicon.png"/>
          <p className="ml-3 text-lg font-bold text-gray-800 dark:text-white">AdamRMS</p>
        </div>
      </a>
      <ul className="mt-6">
        {routes.map((route) =>
          route.routes ? (
            <SidebarSubmenu route={route} key={route.name} />
          ) : (
            <li className="relative px-6 py-3" key={route.name}>
              <NavLink
                exact
                to={route.path || ""}
                className="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"
                activeClassName="text-gray-800 dark:text-gray-100"
              >
                <Route path={route.path} exact={route.exact}>
                  <span
                    className="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg"
                    aria-hidden="true"
                  ></span>
                </Route>
                <Icon className="w-5 h-5" aria-hidden="true" icon={route.icon || ""} />
                <span className="ml-4">{route.name}</span>
              </NavLink>
            </li>
          )
        )}
      </ul>
    </div>
  )
}

export default SidebarContent