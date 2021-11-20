#!/usr/bin/env php
<?php

$path = '/usr/local/lib/node_modules/parcel/node_modules/_svgo@1.2.2@svgo/plugins';

var_dump(
  fnmatch('plugins', 'plugins'),
  fnmatch('*node_modules*', $path),
  fnmatch('*node_modules/*', $path),
  fnmatch('node_modules/*', $path),
  fnmatch('node_modules', $path),
  fnmatch('node_modules/', $path)

);
