---
id: asset-maintenance
title: Asset Maintenance
sidebar_label: Maintenance
---

# Asset Maintenance 

:::caution This documentation is a work in progress.

This file was written by volunteers and then programmatically organized into this site, so there may be errors and typos. Hit "Edit this Page" at the bottom to correct them.

:::

## newJob

Create maintenace job
```
maintenance/newJob.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
formData
```

## searchAsset

Find assets for job
```
maintenance/searchAsset.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
maintenanceJobs_id
term
```

## serachUser

Find users for job
```
maintenance/serachUser.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
term
```

## addAsset

Add asset to job
```
maintenance/job/addAsset.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
maintenanceJobs_id
maintenanceJobs_assets
```

## changeBlock

Add/remove block for job
```
maintenance/job/changeBlock.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
maintenanceJobs_id
maintenanceJobs_blockAssets
```

## changeDueDate

Update job due date
```
maintenance/job/changeDueDate.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
maintenanceJobs_id
maintenanceJobs_timestamp_due
```

## changeFlag

Add/remove flag for job
```
maintenance/job/changeFlag.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
maintenanceJobs_id
maintenanceJobs_flagAssets
```

## changeJobAssigned

Add/remove user job assignment
```
maintenance/job/changeJobAssigned.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
maintenanceJobs_id
users_userid
```

## changeJobStatus

Update job status
```
maintenance/job/changeJobStatus.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
maintenanceJobs_id
maintenanceJobsStatuses_id
```

## changeName

Update job name
```
maintenance/job/changeName.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
maintenanceJobs_id
maintenanceJobs_title
```

## changePriority

Update job priority
```
maintenance/job/changePriority.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
maintenanceJobs_id
maintenanceJobs_priority
```

## deleteJob

Remove job
```
maintenance/job/deleteJob.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
maintenanceJobs_id
```

## removeAsset

Remove asset from job
```
maintenance/job/removeAsset.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
maintenanceJobs_id
assets_id
```

## sendMessage

Send notification to users associated with job
```
maintenance/job/sendMessage.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
maintenanceJobs_id
maintenanceJobsMessages_text
maintenanceJobsMessages_file
```

## tagUser

Add use to job
```
maintenance/job/tagUser.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
maintenanceJobs_id
users_userid
```

## unTagUser

Remove user from job
```
maintenance/job/unTagUser.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
maintenanceJobs_id
users_userid
```