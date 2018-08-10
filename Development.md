This project uses Composer as a package manager. Reference the README.md for the link to Composer

## Adding a Drupal module
```
composer require drupal/modulename:^1.0
```


## Installing and Updating Drupal module
For a given Drupal module/project use:
```
composer update drupal/modulename --with-dependencies
```
Finally, run any database updates and rebuild the cache:
```
drush updatedb
drush rc
```

For more information, go to [Durpal's documentation for updating module](https://www.drupal.org/docs/8/update/update-modules)

## Updating Core
For updating Drupal's core, visit Drupal's documentation [Update core via Drush](https://www.drupal.org/docs/8/update/update-core-via-drush)