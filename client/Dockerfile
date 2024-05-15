# Use the official Node.js image as the base image
FROM node:14-alpine as dev

# Set the working directory to /app
WORKDIR /app

# Copy the package.json and package-lock.json files to the container
COPY ./client/package*.json ./

# Install the dependencies
RUN npm install

# Copy the rest of the application code to the container
COPY ./client/ .

# Set the command to start the application
CMD [ "npm", "run", "serve" ]

FROM dev as prod

# To make build not hang because of hot-reloading, see: https://stackoverflow.com/a/77064134/4500196
RUN rm vue.config.js
RUN npm run build
RUN npm install serve

# -s fixes routing: https://cli.vuejs.org/guide/deployment.html#previewing-locally
CMD [ "npx", "serve", "-s", "-l", "8080", "dist" ]
