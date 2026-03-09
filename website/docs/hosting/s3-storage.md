---
sidebar_position: 10
title: File & Image Storage
---

Images and files are stored using an [AWS S3 Bucket](https://aws.amazon.com/s3/), which can either be from Amazon, or from a service such as [Backblaze](https://www.backblaze.com/), [DigitalOcean Spaces](https://www.digitalocean.com/products/spaces) or [Cloudflare R2](https://developers.cloudflare.com/r2/).

To set up the file storage, you need to set the following settings in the [configuration menu](./configuration-menu.md):

### File Storage

File storage enabled
Whether AWS S3 file storage is enabled or disabled. If disabled, AdamRMS will not allow users to upload files.

### AWS Server Key

The AWS server key.

### AWS Server Secret Key

The AWS server secret key.

### AWS S3 Bucket Name

The AWS S3 bucket name.

### AWS S3 Bucket Browser Endpoint

The AWS S3 bucket endpoint, which must be accessible over the internet for user browsers to upload files

`https://s3.eu-west-1.amazonaws.com` is the default for the eu-west-1 region.

### AWS S3 Bucket Server Endpoint

The AWS S3 bucket endpoint for the server to use to upload files - this is almost certainly the same as the above, except in some very specific circumstances such as running in docker containers.

`https://s3.eu-west-1.amazonaws.com` is the default for the eu-west-1 region.

### Should path-style requests be sent to the upload endpoint?

This should be disabled for almost all providers

### AWS S3 Bucket Region

The AWS S3 bucket region.

e.g. `eu-west-1`

### AWS CloudFront Enabled

Whether AWS CloudFront is enabled.

### AWS CloudFront Private Key

The AWS CloudFront private key.

### AWS CloudFront Key Pair ID

The AWS CloudFront key pair ID.

### AWS S3 CDN Endpoint

The AWS S3 CDN endpoint. This is the URL that users will access the files from: it may be cloudfront, or it may be s3/an alternative.

## Setting up a bucket

### BackBlaze B2

BackBlaze is a cloud storage service which provides S3-compatible object storage.

#### Bucket Info

Set the bucket info to:

```
{"cache-control":"public, max-age=900, s-maxage=3600, stale-while-revalidate=900, stale-if-error=3600"}
```

#### Bucket CORS

You need to setup the CORS on the bucket to allow uploads

1. Download and authenticate the [AWS Cli](https://aws.amazon.com/cli/) (using your Backblaze credentials).
2. Create a file called `cors.json` file, and add the following the information:

```json
{
  "CORSRules": [
    {
      "AllowedHeaders": ["*"],
      "AllowedMethods": ["POST", "PUT", "GET", "HEAD"],
      "AllowedOrigins": ["*"],
      "ExposeHeaders": [
        "etag",
        "x-bz-content-sha1",
        "x-amz-meta-qqfilename",
        "x-amz-meta-size",
        "x-amz-meta-subtype",
        "x-amz-meta-typeid",
        "authorization",
        "content-type",
        "origin",
        "x-amz-acl",
        "x-amz-content-sha256",
        "x-amz-date",
        "range",
        "Access-Control-Allow-Origin"
      ],
      "MaxAgeSeconds": 86400
    }
  ]
}
```

3. Run the following command to apply the policy to your new bucket:

```sh
aws s3api put-bucket-cors --bucket=<BUCKETNAME> --endpoint-url=https://s3.eu-central-003.backblazeb2.com --cors-configuration=file://backblaze-cors.json
```

The region is usually `eu-central-003` for setting in [configuration menu](./configuration-menu.md) of AdamRMS

CDN Url is `https://s3.eu-central-003.backblazeb2.com/<BUCKETNAME>`

And endpoint is `s3.eu-central-003.backblazeb2.com`

````

### AWS S3

To use AWS S3 you need to create an IAM user

#### File S3 Bucket

Create an S3 Bucket, with the follwing Policy

```json
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Sid": "VisualEditor0",
      "Effect": "Allow",
      "Action": ["s3:PutObject", "s3:GetObject"],
      "Resource": "arn:aws:s3:::<BUCKETNAME>/*"
    }
  ]
}
````

- Then set the following environment variables, with information from the S3 console.

#### [Cloudfront](https://aws.amazon.com/cloudfront/)

Using cloudfront is optional, but will improve image and file download times.

Also note that when setting up a policy for the Cloudfront distribution, you must enable:

- Query strings - Include specified query strings response-content-disposition
