# Build stage
FROM node:16-alpine AS build

WORKDIR /app

# Build arguments for environment variables
ARG VITE_CATALOG_SERVICE_URL=http://localhost:8001
ARG VITE_CHECKOUT_SERVICE_URL=http://localhost:8002

# Set environment variables for build
ENV VITE_CATALOG_SERVICE_URL=$VITE_CATALOG_SERVICE_URL
ENV VITE_CHECKOUT_SERVICE_URL=$VITE_CHECKOUT_SERVICE_URL

# Copy package files
COPY package*.json ./

# Install dependencies
RUN npm ci

# Copy source files
COPY . .

# Build the application
RUN npm run build

# Production stage
FROM nginx:alpine

# Copy built files from build stage
COPY --from=build /app/dist /usr/share/nginx/html

# Copy nginx configuration
RUN echo 'server {\n\
    listen 80;\n\
    server_name localhost;\n\
    root /usr/share/nginx/html;\n\
    index index.html;\n\
    \n\
    location / {\n\
        try_files $uri $uri/ /index.html;\n\
    }\n\
    \n\
    location /api {\n\
        return 404;\n\
    }\n\
}' > /etc/nginx/conf.d/default.conf

# Expose port
EXPOSE 80

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=10s --retries=3 \
    CMD wget --quiet --tries=1 --spider http://localhost || exit 1

# Start nginx
CMD ["nginx", "-g", "daemon off;"]

