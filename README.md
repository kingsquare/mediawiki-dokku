[![Docker Pulls](https://img.shields.io/docker/pulls/kingsquare/mediawiki-dokku.svg)](https://hub.docker.com/r/kingsquare/mediawiki-dokku/)

# MediaWiki docker image for dokku

## TODO

 - [x] supports linked database (via DATABASE_URL)
 - [x] supports linked redis (via REDIS_URL)
 - [x] supports linked smtp (via SMTP_URL)
 - [ ] supports linked memcached

## Usage

Currently this can only be used via a `Dockerfile` dokku deployment

## Deployment

    # create an app
    dokku apps:create mediawiki

    # set the environment (only required for non-dev envs)
    dokku config:set mediawiki environment=production

    ## Add Persistent storage (for uploads)

    # create storage for the app
    sudo mkdir -p  /host/location/mediawiki

    # ensure the dokku user has access to this directory
    sudo chown -R dokku:dokku /host/location/mediawiki

    # mount the directory into your app
    dokku storage:mount mediawiki /host/location/mediawiki:/data

    ## Add database
    # Create a mariadb service with environment variables
    dokku mariadb:create mediawiki

    # Link the mariadb service to the app
    dokku mariadb:link mediawiki mediawiki

    # Start a previously stopped mariadb service
    dokku mariadb:start mediawiki

# Env variables

When starting the following vars are required:

    MEDIAWIKI_ADMIN_USER
    MEDIAWIKI_ADMIN_PASS

This creates an admin user and also uses this user to perform database maintenance on restart.

The following vars can be used to change some behaviour:

    MEDIAWIKI_ENABLE_REDIS_JOBQUEUE
