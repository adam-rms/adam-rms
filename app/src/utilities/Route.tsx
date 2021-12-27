import { Route } from "react-router";

/* Components */
import Asset from "../pages/assets/Asset";
import AssetType from "../pages/assets/AssetType";
import AssetTypeList from "../pages/assets/AssetTypeList";
import Project from "../pages/projects/Project";
import ProjectList from "../pages/projects/ProjectList";

/**
 * Add all routes to this component
 * @returns <Routes />
 */
export function Routes() {
  return (
    <>
      {/* Assets */}
      <Route path="/assets/" component={AssetTypeList} exact />
      <Route path="/assets/:type" component={AssetType} exact />
      <Route path="/assets/:type/:asset" component={Asset} exact />

      {/* Projects */}
      <Route path="/projects/" component={ProjectList} exact />
      <Route path="/projects/:projectId" component={Project} exact />
    </>
  );
}
