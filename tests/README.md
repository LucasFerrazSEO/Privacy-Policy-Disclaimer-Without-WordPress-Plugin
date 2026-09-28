# Tests

End-to-end tests run against a real WordPress in Chromium, through
[Playwright](https://playwright.dev/). They check the visitor side
(`tests.js`, 59 checks), the admin side (`admin-tests.js`, 24 checks) and
accessibility with [axe-core](https://github.com/dequelabs/axe-core)
(`axe.js`, WCAG 2.1 AA).

## Setup

1. A throwaway WordPress site (do not use a production site: the tests
   change settings, create a user and delete consent records), with
   [WP-CLI](https://wp-cli.org/) available.
2. A child theme whose `functions.php` loads the file and enqueues a test
   script:

   ```php
   require_once get_stylesheet_directory() . '/privacy-policy-disclaimer.php';
   add_action( 'wp_enqueue_scripts', function () {
   	wp_enqueue_script( 'test-marketing', get_stylesheet_directory_uri() . '/test-marketing.js', array(), '1', true );
   } );
   ```

   `test-marketing.js`:

   ```js
   window.ppdTestMarketing = 1; document.cookie = '_fbp=x; path=/';
   ```

3. A published post with a YouTube iframe, a Google Maps iframe and one
   iframe from another host, and these settings:

   ```bash
   wp option update ppd_settings --format=json '{"script_handles":"test-marketing:marketing","scripts_analytics":"<script>window.ppdTestAnalytics=(window.ppdTestAnalytics||0)+1;document.cookie=\"_ga=GA1.1.1; path=/\";</script>","gtm_id":"GTM-TEST123"}'
   ```

4. An administrator `admin` / `teste123` whose email is `a@example.test`.

5. `npm i playwright-core axe-core`, plus a Chromium binary.

## Run

```bash
export PPD_BASE=http://127.0.0.1:8899   # site URL
export PPD_POST_ID=4                    # post with the embeds
export PPD_WP_PATH=/path/to/wordpress
export PPD_WP_CLI=wp                    # or "php /path/to/wp-cli.phar"
export PPD_CHROMIUM=/path/to/chrome     # optional
node tests/tests.js
node tests/admin-tests.js               # reset ppd_settings (step 3) first
node tests/axe.js
```

Each script prints one line per check and exits with a non-zero code when
something fails.
