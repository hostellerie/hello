# Hello for Geeklog — Roadmap 2.3.0

## Status

**Planning branch:** `hello-2.3.0`

Hello 2.3.0 should prepare the plugin for the broader Geeklog interoperability architecture while preserving its current role: communication with registered Geeklog users through newsletters, digests and email campaigns.

The goal is not to turn Hello into Hub, Marketing, Analytics or an AI plugin.

Hello should become a clean **communication service** that can consume structured Geeklog content and expose safe campaign operations to trusted internal or external consumers.

---

## 1. Current role to preserve

Hello already provides:

- HTML email campaigns to Geeklog user groups;
- story digest generation and preview;
- legacy scheduled digest code that must be changed in 2.3.0 so live digest delivery is never automatic by default;
- personalized messages;
- queued bulk delivery;
- configurable messages per execution;
- strict hourly send limit;
- pause/resume/stop controls;
- administrator test messages;
- click tracking;
- open tracking;
- campaign statistics;
- subscriber statistics;
- browser-confirmed unsubscribe;
- RFC 8058 one-click unsubscribe;
- multisite-aware CLI cron usage;
- Geeklog mail integration.

These capabilities remain the operational foundation of 2.3.0.

---

## 2. Architectural position

Hello should be treated as the **registered-user communication layer** in the Geeklog ecosystem.

```text
Content plugins / Core
        |
        | shared content contract
        v
      Hub
 context / relationships
        |
        v
      Hello
 campaign / digest / queue / delivery
        |
        v
 registered Geeklog users
```

Hello may also be called directly by trusted services:

```text
Connector
   ↓
Hello public service surface
   ↓
campaign draft / test / status / queue action
```

Hello must not require ChatGPT, Hub or a future Marketing plugin in order to function.

---

## 3. Main 2.3.0 objective

Move from a **Stories-specific digest implementation** toward a **generic structured-content digest capability** without breaking current story digests.

Today, the inherited digest logic directly queries Geeklog Stories and can be invoked by Geeklog's scheduled-task path. This inherited automatic-send behavior is **not** the target behavior for 2.3.0.

The target direction is:

```text
Stories
Documents
Maps
Videos
Forum
Store
Tickets
Static Pages
other compatible content plugins
        |
        | plugin_getiteminfo_* collection contract
        v
      Hello
        |
 digest builder
```

The existing Stories path should remain available as a compatibility fallback until the generic path is proven.

---

## 3A. Digest delivery and editorial approval policy

Hello 2.3.0 must treat **digest preparation** and **digest delivery** as separate operations.

The default behavior must be:

```text
new content detected
        ↓
digest candidates prepared
        ↓
administrator reviews / edits
        ↓
administrator explicitly queues or sends
        ↓
delivery
```

A scheduled task may detect new content, refresh candidate lists or prepare a draft, but it must **not queue or send a live digest automatically by default**.

### Required default

```text
Automatic live digest sending = disabled
```

If a future automatic-send option is implemented, it must:

- be an explicit Hello configuration option;
- default to disabled on fresh installations and upgrades;
- clearly state that enabling it can send email without administrator review;
- never be inferred merely from Geeklog's global `emailstories` setting;
- use the same recipient, unsubscribe, rendering, throttling and statistics paths as a manually approved digest;
- provide a visible last-run / next-eligible-run status;
- remain independently disableable without disabling queue processing.

### Digest editorial workflow

The administrator should be able to prepare a digest as a small newsletter with:

- configurable subject;
- editable introduction message;
- candidate content collected since a selected date;
- default start date based on the last **approved/sent digest**, not merely the last scheduled-task execution;
- source selection;
- individual item selection/deselection;
- preview;
- administrator test send;
- explicit final queue/send action.

Suggested workflow:

```text
Last approved digest: 2026-09-17 08:30

Collect content since:
[2026-09-17 08:30]

Subject:
[LE CORDISTE] Les dernières publications

Introduction:
[editable message]

Sources:
[x] Stories
[x] Static Pages
[x] Documents
[x] Maps
[ ] Forum
[ ] Store
[ ] Tickets

Items:
[x] Article A
[x] Article B
[ ] Article C
[x] Document D

[Preview] [Send test] [Queue digest]
```

