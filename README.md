# Geeklog Hello Plugin

![Version](https://img.shields.io/badge/version-2.2.1-blue.svg)
![Geeklog](https://img.shields.io/badge/Geeklog-2.1.1%20to%202.2.2-green.svg)
![PHP](https://img.shields.io/badge/PHP-5.6%20to%208.x-green.svg)

**Hello 2.2.1** is a newsletter, digest and email campaign plugin for Geeklog CMS. It provides queued bulk delivery, automated article digests, subscriber management, engagement statistics, configurable throttling and standards-based unsubscribe handling.

Version 2.2.1 focuses on deliverability, safer tracking, administrator testing, queue control and security while preserving compatibility with Geeklog 2.1.1 through 2.2.2.

## Key features

- Send HTML email campaigns to Geeklog user groups.
- Build automated digests from recently published stories.
- Personalize messages with user information.
- Queue bulk email instead of sending an entire campaign in one request.
- Process queued recipients by ascending UID so low-UID administrative accounts are reached early.
- Configure the number of messages processed per execution.
- Configure a strict hourly sending limit.
- Pause, resume, stop or manually process queued campaigns.
- Track unique opens with an optional first-party 1×1 pixel.
- Track clicks with optional short first-party redirect tokens.
- Review campaign, subscriber, open and click statistics.
- Search and inspect subscribers from the Hello administration.
- Support browser-confirmed unsubscribe and resubscribe.
- Support RFC 8058 `List-Unsubscribe-Post` one-click unsubscribe.
- Send administrator test messages with a complete simulated unsubscribe/resubscribe workflow.
- English and French language files are included.

## Compatibility

Hello 2.2.1 is designed for:

- Geeklog 2.1.1 through 2.2.2.
- PHP 5.6 through PHP 8.x, subject to the PHP version supported by the selected Geeklog release.
- Geeklog mail backends (`mail`, `sendmail`, SMTP/SMTPS) through the Geeklog mail layer.

The plugin keeps a legacy `COM_mail()` path for older Geeklog versions. When the modern `Geeklog\Mail` class is available, Hello can pass both HTML and plain-text bodies.

## Deliverability improvements in 2.2.1

### Short click-tracking links

Older tracked links embedded a Base64-encoded destination URL. New campaigns use a short opaque token and store the destination server-side in `hello_links`.

This produces cleaner first-party links and prevents newly generated tracked links from acting as arbitrary open redirectors.

Transitional support remains for links already sent by Hello 2.2.0, but the legacy destination is accepted only when it points to the same site.

### HTML and plain-text alternatives

On Geeklog versions exposing the modern mail class, Hello supplies both HTML and plain-text bodies.

### SMTP-safe HTML formatting

Hello inserts transport-friendly line breaks between HTML tags to reduce the risk of extremely long SMTP lines being folded inside URLs or attributes.

### Optional open and click tracking

Administrators can independently enable or disable click tracking and open tracking.

Disabling click tracking keeps direct destination URLs. Disabling open tracking removes the 1×1 tracking pixel.

### Unsubscribe headers

Real campaigns include `List-Unsubscribe` and `List-Unsubscribe-Post: List-Unsubscribe=One-Click`.

RFC 8058 POST requests can unsubscribe directly. A normal browser GET displays a confirmation page first so link scanners do not unsubscribe users merely by following a URL.

Administrative bulk newsletters always include a visible unsubscribe link.

## Administrator test mode

Test messages are sent to the **currently logged-in administrator**, not to a hard-coded UID.

Test subjects are prefixed with `[TEST]`.

The test message includes an active **Unsubscribe (Test)** / **Se désinscrire (Test)** link. The administrator can test the entire flow:

1. open the simulated unsubscribe page;
2. review the warning that the action is a test;
3. confirm the simulated unsubscribe;
4. review the simulated success message;
5. click the resubscribe button;
6. review the simulated resubscribe success message.

Test mode does **not** change `emailfromadmin`, subscriber preferences or unsubscribe statistics.

## Queue and throttling

Hello separates campaign creation from delivery.

Two settings have different purposes:

- **Messages per execution** controls how many queued messages one invocation processes.
- **Strict hourly maximum limit** caps successful sends during an hour.

Queued recipients are selected by ascending UID and then by queue creation time. The main administrator account, commonly UID 2, therefore receives a real campaign near the beginning when that account is an eligible recipient.

Queue-changing administration actions use POST requests protected by Geeklog CSRF tokens.

## Cron usage

`cron.php` is intentionally **command-line only**.

Example:

```bash
php /path/to/geeklog/plugins/hello/cron.php example.com
```

The optional domain argument is useful with Geeklog multisite installations where `HTTP_HOST` selects the site configuration.

Do not expose `cron.php` as a web endpoint.

## Security changes in 2.2.1

- Opaque server-side click destinations for new tracked links.
- Same-site validation for transitional 2.2.0 tracking URLs.
- CSRF protection for manual queue processing, pause, resume, stop and subscriber reset actions.
- `hello.edit` permission enforcement on Hello subscriber administration.
- SQL escaping of subscriber search input.
- HTML escaping of subscriber data displayed by the search interface.
- CLI-only cron execution.
- Development/diagnostic scripts removed from the release archive.
- Administrative bulk newsletters can no longer omit the unsubscribe link.
- No-cache headers on tracking endpoints.

## Installation

1. Back up the Geeklog database and files.
2. Download the Hello release ZIP.
3. Sign in to Geeklog as an administrator.
4. Open the **Plugins** administration page.
5. Upload the archive.
6. Complete the Hello installation.
7. Review the Hello configuration before sending a campaign.

## Upgrade to 2.2.1

1. Back up the site and database.
2. Upload the 2.2.1 archive using Geeklog's plugin upgrade mechanism.
3. Run the plugin upgrade.
4. Confirm that the `hello_links` table exists after the upgrade.
5. Review click/open tracking and throttling settings.
6. Send a test message and test simulated unsubscribe and resubscribe.
7. Send a small real campaign before a large mailing.

## Configuration guidance

### Messages per execution

This is the maximum number of queued messages processed by one run. It is not the hourly quota.

### Strict hourly maximum limit

This is the maximum number of successful messages Hello should send during one hour. Use `0` only when you intentionally want no Hello-level hourly limit and your mail provider permits it.

### Track clicks

When enabled, links use short first-party Hello tokens so clicks can be counted. When disabled, links remain direct.

### Track opens

When enabled, Hello appends a first-party 1×1 image. Open statistics remain approximate because clients may block, proxy or preload images.

## Mail-domain recommendations

Hello cannot replace normal domain and SMTP configuration. For newsletter delivery, configure and monitor:

- SPF;
- DKIM;
- DMARC;
- a stable From domain;
- SMTP/IP reputation;
- reasonable hourly volume;
- valid unsubscribe handling.

## Release hygiene

Development helpers such as `admin/test.php` and `fix_entities.php` are intentionally excluded from the 2.2.1 release archive.

## License and credits

Hello is distributed under the GNU General Public License as stated in the source files. Original author credits are preserved in source headers.

## Repository

https://github.com/hostellerie/hello

Use the repository issue tracker for bugs and compatibility reports. Include the Geeklog version, PHP version, mail backend and relevant error-log excerpt when reporting delivery problems.
