[![version](https://img.shields.io/github/package-json/v/JoranOut/wp-soli-mededelingen-plugin?label=version&color=3858e9)](https://github.com/JoranOut/wp-soli-mededelingen-plugin/releases)
[![nightly](https://img.shields.io/github/v/release/JoranOut/wp-soli-mededelingen-plugin?include_prereleases&label=nightly&color=fb8817)](https://github.com/JoranOut/wp-soli-mededelingen-plugin/releases)
[![tested up to](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fapi.wordpress.org%2Fcore%2Fversion-check%2F1.7%2F&query=%24.offers%5B0%5D.current&label=tested%20up%20to&prefix=WP%20&color=40a8af)](https://wordpress.org/download/releases/)
[![requires](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2FJoranOut%2Fwp-soli-mededelingen-plugin%2Fmain%2Fpackage.json&query=%24.wordpress.requiresAtLeast&label=requires&prefix=WP%20&color=40a8af)](https://wordpress.org/download/releases/)
[![wp-env](https://img.shields.io/github/package-json/dependency-version/JoranOut/wp-soli-mededelingen-plugin/dev/@wordpress/env?label=wp-env&color=40a8af)](https://www.npmjs.com/package/@wordpress/env)
[![node](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2FJoranOut%2Fwp-soli-mededelingen-plugin%2Fmain%2Fpackage.json&query=%24.engines.node&label=node&color=43853d)](https://nodejs.org)
[![license](https://img.shields.io/github/license/JoranOut/wp-soli-mededelingen-plugin?color=blue)](LICENSE)

# Soli Mededelingen Plugin

Members-only announcements (mededelingen) post type for Soli sites.

<!-- Machine-readable markers. publish.js reads the plugin name to name the zip,
     and the nightly workflow rewrites the version here when packaging a build.
     Kept in a comment because a single tilde renders as strikethrough on GitHub;
     the badges above are the human-readable version. Do not reformat.
~Current Version:0.1.0~
~Plugin Name: wp-soli-mededelingen-plugin~
-->

## Description

Registers the `soli_mededeling` custom post type for announcements that are
only visible to logged-in members:

- Query Loop blocks and other front-end queries show nothing to logged-out visitors
- Direct visits to a single mededeling or the archive respond with HTTP 403
- The REST endpoints require a logged-in user

The plugin adds a preconfigured **Mededelingen** variation of the core Query
Loop block, so editors can drop the latest announcements on any (members) page.

## Features

- `soli_mededeling` custom post type (title, editor, excerpt, featured image, revisions)
- Members-only visibility on every surface: queries, single page, archive, search, REST
- "Mededelingen" Query Loop block variation
- Translations for `nl_NL` and `en_US`
- Automatic updates via GitHub releases

## Requirements

- WordPress 6.9+ (the e2e suite runs against 6.9 and the newest release)
- PHP 8.2+
