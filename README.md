# Meridian Injury Law: bilingual WordPress build

A custom WordPress build for a fictional multi-state injury law firm, made as a portfolio project.

**[▶ Open the live demo](https://playground.wordpress.net/?blueprint-url=https://raw.githubusercontent.com/allankoaik/meridian-law-wordpress/main/blueprint.json)**

The demo runs real WordPress in your browser through WordPress Playground. It takes about 20–40 seconds to start, and you're signed in as an admin so you can open the editor and see how the content is structured.

> Meridian Injury Law is not a real firm. All names, numbers and addresses are made up for demonstration.

## What's built

| Requirement | How it's done |
|---|---|
| **Native, reusable templates** | A block theme (`theme/meridian`) with templates for the homepage, pages, practice areas, attorneys, locations, articles, search and 404. Header and footer are shared template parts. |
| **Global styles** | Colours, typography, spacing and button styles live in `theme.json`, so the whole site updates from one place in the Site Editor. |
| **Practice areas, attorneys, state and city pages, articles** | Custom post types in the `meridian-core` plugin. Locations are hierarchical, so a state contains its cities: `/locations/texas/houston/`. |
| **Team can edit without code** | Every page is ordinary block content. New practice areas, attorneys or cities appear automatically in the menus, card grids and contact form. |
| **English and Spanish** | Polylang handles `/es/` URLs, translation links and `hreflang`. Interface text (menu, footer, form) switches language automatically. |
| **Accessible navigation** | Skip link, a labelled `<nav>`, a mobile menu button with `aria-expanded` that closes with Escape, `aria-current` on the active page, and visible focus outlines. |
| **Accessible forms** | Real labels, autocomplete hints, required fields marked in text, server-side validation with an error summary that receives focus, and errors linked to fields with `aria-describedby`. |
| **Responsive** | Fluid type and spacing, CSS grid card layouts and a mobile menu below 1024px. |
| **Optimized images** | Icons and attorney monograms are inline SVG (no image requests). Uploaded photos use WordPress's built-in responsive `srcset` and lazy loading. |
| **SEO basics** | Meta descriptions from excerpts, `LegalService` structured data and `hreflang` alternates. |
| **Lead handling** | Form submissions are saved as private "Inquiries" in wp-admin and emailed to the site admin. Nonce and honeypot spam protection. |

## Project structure

```
theme/meridian/        Block theme: theme.json, templates, parts, style.css, site.js
plugin/meridian-core/  Post types, bilingual helpers, form, SEO, demo content
blueprint.json         WordPress Playground setup for the live demo
dist/                  Installable zips of the theme and plugin
docs/                  Project page (GitHub Pages)
```

The post types live in a plugin rather than the theme, so the content survives a future redesign.

## Install on a real WordPress site

1. Install and activate **Polylang** from the plugin directory.
2. Upload `dist/meridian-core.zip` (Plugins → Add New → Upload) and activate it.
3. Upload `dist/meridian-theme.zip` (Appearance → Themes → Add New → Upload) and activate it.
4. Optional: load the demo content by running `meridian_seed()` once with WP-CLI: `wp eval 'meridian_seed();'`

## Local development

Run the theme and plugin live from this folder with the WordPress Playground CLI (no PHP or database needed):

```
npx @wp-playground/cli@latest server --mount=theme/meridian:/wordpress/wp-content/themes/meridian --mount=plugin/meridian-core:/wordpress/wp-content/plugins/meridian-core --blueprint=blueprint.local.json
```

After changing files, run `build.ps1` to refresh the zips in `dist/`.

## Built with

WordPress 6.x block theme · theme.json v3 · PHP 8 · Polylang · vanilla JavaScript (under 1 KB) · WordPress Playground

Built by Allan Koaik, using AI-assisted development (Claude Code) with every change reviewed and tested by hand.