The existing `lastemailedstories` value may be used as a migration/fallback input, but 2.3.0 should model the digest boundary as operational state owned by Hello rather than as a hidden side effect of a scheduled send.

---

## 4. Shared Geeklog content contract

Hello should consume the common conventions documented in the Memorandum rather than read another plugin's database tables.

Preferred content source interface:

```php
plugin_getiteminfo_PLUGIN('*', $what, $uid, $options)
```

Expected fields where available:

```text
id
type
subtype
title
url
description / excerpt
date-created
date-modified
author
image
category / topic
```

Recommended collection options:

```text
since
limit
order
```

Hello should not require every plugin to expose identical optional fields.

---

## 5. 2.3.0 Phase A — Interoperability audit

Before changing digest behavior:

- inventory current Story-specific SQL in Hello;
- identify all places where Story fields are assumed;
- identify current topic-preference dependencies;
- document current digest rendering inputs;
- identify which logic can become generic;
- identify which logic must remain Story-specific;
- test `PLG_getItemInfo()` behavior on Geeklog 2.1.1 and 2.2.2;
- test compatible content plugins as they become available;
- define fallbacks where collection APIs are missing.

Deliverable:

- documented source adapter boundary;
- no functional regression to current story digests.

---

## 6. Phase B — Generic content-source adapters

Introduce an internal source abstraction that normalizes content before rendering.

Conceptual normalized item:

```php
array(
    'type' => 'documents',
    'id' => '123',
    'title' => 'Example',
    'url' => 'https://example.org/...',
    'excerpt' => '...',
    'image' => '',
    'date' => '...',
    'source_label' => 'Documents'
);
```

Possible internal adapters:

```text
HELLO_getStoryItems()
HELLO_getPluginItems($plugin, $options)
HELLO_normalizeDigestItem()
```

Names are illustrative, not frozen APIs.

Requirements:

- no direct SQL into other plugins;
- current Stories support preserved;
- missing optional fields handled safely;
- permission-aware collection where supported;
- stable canonical URLs supplied by owning plugin;
- duplicate items avoided.

---

## 7. Phase C — Multi-source digest configuration

Allow administrators to choose which compatible content sources may contribute to a digest.

Possible configuration:

```text
Stories        enabled
Documents      enabled
Maps           disabled
Videos         enabled
Forum          disabled
Store          disabled
Tickets        disabled
Static Pages   enabled
```

Do not assume every content plugin belongs in every newsletter.

Possible per-source options later:

```text
maximum items
created vs modified
category/topic filter
include image
source heading
```

The first 2.3.0 editorial controls should remain simple and useful:

- subject;
- introduction;
- content-since date;
- enabled sources;
- individual candidate selection;
- preview;
- test;
- explicit queue/send.

Start simple before introducing complex rules.

---

## 8. Phase D — Hub-assisted digests

If Hub is installed, Hello may optionally consume Hub-provided context.

Potential uses:

- get items related to a pillar;
- get recently changed items connected to a topic;
- retrieve an editorially approved related-content set;
- create a thematic digest candidate list.

Preferred model:

```text
Hub determines relevance/relationships
        ↓
Hello receives normalized candidate items
        ↓
Hello builds campaign/digest
```

Hello must not:

- maintain its own Hub relationship graph;
- query Hub tables directly;
- duplicate orphan or dependency logic;
- require Hub for normal campaigns.

---

## 9. Phase E — Public Hello service surface

Prepare a narrow public service API usable by other trusted Geeklog components and future external adapters.

Possible read operations:

```text
hello.list_campaigns
hello.get_campaign
hello.get_campaign_stats
hello.get_queue_status
hello.get_subscriber_summary
```

Possible controlled actions:

```text
hello.create_campaign_draft
hello.update_campaign_draft
hello.send_test
hello.queue_campaign
hello.pause_campaign
hello.resume_campaign
hello.stop_campaign
```

