# Shared page layout

The home, about, Gen Z, and managers pages use these shared components:

- `header.html`: logo, desktop navigation, mobile menu, and registration link.
- `footer.html`: contact details, social links, and copyright.
- `layout.js`: loads the templates and handles the mobile menu and navbar scroll effect.

Edit the template once to update all four pages. Page-specific content stays in each page.

To reuse the layout on another page, add:

```html
<!-- In the head -->
<script src="templates/layout.js" defer></script>

<!-- Where the navigation and footer belong in the body -->
<div id="site-header"></div>
<!-- Page content -->
<div id="site-footer"></div>
```

The templates use Tailwind utilities, the Montserrat font, and Material Symbols. Use the existing pages' font imports and Tailwind configuration; Gen Z scopes Tailwind to the component containers to coexist with Bootstrap. Give the hero `id="hero"` to enable the transparent-to-navy navbar effect.

Preview through an HTTP server (such as VS Code Live Server or your PHP server). Opening HTML directly with `file://` does not support fetching the templates.
