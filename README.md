# Secmon Test Execution Module

## Startup
Initially, might need to be run twice because of service interdependencies. 
```
docker compose up
```

## Development
To rebuild and restart a single image, or example `frontend`:
```
docker-compose up -d --build --no-deps frontend
```

## Prod build
```
# Builds
docker build -t populate_db_prod --target prod populate-db
docker build -t web_prod .
docker build -t frontend_prod --target prod --file client/Dockerfile .
docker build -t debian_target --file test-target/Dockerfile_debian test-target/
docker build -t centos_target --file test-target/Dockerfile_centos test-target/


# Rename
docker tag frontend_prod uglyz4rd/dp_frontend:1.0.0
docker tag web_prod uglyz4rd/dp_backend:1.0.0
docker tag debian_target uglyz4rd/dp_debian_target:1.0.0
docker tag centos_target uglyz4rd/dp_centos_target_:1.0.0
docker tag populate_db_prod uglyz4rd/dp_populate_db_prod:1.0.0

# Push
docker push uglyz4rd/<image_name>:1.0.0
```
