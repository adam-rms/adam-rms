# AdamRMS

## Debugging Website

1. `jekyll serve`

## Docker Setup

1. Install `docker.io`
1. `systemctl enable docker` to ensure docker boots on startup
1. Clone git
1. `cd` into it
1. Create `adamrmsprod.env` 
1. Run `docker build -t adamrms .`

### Docker Update

```
docker build -t adamrms .
docker stop adamrms-container && docker rm adamrms-container
docker run -d -p 80:80 --restart=always --memory=800m --name adamrms-container adamrms
```