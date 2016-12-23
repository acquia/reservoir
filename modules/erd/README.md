# Drupal 8 Entity Relationship Diagrams

![Screencast Preview](http://i.imgur.com/AzIEyhL.gif)

## Description

This module lets you visualize the Entity structure of your Drupal 8 site using an Entity Relationship Diagram (ERD).

While this isn't meant to be a fully functional tool to build new ERDs, it is meant to be used as a learning tools for developers and site builders.

## Installation

Download and enable the "erd" module, then visit /admin/structure/erd to start playing around.

## Technical details

1. All information seen at /admin/structure/erd is dynamically generated and provided by Drupal.
1. Only Entity References and Image References are supported at this time for dynamically generated links.
1. All Javascript dependencies are taken in via a CDN, which means that this module will not work offline without modification or erd.libraries.yml. This was done for ease of use and installation.
1. The permission to view /admin/structure/erd is "administer site configuration", which may be too permissive for a production site if exposing all your Entity Types/Bundles to an administrator poses a security risk to you.

*Have fun!*
