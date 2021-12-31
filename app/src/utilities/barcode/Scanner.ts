import { BarcodeScanner } from "@capacitor-community/barcode-scanner";
import { Dialog } from "@capacitor/dialog";
import { isPlatform } from "@ionic/core";

/*
 * This file handles the actual collection of barcode data,
 * and should be used in other files to handle the result
 */
//Temporary constant for barcode type, waiting for library to update
const barcodeType = "CODE_128";

/**
 * Verifies whether BarcodeScanner plugin is allowed to scan barcodes
 * ie. has the camera permission been granted?
 * @returns {Promise<boolean>} boolean permission
 */
const didUserGrantPermission = async () => {
  // check if user already granted permission
  const status = await BarcodeScanner.checkPermission({ force: false });
  if (status.granted) {
    // user granted permission
    return true;
  }
  if (status.denied) {
    // the user denied permission for good
    // redirect user to app settings if they want to grant it anyway
    const c = confirm(
      "If you want to scan barcodes, you'll need to grant permission for using your camera.",
    );
    if (c) {
      BarcodeScanner.openAppSettings();
    }
  }
  if (status.neverAsked) {
    // user has not been requested this permission before
    // it is advised to show the user some sort of prompt
    // this way you will not waste your only chance to ask for the permission
    const c = confirm(
      "We need your permission to use your camera to be able to scan barcodes",
    );
    if (!c) {
      return false;
    }
  }
  if (status.restricted || status.unknown) {
    // ios only
    // probably means the permission has been denied
    return false;
  }

  // user has not denied permission
  // but the user also has not yet granted the permission
  // so request it
  const statusRequest = await BarcodeScanner.checkPermission({ force: true });
  if (statusRequest.granted) {
    // the user did grant the permission now
    return true;
  }
  // user did not grant the permission, so he must have declined the request
  return false;
};

/**
 * Use BarcodeScanner to fetch content
 * @returns {string | boolean} Barcode content or false
 */
const OpenScanner = async () => {
  BarcodeScanner.hideBackground(); // make background of WebView transparent

  const result = await BarcodeScanner.startScan(); // start scanning and wait for a result

  // if the result has content
  if (result.hasContent) {
    return result.content; // log the raw scanned content
  } else {
    return false;
  }
};

/**
 * Provide a popup prompt if barcode libray can't be used.
 * @returns {string | boolean} Barcode content or false
 */
const WebPrompt = async () => {
  const { value, cancelled } = await Dialog.prompt({
    title: "Asset Tag",
    message: "What is the Asset Tag?",
  });

  if (cancelled) {
    //there's no value
    return false;
  } else {
    return value;
  }
};

/**
 * Handles relevant scanning depending on platform
 * Currently, barcodeType is always CODE_128
 * @returns {[string, string]} [Scanned/Input barcode, barcodeType]
 * @returns {[false, null]} if no vaild barcode given: [false, null]
 */
const DoScan = async () => {
  let content: string | boolean | undefined;
  if (isPlatform("ios") || isPlatform("android")) {
    //we can use the BarcodeScanner Plugin with these platforms
    if (await didUserGrantPermission()) {
      content = await OpenScanner(); //return these values
    }
  } else {
    //Plugin doesn't work so just prompt for input
    content = await WebPrompt();
  }

  if (content) {
    return [content, barcodeType];
  } else {
    return [false, null];
  }
};

export default DoScan;
