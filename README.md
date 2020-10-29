# ArcGIS Geocode Addresses List - Drupal integrations
This repo contains one file and one config schema file which you can add to any
custom module to integrate the
[Drupal Geocoder](https://www.drupal.org/project/geocoder/) module (3.x) with
[ArcGIS Geocode Addresses List](https://github.com/wantell/arcgis-geocode-addresses)

## Usage
* `ArcGISListToken.php` needs to be in this folder path from your module:
`[module]/src/Plugin/Geocoder/Provider/ArcGISListToken.php`
    * Once placed, open this file and replace `your_module` in the namespace
    declaration with the name of your module.
* Add the contents of `schema-snippet.yml` to this file:
`[module]/config/schema/[module].schema.yml`
    * If you do not have this file, rename `schema-snippet.yml`, create the
    folder path indicated, and place it there.

## Note
I will submit these to the Drupal Geocoder module to see if they will
incorporate them.
