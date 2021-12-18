/**
 * Asset Type object
 */
interface IAssetType {
  assets: [...IAssetTypeData];
  pagination: {
    page: number;
    total: number;
  };
}

interface IAssetTypeData {
  assetTypes_id: number;
  assetTypes_name: string;
  assetCategories_id: number;
  manufacturers_id: number;
  instances_id: number;
  assetTypes_description: string;
  assetTypes_productLink: string;
  assetTypes_definableFields: string;
  assetTypes_mass: number;
  assetTypes_inserted: string;
  assetTypes_dayRate: number;
  assetTypes_weekRate: number;
  assetTypes_value: number;
  manufacturers_name: string;
  manufacturers_internalAdamRMSNote: string;
  manufacturers_website: string;
  manufacturers_notes: string;
  assetCategories_name: string;
  assetCategories_fontAwesome: string;
  assetCategories_rank: number;
  assetCategoriesGroups_id: number;
  assetCategories_deleted: number;
  assetCategoriesGroups_name: string;
  thumbnails: [...any];
  assetTypes_mass_format: string;
  assetTypes_value_format: string;
  assetTypes_dayRate_format: string;
  assetTypes_weekRate_format: string;
  count: number;
  fields: [...any];
  tags: [...IAsset];
  files: [...any];
}

type AssetTypeContextType = {
  AssetTypes: IAssetType;
  getMoreAssets: () => void;
  refreshAssetTypes: () => void;
};

/**
 * Asset object
 */
interface IAsset {
  assets_id: number;
  assets_notes: string;
  assets_tag: string;
  asset_definableFields_1: string;
  asset_definableFields_2: string;
  asset_definableFields_3: string;
  asset_definableFields_4: string;
  asset_definableFields_5: string;
  asset_definableFields_6: string;
  asset_definableFields_7: string;
  asset_definableFields_8: string;
  asset_definableFields_9: string;
  asset_definableFields_10: string;
  assets_dayRate: number;
  assets_weekRate: number;
  assets_value: number;
  assets_mass: number;
  assets_tag_format: string;
  assets_mass_format: string;
  assets_value_format: string;
  assets_dayRate_format: string;
  assets_weekRate_format: string;
  flagsblocks: {
    BLOCK: [...any];
    FLAG: [...any];
    COUNT: {
      BLOCK: number;
      FLAG: number;
    };
  };
  files: [...any];
}

/* Project Object */
interface IProject {
  projects_id: number;
  projects_name: string;
  clients_name: string;
  projects_manager: number;
  thisProjectManager: boolean;
}

interface IProjectData {
  project: {};
  files: [...any];
  assetsAssignmentsStatus: [...any];
  FINANCIALS: {};
}
