---
sidebar_position: 61
title: AdamRMS App
---

# AdamRMS App

The AdamRMS app is the companion to the dashboard interface, and focuses on asset dispatch through asset barcode scanning. You can also view cms pages and browse all assets.

AdamRMS app is available on the [Apple App Store](https://apps.apple.com/us/app/id1519443182) or the [Google Play Store](https://play.google.com/store/apps/details?id=com.bstudios.adamrms)

:::note App permissions  
85 - Scan Barcodes in the App  
88 - Associate any unassociated barcode with an asset  
:::

## Asset Barcode Scanning
---

Barcode Scanning is the key element of the App, and all scanning processes follow the same route.  
First, you need to set your location using a Location barcode, or a custom location text. You can then scan a barcode and follow any prompts from the App.

If the barcode is already associated with an asset, the app will show you details of that asset.
If it’s a new barcode, the app will ask you the following questions (if you have permission 88):
1. Would you like to associate this barcode with an asset?
2. Enter the current asset Tag.
   - This will usually be an A-XXXX value
3. Would you like to update the asset Tag with this barcode?
   - Generally you’ll want to answer yes to this, unless the barcode is an additional code
