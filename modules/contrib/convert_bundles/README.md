# Convert Bundles

## CONTENTS OF THIS FILE


 * Introduction
 * Requirements
 * Installation
 * Configuration
 * Maintainers

## INTRODUCTION

This module adds drupal actions to convert entities from one bundle to another.

Content can be converted individually from a "Convert Bundle" tab on the node
(or entity), selected nodes from the content administration page
`(/admin/content)`, or all entities of one bundle to another from the convert
bundles config page (/admin/config/content/convert_bundles).

Since the convert bundles creates standard drupal actions for each type of
entity, this module should work with other drupal modules such as rules and vbo.

Please feel free to file any integration or other issues in the issue queue.

## REQUIREMENTS

This module requires no modules outside of Drupal core.

## INSTALLATION

 * Install via /admin/modules (Place module folder in modules dir)
 * ```composer require drupal/convert_bundles``` (using composer)
 * ```drush en convert_bundles -y``` (enable with all dependencies)

## CONFIGURATION

Content can be converted individually from a "Convert Bundle" tab on the node
(or entity), selected nodes from the content administration page
`(/admin/content)`, or all entities of one bundle to another from the convert
bundles config page `(/admin/config/content/convert_bundles)`.

## MAINTAINERS

- Eliot Scott - [el1_1el](https://www.drupal.org/u/el1_1el)
- joseph.olstad -[joseph.olstad](https://www.drupal.org/u/josepholstad)
