FROM aubreyhewes/mediawiki:1.28

# update upstream composer
RUN composer self-update

# add dokku configuration
COPY conf /conf

# override entrypoint
COPY bin/dokku-entrypoint.sh /dokku-entrypoint.sh
ENTRYPOINT ["/dokku-entrypoint.sh"]
CMD ["apachectl", "-e", "info", "-D", "FOREGROUND"]
