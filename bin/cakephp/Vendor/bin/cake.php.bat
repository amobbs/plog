@echo off
pushd .
cd %~dp0
cd "../pear-pear.cakephp.org/CakePHP/bin"
set BIN_TARGET=%CD%\cake.php
popd
php -q "%BIN_TARGET%" %*
