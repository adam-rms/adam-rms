import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import {
  useIonRouter,
  useIonToast,
  IonFab,
  IonFabButton,
  IonFabList,
} from "@ionic/react";
import GetAssetFromBarcode from "../../utilities/barcode/GetAssetFromBarcode";

const AssetsFab = () => {
  const router = useIonRouter();
  const [present] = useIonToast();

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

  return (
    <IonFab vertical="bottom" horizontal="end" slot="fixed">
      <IonFabButton color="light">
        <FontAwesomeIcon icon="info" />
      </IonFabButton>
      <IonFabList side="top">
        <IonFabButton onClick={redirectToAsset}>
          <FontAwesomeIcon icon="search" />
        </IonFabButton>
      </IonFabList>
    </IonFab>
  );
};

export default AssetsFab;
