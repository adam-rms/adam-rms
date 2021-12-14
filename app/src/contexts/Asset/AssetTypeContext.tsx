import axios from "axios";
import { createContext, useContext, useEffect, useReducer, useState } from "react";
import Api from "../../controllers/Api";

export const AssetTypeContext = createContext<any>(null);

const AssetTypeProvider: React.FC<React.ReactNode> = ({children}) => {
    const [AssetTypes, setAssetTypes] = useState<IAssetType[]>([{
        assetTypes_id: 0,
        assetTypes_name: "",
        assetCategories_id: 0,
        manufacturers_id: 0,
        instances_id: 0,
        assetTypes_description: "",
        assetTypes_productLink: "",
        assetTypes_definableFields: ",,,,,,,,,",
        assetTypes_mass: 0,
        assetTypes_inserted: "",
        assetTypes_dayRate: 0,
        assetTypes_weekRate: 0,
        assetTypes_value: 0,
        manufacturers_name: "",
        manufacturers_internalAdamRMSNote: "",
        manufacturers_website: "",
        manufacturers_notes: "",
        assetCategories_name: "",
        assetCategories_fontAwesome: "",
        assetCategories_rank: 12,
        assetCategoriesGroups_id: 1,
        assetCategories_deleted: 0,
        assetCategoriesGroups_name: "",
        thumbnails: [],
        assetTypes_mass_format: "",
        assetTypes_value_format: "",
        assetTypes_dayRate_format: "",
        assetTypes_weekRate_format: "",
        count: 2,
        fields: ["","","","","","","","","",""],
        tags: [],
        files: []
    }]);

    async function getAssetTypes() {
        const fetchedAssets = await Api("assets/list.php", {"all": true});
        return fetchedAssets['assets'];
    }
    async function refreshAssetTypes() {
        setAssetTypes(await getAssetTypes());
    }

    return (
        <AssetTypeContext.Provider value={{ AssetTypes, refreshAssetTypes }}>
            {children}
        </AssetTypeContext.Provider>
    );
}

export default AssetTypeProvider;