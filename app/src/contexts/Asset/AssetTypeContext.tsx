import { createContext, useState } from "react";
import Api from "../../controllers/Api";

export const AssetTypeContext = createContext<any>(null);

const AssetTypeProvider: React.FC<React.ReactNode> = ({children}) => {
    const [AssetTypes, setAssetTypes] = useState<IAssetType>({
        assets: [],
        pagination: {
            page: 0,
            total: 0
        }
    });

    async function refreshAssetTypes() {
        //replace all assets
        setAssetTypes(await Api("assets/list.php", {"all": true}));
    }

    async function getMoreAssets() {
        //check if there are more pages to get
        if (AssetTypes.pagination.page < AssetTypes.pagination.total){ 
            //get assets
            let newassets:IAssetType =  await Api("assets/list.php", {"all": true, "page": ( AssetTypes.pagination.page + 1 )});
            newassets.assets = AssetTypes.assets.concat(newassets.assets);
            setAssetTypes(newassets);
        }
    }

    return (
        <AssetTypeContext.Provider value={{ AssetTypes, refreshAssetTypes, getMoreAssets }}>
            {children}
        </AssetTypeContext.Provider>
    );
}

export default AssetTypeProvider;