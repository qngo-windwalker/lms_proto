#Deployment Instructions
Follow these instructions for every deployment. The repository does not contain all of Drupal modules and dependencies. However, this project uses Composer which keep track of Drupal modules and dependencies.

##Backup
Before and update or change, always backup the codes and database. Sometime it's necessary to backup user uploaded files. 

You can easily backup the database using Drush command line which will dump the database:
```
drush db-dump nameofyourfile.txt
``` 

Once the codes are deployed, run the follow command to make sure all of required modules and dependencies exists in the project:
```
composer update
``` 

Finally, run any database updates and rebuild the cache:
```
drush updatedb
drush rc
```