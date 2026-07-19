#?ini charset="iso-8859-1"?
# eZ Publish configuration file.
#
# NOTE: It is not recommended to edit this files directly, instead
#       a file in override should be created for setting the
#       values that is required for your site. Either create
#       a file called settings/override/site.ini.append or
#       settings/override/site.ini.append.php for more security
#       in non-virtualhost modes (the .php file may already be present
#       and can be used for this purpose).


[SyndicationFilters]
FilterArray[]
FilterArray[]=Section
FilterArray[]=Attribute

[Syndication]
CacheDir=syndication
CronUser=14