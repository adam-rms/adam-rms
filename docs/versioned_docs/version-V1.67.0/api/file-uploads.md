---
id: file-uploads
title: File Uploads
---

# File Uploads 

:::caution This documentation is a work in progress.

This file was written by volunteers and then programmatically organized into this site, so there may be errors and typos. Hit "Edit this Page" at the bottom to correct them.

:::

## delete

Delete file from s3 bucket
```
file/delete.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
s3files_id
```

## index

Display file
```
file/index.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
f (file)
s (filesize)
d (force download)
r (return as redirect or text)
e (link expires when?)
key (?)
```

## removeShare

Stop external share of file
```
file/removeShare.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
s3files_id
```

## rename

Rename file
```
file/rename.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
s3files_id
s3files_name
```

## share

Externally share file
```
file/share.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
s3files_id
```

## appUploader.php

Upload file to s3 bucket
```
s3files/appUploader.php.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
filename
typename
typeid
subtype
public
```

## generateSignatureUppy.php

Format uppy file uploads for bucket
```
s3files/generateSignatureUppy.php.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
filename
contentType
```

## projectInvoice.php

Generate an invoice and upload
```
s3files/projectInvoice.php.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
[GET] id
[GET] generate
possibly others that are used by a template
```

## uploadSuccess.php

Confirm file is uploaded and add to database
```
s3files/uploadSuccess.php.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
name
size
typeid
subtype
originalName
public
```