Live sending must remain separate from draft creation or update.

Possible high-risk action:

```text
hello.send_campaign
```

The exact service names and implementation mechanism should be aligned with Geeklog's shared service/resource architecture, preferably using `PLG_invokeService()` or the future common Data/API layer where appropriate.

---

## 10. Connector readiness

Hello 2.3.0 should prepare safe capabilities for the future Geeklog Connector without adding ChatGPT-specific code.

Potential capability groups:

```text
hello.campaigns.read
hello.stats.read
hello.queue.read
hello.subscribers.summary
hello.campaigns.draft
hello.campaigns.test
hello.campaigns.queue
hello.campaigns.control
hello.campaigns.send
```

Personally identifiable subscriber data must be a separate capability.

For example:

```text
hello.stats.read
```

must not imply:

```text
hello.personal_data.read
```

Default external access should favor aggregated data.

---

## 11. Subscriber privacy and consent boundaries

Hello currently relies heavily on Geeklog's registered-user email preference (`emailfromadmin`) for subscription state.

That is too broad for the 2.3.0 digest model. A user who chooses **unsubscribe from digest** must not thereby opt out of unrelated Hello/admin mailing-list messages.

2.3.0 must therefore define separate consent/subscription boundaries for at least:

```text
Hello/admin mailing eligibility
Digest subscription
```

The existing `emailfromadmin` preference may still participate in eligibility for administrative mail, but the digest unsubscribe action must be scoped to the digest.

2.3.0 should provide an administrator-facing **subscription event manager** showing who changed state, when, for which scope and through which mechanism.

Minimum event fields:

```text
user / uid
scope: campaign | digest
action: subscribe | unsubscribe
source: email_link | one_click | admin | profile | bounce
campaign/digest id when applicable
date/time
context/details
```

The current subscription state and the append-only event history must be stored separately. A new event must be appended rather than overwriting historical evidence.

2.3.0 should document clearly:

- where each subscription preference is stored;
- which unsubscribe scope applies to campaigns versus digests;
- which actions can change it;
- which service methods may expose it;
- which data is personally identifiable;
- retention behavior for tracking data;
- whether statistics are aggregate or user-level.

Any future Connector integration must not expose subscriber email addresses merely to obtain campaign performance statistics.

Possible separation:

```text
campaign summary
    sent / opened / clicked / unsubscribed

subscriber summary
    counts and rates

subscriber personal detail
    separately privileged
```

---

## 12. Campaign statistics API

Create normalized internal/public statistics helpers instead of requiring consumers to read Hello tables.

Possible campaign summary:

```php
array(
    'campaign_id' => 12,
    'sent' => 780,
    'opened' => 220,
    'unique_clickers' => 18,
    'unsubscribed' => 4,
    'open_rate' => 28.2,
    'click_rate' => 2.3,
    'unsubscribe_rate' => 0.5
);
```

Rates should be calculated by Hello, which remains authoritative for its tracking semantics.

Consumers such as Hub, Connector or future Marketing should not reconstruct Hello metrics from database tables.

---

## 13. Queue service cleanup

Formalize queue operations behind internal functions/services so the admin UI, cron and future Connector do not implement separate mutation logic.

Target operations:

```text
get queue status
process queue
pause campaign
resume campaign
stop campaign
```

Requirements:

- one authoritative implementation path;
- ACL checks at service boundary;
- CSRF for browser-admin actions;
- Connector/service authentication handled separately from browser CSRF;
- audit/logging of state changes;
- no GET-based destructive action in future refactoring;
- safe behavior under repeated calls.

---

## 14. Campaign lifecycle model

Clarify campaign states.

Possible conceptual states:

```text
draft
queued
sending
paused
completed
stopped
failed
```

Current database state should be reviewed before changing schema.

Do not introduce a migration merely for naming consistency unless it provides clear operational value.

The service API should return a normalized state even if legacy persisted values remain simpler internally.

---

