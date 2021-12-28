import { Route } from "react-router";
import Asset from "../components/assets/Asset";
import AssetType from "../components/assets/AssetType";
import AssetTypeList from "../components/assets/AssetTypeList";

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
    </>
  );
}
