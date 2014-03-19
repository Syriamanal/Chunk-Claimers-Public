@echo off
echo Running automatic full upload...
echo Adding files to stage...
echo -----
git add -A
echo -----
echo All files staged
echo #####
echo Committing files
echo -----
git commit -m "Untitled commit"
echo -----
echo All files committed
echo #####
echo Checking if remote contains unpulled commits
echo -----
git pull origin master
echo -----
echo Done.
echo #####
echo Pushing to remote
echo Please enter username (PEMapModder) and password
echo -----
git push -u origin master
echo Pushed to remote
echo Automatic full upload completed
echo #####
echo Press any key to close.
pause
