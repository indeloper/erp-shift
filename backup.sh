#!/bin/bash
# Backup storage directory
backupfolder=/var/backups/erp-sk-gorod-database-backups
backupmountpoint=/mnt/web_backup/
backuperpfilesfolder=/var/www/accounting
logfile=/var/log/erp-sk-gorod-backup.log
# MySQL user
user=root
# MySQL password
password=GVlhalf857bkJKas
# Database name
database_name=sk_gorod_erp
# Number of days to store the backups
keep_day=30
sqlfile=$backupfolder/$database_name-$(date +%d-%m-%Y_%H-%M-%S)'UTC'.sql
zipfile=$backupfolder/$database_name-$(date +%d-%m-%Y_%H-%M-%S)'UTC'.zip
# Create a backup
echo '____________________________________________________________'
echo $(date +%d-%m-%Y_%H-%M-%S)' UTC - Starting backup process'
mysqldump -u $user -p$password $database_name > $sqlfile
if [ $? == 0 ]; then
  echo 'Sql dump created'
else
  echo 'ERROR: mysqldump return non-zero code'
 exit
fi
# Compress backup
zip $zipfile $sqlfile
if [ $? == 0 ]; then
  echo 'The backup was successfully compressed'
else
  echo 'ERROR: cannot compress backup'
  exit
fi
rm $sqlfile
echo $zipfile ' - backup was successfully created'
# Delete old backups
find $backupfolder -type f -mtime +$keep_day -delete
rsync -rtv --delete $backupfolder $backupmountpoint
rsync -rtv $backuperpfilesfolder $backupmountpoint
echo $(date +%d-%m-%Y_%H-%M-%S)' UTC - Finished backup process'
