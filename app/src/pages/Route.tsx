import { Route } from "react-router";
import Asset from "../components/assets/Asset";
import AssetType from "../components/assets/AssetType";
import AssetTypeList from "../components/assets/AssetTypeList";

export function Routes() {
    return (
        <>
            <Route path="/assets/" component={AssetTypeList} exact/>
            <Route path="/assets/:type" component={AssetType} exact/>
            <Route path="/assets/:type/:asset" component={Asset} exact />
        </>
    )
}