## 15. Draft versus send safety

Key 2.3.0 rules:

> **Creating or updating a campaign must never implicitly send it.**

> **Preparing, refreshing or scheduling a digest must never implicitly send a live digest unless the administrator has explicitly enabled a dedicated automatic-send option.**

The automatic-send option, if implemented, must be disabled by default.

Separate actions:

```text
create draft
update draft
send test
queue/send live campaign
```

This separation is required for safe future Connector/agent use.

---

## 16. Administrator test workflow

Preserve and improve the existing test system.

Requirements:

- test goes to currently logged-in administrator;
- `[TEST]` subject remains obvious;
- test open/click tracking remains isolated from live statistics;
- simulated unsubscribe/resubscribe must not change real preferences;
- multi-source digests must use the same rendering path as live digest as far as practical;
- service API may expose a `send_test` operation only to authorized administrators.

---

## 17. Digest rendering

Refactor digest rendering so it consumes normalized items rather than Story database rows.

Rendering must be usable by preview, administrator test and live delivery so that approval is performed against substantially the same content that will be queued.

A generic item renderer should support graceful degradation:

```text
Title       required
URL         required
Excerpt     optional
Image       optional
Source      optional
Date        optional
```

The renderer should preserve current email-safe behavior:

- responsive images;
- plain-text alternative;
- SMTP-safe HTML line handling;
- direct canonical URL before optional tracking rewrite.

---

## 18. Topic and interest model

Current Story digests can use Geeklog topic interests.

Do not force the same topic model onto every plugin.

2.3.0 should distinguish:

```text
Story user-topic preferences
        vs
Generic plugin content selection
```

Possible future options:

- plugin-provided topic/category metadata;
- Hub pillar/topic context;
- administrator-selected digest source filters;
- future Marketing segments.

Do not build a new universal interest-profile database in Hello.

---

## 19. Future Marketing relationship

Hello should not become the future Marketing plugin.

Future separation:

```text
Marketing
  profiles / consent projections / tags / segments / scoring
        ↓
Hello
  campaign / queue / delivery / unsubscribe / tracking
```

Possible future Marketing-to-Hello actions:

```text
send campaign to authorized segment
trigger lifecycle email
provide recipient set reference
```

The exact contract is outside 2.3.0 unless needed to avoid a blocking design choice.

---

## 20. Common Events readiness

Hello should be prepared to consume or emit future common Geeklog events without becoming the universal event bus.

Possible future events emitted by Hello:

```text
hello.campaign.created
hello.campaign.queued
hello.campaign.sent
hello.campaign.paused
hello.campaign.completed
hello.email.opened
hello.link.clicked
hello.user.unsubscribed
```

Names are conceptual until a shared event specification exists.

Tracking events involving user identity must respect privacy and consent rules defined by the wider architecture.

---

## 21. Multisite

Hello 2.3.0 must preserve site isolation.

Requirements:

- use active `$_CONF` and `$_TABLES` context;
- no shared campaign state across sites unless explicitly designed;
- cron remains site-selectable;
- external service calls resolve the active site explicitly;
- no credential or queue leakage across sites;
- shared plugin files must remain safe during staggered site upgrades.

Follow the Memorandum multisite and shared-files upgrade rules.

---

## 22. Security review priorities

Before exposing any new public service:

- review every state-changing action;
- move destructive browser operations toward POST where not already done;
- enforce `hello.edit` and any finer future permissions at the service layer;
- validate campaign IDs and user IDs;
- escape all SQL values appropriately;
- escape HTML output by context;
- preserve safe tracking-token behavior;
- preserve same-site / stored-destination validation for legacy redirects;
- keep cron CLI-only;
- ensure service errors do not leak sensitive data;
- protect subscriber PII;
- add audit logs for external/service-triggered actions.

---

## 23. Potential permission split

Current `hello.edit` may be sufficient for the existing UI, but future service/Connector usage may justify finer permissions.

Potential future features:

```text
hello.view
hello.edit
hello.send_test
hello.send
hello.stats
hello.subscribers
```

