import Api from "../Api";
import GetAssetFromBarcode from "./GetAssetFromBarcode";

/**
 * Add scanned asset to project
 * @param project_id id of project to add asset to
 * @returns {boolean | string} true if asset assigned
 * @returns {boolean | string} false if asset not found
 * @returns {boolean | string} assignment error message
 */
const AddAssetToProject = async (project_id: string) => {
  const asset = await GetAssetFromBarcode();

  if (asset) {
    const assignment = await Api("projects/assets/assign.php", {
      projects_id: project_id,
      assets_id: asset.assets_id,
    });
    if (assignment.result) {
      return true;
    } else {
      return assignment.error.message;
    }
  }
  return false;
};

export default AddAssetToProject;
