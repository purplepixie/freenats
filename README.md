# FreeNATS
FreeNATS Network Monitor - http://www.purplepixie.org/freenats/

Licenced under the GNU General Purpose Licence (GPL) v3 (or later).

# Documentation and Support
See the main website - http://www.purplepixie.org/freenats/

# Source Repository
This repository contains the active development code for FreeNATS along with build and release tools, to package and release versions through the main download site.

# File Structure

## /src - Source

Actual source as contained within a release including the upgrade/install scripts with both server (src/server) and nodeside (src/node) versions.

## /doc - Bundled Documentation

Very limited bundled documentation (based on the .txt files; the .html are built dynamically at build time). These are the main README etc which are packaged with the distribution.

## /pub - Publication Files

Files which are overwritten at build time into a release (mainly just the config file which will overwrite whatever the development setup is in /src).

## /test

Random (and outdated) test files of various types used at different times.

## /build-tools

Build scripts used by *build.php* (or can be used manually - view their internal usage notes).

# Development

The easiest option is to just develop within the /src directory as needed. You can expose this as needed, and commit files as needed to a branch etc.

# Building

The build script *build.php* will build a release and optionally archive it and upload it directly to purplepixie.org with the relevant release notes etc.

To build:
>  php build.php

The command line options are as follows:
Usage: php build.php [options]

Options:

```
 Tag    --tag -t X
        Add the tag to the release e.g. freenats-X.YY.ZZ-tag.tar.gz
 Prefix --prefix -p X
        Prefix the release with X e.g. prefix-freenats-X.YY.ZZ.tar.gz
 Zip    --zip -z
        Use Zip rather than TAR+GZIP (if archiving)
 Dummy  --dummy -d
        Dummy - do not compress e.g. creates folder structure in release/ but does not compress
 Upload --upload -u
        Upload to release server (as specified in configuration)
 Dir    --dir
        Directory to put release into (defaults to ./release/)
 Yes    --yes -y
        Say yes to all prompts (dangerous!)
 Clean  --noclean -nc
        No cleaning (don't remove directory after compression)
 Dots  --dotclean -dc
        Run a recursive dot_clean to clean up OSX ._ files from the built directory
```

So for example, let's say the file */src/server/base/freenats.inc.php* contains the version 1.2.3 and release x, the compound version would be 1.2.3x.

A *php build.php* would create a release in the release folder called freenats-1.2.3x containing the full release file structure, before then creating a tarball (.tar.gz) and deleting the folder - resulting in a file /release/freenats-1.2.3x.tar.gz.

Putting the -d (dummy) flag means only the folder is created, it's not compressed.

The -u upload flag will try and upload the release to purplepixie.org and prompt you for credentials to allow this to happen.
