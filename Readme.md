# AdamRMS


## Docker Setup

1. Install `docker.io`
1. `systemctl enable docker` to ensure docker boots on startup
1. Clone git
1. `cd` into it
1. Create `adamrmsprod.env` 
1. Run `docker build -t adamrms .`

### Docker Update

```
cd adam-rms
bash updater.sh
```

## AWS 

`serverless deploy --aws-profile aws`