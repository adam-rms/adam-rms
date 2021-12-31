import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import {
  IonFab,
  IonFabButton,
  IonFabList,
  useIonRouter,
  useIonToast,
} from "@ionic/react";
import { useContext } from "react";
import { ProjectDataContext } from "../../contexts/project/ProjectDataContext";
import AddAssetToProject from "../../utilities/barcode/AddAssetToProject";
import GetAssetFromBarcode from "../../utilities/barcode/GetAssetFromBarcode";

/**
 * Floating action buttons for Projects
 */
const ProjectFab = () => {
  const router = useIonRouter();
  const [present] = useIonToast();
  const { projectData } = useContext(ProjectDataContext);

  //Define actual button functions here, that then call utility functions
  /**
   * Redirect to Asset
   * Scans a barcode and then goes to that asset
   */
  const redirectToAsset = async () => {
    const asset = await GetAssetFromBarcode();
    if (asset) {
      router.push("/assets/" + asset.assetTypes_id + "/" + asset.assets_id);
    } else {
      present("Asset Not found", 2000);
    }
  };

  /**
   * "Supermarket Sweep"
   * Adds Asset to current project
   * Scan asset and it's added to project
   */
  const addAsset = async () => {
    if (projectData) {
      const result = await AddAssetToProject(projectData.project.projects_id);

      if (result) {
        if (typeof result === "string") {
          //we've got an error message
          present(result, 2500);
        } else {
          //successfully added
          present("Added to " + projectData.project.projects_name, 2500);
        }
      } else {
        //asset not found
        present("There was an error adding this asset", 2500);
      }
    } else {
      //we don't have a project so something has gone very wrong!
      throw new Error("addAssett() can only be called within a project");
    }
  };

  return (
    <IonFab vertical="bottom" horizontal="end" slot="fixed">
      <IonFabButton color="light">
        <FontAwesomeIcon icon="shopping-basket" />
      </IonFabButton>
      <IonFabList side="top">
        <IonFabButton onClick={redirectToAsset}>
          <FontAwesomeIcon icon="search" />
        </IonFabButton>
        {projectData && (
          <IonFabButton onClick={addAsset}>
            <FontAwesomeIcon icon="shopping-cart" />
          </IonFabButton>
        )}
      </IonFabList>
    </IonFab>
  );
};

export default ProjectFab;
