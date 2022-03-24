# Geo CMB2

Example WordPress plugin which uses [CMB2](https://github.com/CMB2) to manage and render location-based content to visitors on a site using Pantheon Edge Integrations personalization features.

## Architecture

The diagram below illustrates the how this plugin fits in with Pantheon's Edge Integrations WordPress solution.

```mermaid
flowchart TB
agcdn[/Pantheon Advanced Global CDN\]-->ei[Pantheon Edge Integrations global library]
ei-->eiplugin(Pantheon WordPress Edge Integrations plugin)
eiplugin-->(Geo CMB2)
```

## Description

This plugin registers a field group named `CMB2 Geo` on Pages. The field group contains:

- Default content field: content that is rendered on the frontend if no location is set.
- Repeater field: contains a country select option (`US` `CA` `FR`) and an associated textarea.

The `render_the_geo_content` function hooks into `the_content` filter and appends any location-specific content. The visitor's location is determined using the [`get_geo` function](https://github.com/pantheon-systems/pantheon-wordpress-edge-integrations/blob/main/inc/geo.php#L25).