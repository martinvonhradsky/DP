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