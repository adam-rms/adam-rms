---
sidebar_position: 40
title: Files
---

AdamRMS uses S3-compatible for file storage, using the s3files table in the database to keep track of them. Most of the columns are fields related to the S3 specification for retrieving files, but some are added to make it easier to find files across the application.

| Column Name | Purpose |
|-------------|---------|
| s3files_id | AdamRMS id for the file |
| instances_id | Associated instance for the file - all files must belong to exactly one instance |
| s3files_path | The path to the file in the S3-compatible storage, set by file handlers in bCMS. Should not inlcude a leading `/` |
| s3files_name | The user-facing name for the file |
| s3files_filename | The name of the file as it is stored in the S3-compatible storage (not including the path and extension) |
| s3files_extension | The file extension (no .) |
| s3files_original_name | The filename when originally uploaded, should be immutable (includes extension) |
| s3files_meta_size | Size of the file in bytes |
| s3files_meta_public | Whether the file is publically accessible or only available to those in the associated `instances_id` - not actually used anywhere |
| s3files_shareKey | Key used for sharing the file with public, for "anyone with the link" style sharing |
| s3files_meta_type | The type of the file - see below |
| s3files_meta_subType | Depends what it is - each module that uses the file handler will be setting this for themselves. Usually it's the primary key of the row to which that file relates (like the asset type id of an asset type thumbnail) | 
| s3files_meta_uploaded | Date of when file was uploaded - set by the database |
| users_userid | Who uploaded the file |
| s3files_meta_deleteOn | Marker to delete this file on this set date, used to soft-delete files for 30 days before deletion |
| s3files_meta_physicallyStored | If we have the file it's 1 - if we deleted it it's 0 but the "deleteOn" is set. If we lost it, it's 0 with a null "delete on" |

## File Types 

Each file has an associated file type, which is used to filter files and decide various properties:

- `ignore instance` - File is visible to all **authenticated** users
- `public` - File is visible to **un-authenticated** users

Files default to private and instance aware, and only specific types vary.

| ID | File Type | Restrictions |
|----|-----------|--------------|
| 0 | Unknown | |
| 1 | _unused_ | _n/a_ |
| 2 | Asset type thumbnail | `ignore instance`, `public` |
| 3 | Asset type file attachment | |
| 4 | Asset file attachment | |
| 5 | Instance thumbnail | `ignore instance`, `public` |
| 6 | _unused_ | _n/a_ |
| 7 | Project file | |
| 8 | Maintenance job file | |
| 9 | User thumbnail | `ignore instance` |
| 10 | Instance email header image | `ignore instance`, `public` |
| 11 | Location file attachment | |
| 12 | Training Module thumbnail | |
| 13 | Training Module image upload for an individual step of a module | |
| 14 | Project payment file attachment | |
| 15 | Public site file attachement | `ignore instance`, `public` | 
| 16 | Public site homepage content image | `ignore instance`, `public` | 
| 17 | Public site homepage header image | `ignore instance`, `public` | 
| 18 | Project vacant role - application attachment | |
| 19 | CMS page image upload | |
| 20 | Project Invoice | | 
| 21 | Project Quote | |
| 22 | Project Delivery Note | |