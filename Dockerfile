FROM aubreyhewes/mediawiki:1.28

# add dokku configuration
COPY conf /conf

# override entrypoint
COPY bin/dokku-entrypoint.sh /dokku-entrypoint.sh
ENTRYPOINT ["/dokku-entrypoint.sh"]
CMD ["apachectl", "-e", "info", "-D", "FOREGROUND"]
