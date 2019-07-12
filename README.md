# Stockfile

## What's This?

A simple LAMP-stack self-hosted file server for backing up files (chiefly from Apple devices) with an emphasis on being able to tag and annotate media files for controlled sharing of subsets of your personal media with others.

## Motivation

After trying various self-hosted file servers and image galleries (nextcloud, seafile, filerun, piwigo, chevereto, etc.), I realized that no single product had all the features that I wanted and that it would be easier to put together a simple file server of my own from scratch than work around any one of the aforementioned convoluted systems. The desired specs are:

- Be able to upload files from Apple devices using sftp/rsync (with emphasis on photos)
- Be able to inspect all uploaded files through a simple interface that would allow each media file to receive (i) tags, and (ii) captions/comments
- Be able to generate thumbnails for images and videos
- Be able to convert tagged MOV files to mp4
- Extract EXIF metadata and group media files by month
- Work efficiently (viz. using symlinks wherever possible)
- Be able to stream mp4s

## Quick Install

In a remote linux server, clone this repo and symlink it to your apache DocumentRoot so that it's reachable from e.g. `https://yourdomain.com/stockfile`

## Dev Notes

- ...
