# Geo ACF

Example WordPress plugin which uses [Advanced Custom Fields](https://wordpress.org/plugins/advanced-custom-fields/) to manage and render location-based content to visitors on a site using Pantheon Edge Integrations personalization features.

## Architecture

The diagram below illustrates the how this plugin fits in with Pantheon's Edge Integrations WordPress solution.

```mermaid
flowchart TB
agcdn[/Pantheon Advanced Global CDN\]-->ei[Pantheon Edge Integrations global library]
ei-->eiwpplugin(Pantheon WordPress Edge Integrations plugin)
eiwpplugin-->(Geo ACF)
```

## Description

This plugin registers a field group named `ACF Geo` on Posts and Pages. The field group contains:

- Default content field: content that is rendered on the frontend if no location is set.
- US content field: content that is rendered on the frontend if a visitor is US-based.
- CA content field: content that is rendered on the frontend if a visitor is CA-based.
- FR content field: content that is rendered on the frontend if a visitor is FR-based.

The `render_the_geo_content` function hooks into `the_content` filter and appends any location-specific content. The visitor's location is determined using the [`get_geo` function](https://github.com/pantheon-systems/pantheon-wordpress-edge-integrations/blob/main/inc/geo.php#L25).