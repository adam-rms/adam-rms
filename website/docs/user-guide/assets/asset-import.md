---
sidebar_position: 14
title: Asset Import
---

# Asset Import
AdamRMS supports the bulk import of assets from a CSV file. This is an instance-level feature, accessible to instance admins with the `ASSETS:IMPORT` permission.

A template is available with the correct CSV headings, which can be downloaded from within the Import Assets page in AdamRMS.

To import assets, navigate to **Assets → Import Assets** in the sidebar and upload your completed CSV file.

The columns of the CSV file are as follows:

| Column | Description | Required |
| --- | --- | --- |
| assetTypes_name                   | Asset Type Name | Yes |
| assetTypes_description            | Asset Type Description | No |
| assetTypes_productLink            | Link to product page | No |
| assetTypes_mass                   | Asset Type Mass | No |
| assetTypes_dayRate                | Hire cost per day | Yes |
| assetTypes_weekRate               | Hire cost per week | Yes |
| assetTypes_value                  | Asset Type Purchase Value | Yes |
| assetCategories_name              | Asset Category Name (must match an existing category in this instance) | Yes |
| manufacturers_name                | Manufacturer Name | Yes |
| assets_tag                        | The Unique ID/Tag associated with the asset | Yes |
| assets_notes                      |  | No | 
| assets_storageLocation            | The ID of the default location for the asset | No |
| assets_dayRate                    | Override the Asset Type Day Rate for this asset | No |
| assets_WeekRate                   | Override the Asset Type Week Rate for this asset | No |
| assets_value                      | Override the Asset Type Value for this asset | No |
| assets_mass                       | Override the Asset Type Mass for this asset | No |
| assetType_definableFieldsName_1   | Name of the first Asset Type definable field | No |
| assetType_definableFieldsName_2   | As above | No |
| assetType_definableFieldsName_3   | As above | No |
| assetType_definableFieldsName_4   | As above | No |
| assetType_definableFieldsName_5   | As above | No |
| assetType_definableFieldsName_6   | As above | No |
| assetType_definableFieldsName_7   | As above | No |
| assetType_definableFieldsName_8   | As above | No |
| assetType_definableFieldsName_9   | As above | No |
| assetType_definableFieldsName_10  | As above | No |
| asset_definableFields_1           | Value for the first Asset Type definable field | Yes, if field name given |
| asset_definableFields_2           | As above | Yes, if field name given |
| asset_definableFields_3           | As above | Yes, if field name given |
| asset_definableFields_4           | As above | Yes, if field name given |
| asset_definableFields_5           | As above | Yes, if field name given |
| asset_definableFields_6           | As above | Yes, if field name given |
| asset_definableFields_7           | As above | Yes, if field name given |
| asset_definableFields_8           | As above | Yes, if field name given |
| asset_definableFields_9           | As above | Yes, if field name given |
| asset_definableFields_10          | As above | Yes, if field name given |


:::note Instance Permissions Required
ASSETS:IMPORT (requires ASSETS:CREATE, ASSETS:ASSET_TYPES:CREATE, ASSETS:MANUFACTURERS:CREATE)
:::
