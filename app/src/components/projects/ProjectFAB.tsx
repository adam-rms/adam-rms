import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import {
  IonFab,
  IonFabButton,
  IonFabList,
  useIonRouter,
  useIonToast,
} from "@ionic/react";
import GetAssetFromBarcode from "../../utilities/barcode/GetAssetFromBarcode";

const ProjectFab = () => {
  const router = useIonRouter();
  const [present] = useIonToast();

  //Define actual button functions here, that then call utility functions
  const redirectToAsset = async () => {
    const asset = await GetAssetFromBarcode();
    if (asset) {
      router.push("/assets/" + asset.assetTypes_id + "/" + asset.assets_id);
    } else {
      present("Asset Not found", 2000);
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
      </IonFabList>
    </IonFab>
  );
};

export default ProjectFab;
