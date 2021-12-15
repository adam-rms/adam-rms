import { createContext, useState } from "react";
import Api from "../../controllers/Api";

// The actual context
export const AssetTypeContext = createContext<any>(null);

//Create a provider wrapper to make the interaction with the context easier
const AssetTypeProvider: React.FC<React.ReactNode> = ({children}) => {
    
    //Create default state
    const [AssetTypes, setAssetTypes] = useState<IAssetType>({
        assets: [],
        pagination: {
            page: 0,
            total: 0
        }
    });

    /**
     * Refresh Context
     * Replace all assets in context
     */
    async function refreshAssetTypes() {
        //
        setAssetTypes(await Api("assets/list.php", {"all": true}));
    }

    /**
     * Extend Context
     * Add more assets to the list if available
     */
    async function getMoreAssets() {
        //check if there are more pages to get
        if (AssetTypes.pagination.page < AssetTypes.pagination.total){ 
            //get assets
            let newassets:IAssetType =  await Api("assets/list.php", {"all": true, "page": ( AssetTypes.pagination.page + 1 )});
            newassets.assets = AssetTypes.assets.concat(newassets.assets);
            setAssetTypes(newassets);
        }
    }

    // Don't forget to add new functions to the value of the provider!
    return (
        <AssetTypeContext.Provider value={{ AssetTypes, refreshAssetTypes, getMoreAssets }}>
            {children}
        </AssetTypeContext.Provider>
    );
}

export default AssetTypeProvider;