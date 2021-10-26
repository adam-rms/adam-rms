/**
 * ⚠ These are used just to render the Sidebar!
 * You can include any link here, local or external.
 *
 * If you're looking to actual Router routes, go to
 * `routes/index.js`
 */

import { chevronDown, home } from "ionicons/icons";

interface IRoute{
  path?: string
  icon?: string
  name: string
  routes?: IRoute[]
  exact?: boolean
};

const routes: IRoute[] = [
  {
    path: '/dashboard', // the url
    icon: home, // the component being exported from icons/index.js
    name: 'Dashboard', // name that appear in Sidebar
  },
  {
    icon: chevronDown,
    name: 'Pages',
    routes: [
      // submenu
      {
        path: '/login',
        name: 'Login',
      },
      {
        path: '/create-account',
        name: 'Create account',
      },
      {
        path: '/forgot-password',
        name: 'Forgot password',
      },
      {
        path: '/app/404',
        name: '404',
      },
      {
        path: '/app/blank',
        name: 'Blank',
      },
    ],
  },
];

export type {IRoute};
export default routes;
