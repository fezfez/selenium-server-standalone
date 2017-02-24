@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION

SET PATH=%PATH%;%~dp0
SET BIN_TARGET=%~dp0/selenium-server-standalone.jar

php "%~dp0check-env.php" %*
java -jar "%BIN_TARGET%" %*
