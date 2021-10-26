import routes from '../../../routes/sidebar';
import { NavLink, Route } from 'react-router-dom';
import SidebarSubmenu from './SidebarSubmenu';
import { IonIcon, IonItem, IonLabel } from "@ionic/react";

function SidebarContent() {
  return (
    <div className="py-4 text-gray-500 dark:text-gray-400">
      <a href="/">
        <div className="flex items-center ml-3">
          <img src="https://cdn.adam-rms.com/img/logoicon.png" alt="AdamRMS Logo"/>
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
                <IonIcon className="w-5 h-5 pr-5" aria-hidden="true" icon={route.icon || ""} />
                <IonLabel>{route.name}</IonLabel>
              </NavLink>
            </li>
          )
        )}
      </ul>
    </div>
  )
}

export default SidebarContent