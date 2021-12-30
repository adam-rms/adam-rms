import Api from "../Api";
import DoScan from "./Scanner";

/**
 * Open a scanning prompt and get associated asset
 * @returns {IAsset | false} an Asset or false if asset can't be found
 */
const GetAssetFromBarcode = async () => {
  const scanResult = await DoScan();

  //This is a temporary replacement whilst the scanning library is updated
  const barcodeType = "CODE_128";

  if (scanResult) {
    const asset = await Api("assets/barcodes/search.php", {
      text: scanResult,
      type: barcodeType,
    });

    if (asset.asset) {
      //this is the asset you are looking for
      return asset.asset;
    } else if (asset.assetSuggest) {
      //we've not found the exact asset so return the closest suggestion
      return asset.assetSuggest;
    }
  }

  //no asset found so can't return asset
  return false;
};

export default GetAssetFromBarcode;
