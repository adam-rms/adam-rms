git pull
docker build -t adamrms .
docker stop adamrms-container && docker rm adamrms-container
docker run -d -p 80:80 --restart=always --memory=400m --memory-swap="5g" --name adamrms-container adamrms
docker system prune -f