Do not add permissions mechanically. Add them only where a real privilege boundary exists.

At minimum, live sending and subscriber personal-data access should be evaluated as distinct high-risk capabilities.

---

## 24. Backward compatibility

Target remains:

- Geeklog 2.1.1 through 2.2.2;
- PHP 5.6 through PHP 8.x where practical.

Requirements:

- no PHP syntax above the supported floor in shared code;
- feature detection preferred where Geeklog APIs differ;
- preserve legacy `COM_mail()` fallback;
- new shared-file code must work before each site's persisted upgrade completes;
- database migrations must be non-destructive and repeatable where practical.

---

## 25. Proposed 2.3.0 implementation order

### P0 — Audit, consent and send safety

1. Document current campaign/digest/queue flows.
2. Remove the current implicit live digest send from the default scheduled-task behavior.
3. Make manual review/approval the default digest workflow.
4. Separate digest unsubscribe from the broader `emailfromadmin` mailing preference.
5. Add persistent scoped subscription state plus append-only subscription event history.
5. Define the operational “last approved/sent digest” state and administrator-editable content-since date.
6. Add configurable digest subject and editable introduction to the 2.3.0 design.
7. Audit configuration labels/tooltips and remove obsolete duplicate admin-menu HTML where unused.
8. Align configuration declarations with the Memorandum configuration guidance.
9. Identify Story-specific assumptions.
10. Review subscriber-data exposure and state-changing endpoints.
11. Define normalized campaign and statistics helpers.

### P1 — Internal service boundaries

12. Centralize campaign statistics.
13. Centralize queue status/control.
14. Separate draft, test and live-send operations cleanly.
15. Define normalized campaign states.

### P2 — Generic editorial digests

16. Add normalized digest-item model.
17. Keep Stories adapter.
18. Add generic `PLG_getItemInfo()` collection adapter.
19. Add administrator source selection.
20. Add individual candidate selection/deselection.
21. Add digest preview and reusable test/live rendering path.
22. Add multi-source rendering tests.
23. Validate at least Stories plus one non-Story provider; include Tickets in provider discovery where it exposes the shared contract.

### P3 — Hub integration

24. Define optional Hub service consumption.
25. Support Hub-provided candidate/context lists without direct Hub SQL.
26. Test thematic digest workflow.

### P4 — External/resource readiness

27. Expose safe read-only Hello services.
28. Add campaign-draft service.
29. Add administrator test service.
30. Add queue-control services.
31. Add live-send service only after permissions/audit review.

### P5 — Documentation and compatibility

32. Document API/service capabilities.
33. Document digest consent and automatic-send semantics.
34. Test Geeklog 2.1.1 and 2.2.2.
35. Test PHP 5.6 and current supported PHP 8.x target.
36. Test multisite and staggered shared-file upgrades.

---

## 26. 2.3.0 completion criteria

Hello 2.3.0 should be considered ready when:

- existing campaigns still work;
- Story digest preparation still works without automatic live sending by default;
- a live digest requires explicit administrator queue/send approval unless a dedicated automatic-send option has been explicitly enabled;
- any automatic-send option is disabled by default;
- digest unsubscribe does not disable unrelated Hello/admin mailing eligibility;
- the administrator can see and control the digest content-since boundary, subject and introduction;
- digest rendering accepts normalized content items;
- at least one compatible non-Story source can be consumed through a generic Geeklog contract;
- Hub integration is optional and does not create direct dependencies;
- campaign statistics are available through a normalized helper/service;
- queue controls use an authoritative internal implementation;
- draft, test and live send are separate operations;
- personal subscriber data is not exposed by generic statistics services;
- the plugin can be safely consumed by a future provider-neutral Connector;
- multisite and compatibility tests pass.

---

## Design rule

> **Hello owns communication. It consumes shared Geeklog content and optional Hub context, then manages campaign drafting, queueing, delivery, unsubscribe handling and engagement statistics. It must not become the relationship graph, marketing profile store, external API gateway or AI layer.**
