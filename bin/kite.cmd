:: this is script for windows cmd
@echo off
setlocal
set __DBG=
set __ME=%~dp0

: call kite in current dir
php %__ME%/kite %*

