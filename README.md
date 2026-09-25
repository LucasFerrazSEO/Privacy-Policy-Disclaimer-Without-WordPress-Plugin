**English** · [Português (Brasil)](README.pt-BR.md)

# Privacy-Policy-Disclaimer-Without-WordPress-Plugin

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

A cookie notice banner ("by using this site, you accept...") in plain
HTML and JavaScript, to paste into your theme footer instead of
installing a cookie consent plugin. It has no dependencies. The banner
text and the default privacy policy path are in Brazilian Portuguese.

## Contents

- [Features](#features)
- [Usage](#usage)
- [FAQ](#faq)
- [Limitations](#limitations)
- [Contributing](#contributing)
- [Author](#author)
- [License](#license)

## Features

A fixed banner shows up on the first visit, with a link to the privacy
policy page and an "OK" button. Clicking OK hides the banner (without
reloading the page) and sets a cookie for 30 years (10950 days), so it
does not show up again in the same browser.

The code was revised in this version. The "OK" button is now a real
`<button>` (it used to be a `<div>` with `onclick`, which cannot get
keyboard focus and is not announced correctly by screen readers). Two
dead functions that were never called (one re-showed the banner for no
reason, the other updated a clock that does not exist in this snippet)
were removed.

## Usage

1. Copy the contents of `Code.txt`.
2. Paste it into your theme's `footer.php` (or into a custom HTML
   block, if your theme or site builder allows it).
3. Check the privacy policy URL. The code uses `bloginfo('url')` plus
   `/politica-de-privacidade/`. If your policy page has a different
   URL, change the `privacy_policy` variable on line 3 of the file.
4. Publish and open the site in a private window to check that the
   banner shows up and goes away when you click OK.

## FAQ

**Does this make the site LGPD compliant?**
Not on its own. This is a simple cookie notice, not a full consent
management tool (that would require, for example, blocking third-party
scripts until the user accepts, per-category consent and a record of
the consent). For full LGPD compliance, look at a dedicated consent
tool or get specific legal advice.

**Does it work outside WordPress?**
The only WordPress-specific part is `<?php bloginfo('url'); ?>` on
line 3, which outputs the site URL. Outside WordPress, replace it with
your site's fixed URL.

**Does it need a plugin or an external library?**
No. It is plain HTML and JavaScript, with no dependencies.

**Does the banner reload the page when accepted?**
No. Clicking OK only sets the cookie and hides the banner. The page
stays exactly as it was.

## Limitations

A simple cookie notice banner, not a granular consent tool. It does not
block third-party scripts before acceptance, does not record the date
and time of consent and does not tell cookie categories apart
(necessary, analytics, marketing).

## Contributing

Bug reports and suggestions are welcome through [GitHub Issues](https://github.com/LucasFerrazSEO/Privacy-Policy-Disclaimer-Without-WordPress-Plugin/issues).

## Author

[Lucas Ferraz](https://lucasferraz.com) is an SEO, website development and Generative Engine Optimization specialist and the founder of [Lucas Ferraz SEO](https://lucasferrazseo.com).

## License

MIT. See [LICENSE](LICENSE).
