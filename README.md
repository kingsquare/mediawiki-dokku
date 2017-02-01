# mediawiki-dokku

MediaWiki docker image for dokku

 - [*] supports linked database
 - [ ] supports linked redis
 - [ ] supports linked memcached

## Usage

Currently this can only be used via a `Dockerfile` deployment

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
