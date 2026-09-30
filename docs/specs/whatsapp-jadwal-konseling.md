# Spec — WhatsApp Notification for Scheduled Counseling Sessions

Status: agreed, ready for implementation
Provider: Fonnte (unofficial WhatsApp gateway)
Scope: `ung-konseling` backend only — **no frontend changes**

---

## Problem Statement

When a counselor approves a counseling ticket, the system creates a *sesi konseling* and emails the
**konseli** about the confirmed schedule. The konseli is never told about this over WhatsApp, even
though WhatsApp is the channel they actually read on a university campus.

Worse, the counselor who just committed to a date, time and room receives no notification at all on
that side of the flow — nothing tells them the session landed on their plate beyond the database.

Two further gaps make the existing channel unreliable:

1. **Phone numbers are never normalized.** The registration forms accept `08…`, `628…`, and `+62…`,
   all stored verbatim. A WhatsApp gateway expects a country-code-prefixed number, so any record
   saved as `08…` will silently fail to deliver.
2. **There is no failure visibility.** A WhatsApp failure would be invisible — nothing records that a
   message was attempted, to which number, or why it failed.

A konseli who shows up on the wrong day, or a counselor who double-books because they missed a
reschedule, is the concrete harm this spec prevents.

## Solution

When a sesi konseling is scheduled, rescheduled, or cancelled, the system sends the **konseli** a
WhatsApp message containing the confirmed schedule details, delivered through Fonnte.

The design principle governing recipients: **notify the party who did not make the change.**

- Counselor approves the ticket → konseli is notified (they were waiting).
- Counselor reschedules or cancels → konseli is notified (they were not the one who changed it).
- The counselor is never notified of their own action.

Messages are addressed by name, formatted in natural Indonesian, and signed by the Unit Pelayana
Bimbingan Konseling. Delivery is best-effort: a WhatsApp failure never rolls back or blocks a
legitimate schedule change, but it is logged with full diagnostic detail so it can be investigated.

## User Stories

### Scheduling

1. As a **konseli**, I want a WhatsApp message the moment my counseling ticket is approved, so that I
   find out without having to keep checking the app.
2. As a **konseli**, I want the confirmed date in the message, so that I know which day to come.
3. As a **konseli**, I want the confirmed start and end time in the message, so that I know when to
   arrive and roughly when I can leave.
4. As a **konseli**, I want the location in the message, so that I go to the right room instead of
   asking at reception.
5. As a **konseli**, I want to be addressed by name in the message, so that I know the message is
   about me and not a broadcast.
6. As a **konseli**, I want the date written in natural Indonesian rather than an ISO date, so that I
   read "Senin, 5 Oktober 2026" instead of "2026-10-05".
7. As a **konseli**, I want the message to name the unit that sent it, so that I recognise it as
   official and do not treat it as spam.
8. As a **konseli**, I want to be told not to reply, so that I do not send a reply that goes nowhere.
9. As a **konseli**, I want an email too if I have one, so that I have a channel that is permanently
   searchable.

### Rescheduling

10. As a **konseli**, I want a WhatsApp message when my session is moved, so that I do not travel to
    the campus on a day that is no longer my appointment.
11. As a **konseli**, I want the message to clearly state that the session was *moved* rather than
    newly approved, so that I do not think a second session was booked.
12. As a **konseli**, I want the new date, time and location in a reschedule message, so that the
    message is actionable on its own.
13. As a **konseli**, I want reschedule messages to stop once I have received one, so that a session
    saved twice does not produce two confusing "your session moved" messages for the same change.

### Cancellation

14. As a **konseli**, I want a WhatsApp message when my session is cancelled, so that I do not wait
    for a counselor who is not coming.
15. As a **konseli**, I want a cancellation message to be clearly distinct from an approval message,
    so that I do not mistake one for the other.

### Counselor experience

16. As a **counselor**, I want approval to send the konseli a notification without me doing anything
    extra, so that the konseli is informed the moment I commit.
17. As a **counselor**, I want the notification content and timing decided by the system, so that
    every konseli gets the same treatment regardless of who approves.
18. As a **counselor**, I want editing an unrelated field on a session not to generate a WhatsApp
    message to the konseli, so that the konseli is not confused by a change they never made.
19. As a **counselor**, I want marking a session *selesai* not to generate a WhatsApp message, so that
    closing out a session does not send the konseli a meaningless alert.
20. As a **counselor**, I want my own reschedule and cancel actions not to notify me, so that I am
    not told about something I just did.

### Message correctness

21. As a **recipient**, I want my message to arrive if my number is stored as `08…`, so that the
    format I typed when registering does not matter.
22. As a **recipient**, I want my message to arrive if my number is stored as `628…`, so that both
    common Indonesian formats work.
23. As a **recipient**, I want my message to arrive if my number is stored as `+62…`, so that a number
    copied with a leading plus is not silently dropped.
24. As a **recipient**, I want my message to arrive if my number contains spaces, dashes or dots, so
    that formatting pasted from a contact card does not break delivery.
25. As a **recipient**, I want my number stripped of formatting before it is sent, so that what is
    stored in the database can never be compared against what was actually delivered.

### Failure behaviour

26. As a **konseli**, I want my approved session saved even if the WhatsApp gateway is down, so that a
    third-party outage cannot cost me my appointment.
27. As a **konseli**, I want my approved session saved even if my phone number is malformed, so that a
    data-quality problem on my record does not cost me my appointment.
28. As a **konseli**, I want my reschedule saved even if notification fails, so that a gateway outage
    cannot block the counselor from fixing a scheduling mistake.
29. As a **system operator**, I want every failed send logged with the target number and the
    gateway's reason, so that I can answer "did this konseli get the message?" without guessing.
30. As a **system operator**, I want a missing phone number logged as a distinct warning rather than
    treated as a gateway failure, so that a skipped send is distinguishable from a rejected one.
31. As a **system operator**, I want the gateway request to time out quickly, so that a hanging
    third-party API cannot make the approval request feel broken to the counselor.

### Configuration and operations

32. As a **system operator**, I want the gateway token read from configuration, so that
    `config:cache` in production does not silently null it out.
33. As a **system operator**, I want an example environment file documenting the required token key,
    so that a new developer knows the gateway needs configuring at all.
34. As a **system operator**, I want the sending country's dialling code configurable, so that a future
    deployment outside Indonesia needs a one-line change.
35. As a **system operator**, I want the signing unit name in configuration rather than inline, so that
    rebranding the unit does not require a code change.
36. As a **system operator**, I want to be able to find the one place in the code where the sending
    number is configured, so that swapping to a dedicated institutional number later is obvious.

### Data integrity

37. As a **system operator**, I want to be able to register a counselor without a phone number, so
    that a missing optional field does not produce a database error.
38. As a **system operator**, I want existing phone numbers normalized in place, so that historical
    records deliver without requiring anyone to re-enter them.
39. As a **system operator**, I want a counselor with no phone number to remain fully usable, so that
    one missing field does not block them from taking sessions.
40. As a **konseli**, I want my existing greeting in the schedule email to actually contain my name,
    so that the email does not open with an empty salutation.

### Verification

41. As a **developer**, I want to trigger a real send from the command line without a frontend, so
    that I can verify delivery before wiring anything to the UI.
42. As a **developer**, I want tests that assert on the outbound HTTP request rather than on internal
    method calls, so that tests keep passing when the internals are refactored.
43. As a **developer**, I want tests proving that editing an unrelated field sends nothing, so that a
    regression in change detection is caught.
44. As a **developer**, I want tests covering every accepted phone number format, so that a future
    change to normalization cannot silently break delivery for one format.

## Implementation Decisions

### Trigger points and recipients

Three notification events, all delivered to the **konseli** only:

| Event | Fires when | Recipient |
| --- | --- | --- |
| Approved | A sesi konseling is created via the schedule endpoint | Konseli |
| Rescheduled | An update changes `tanggal_konseling`, `jam_mulai`, `jam_selesai` or `tempat` | Konseli |
| Cancelled | An update changes `status` to `dibatalkan` | Konseli |

The counselor is never a WhatsApp recipient. The governing principle: **notify the party who did not
make the change.** A counselor who reschedules does not need to be told about their own action; the
konseli does.

Reschedule detection compares the four schedule fields before and after the write. The previous
values must be captured *before* the update is applied, following the status-transition comparison
pattern already used for ticket rejection in the ticket service. A session marked `selesai`, or a
session edited only in unrelated fields such as counselor notes, must send nothing.

The frontend issues ticket status update and session creation as two separate requests.
Notification hangs off session creation, not ticket status, because session creation is where the
schedule actually becomes concrete.

### Message content

The message addresses the konseli by name and states that their session was approved by a counselor.
It carries the confirmed date, time and location, then the signing unit, then a no-reply notice.

```
Yang hormat {nama_konseli},

Sesi konseling yang Anda ajukan telah disetujui.
Berikut detail jadwalnya:

Tanggal: {tanggal}
Waktu  : {jam_mulai} - {jam_selesai}
Tempat : {tempat}

Unit Pelayanan Bimbingan Konseling
Universitas Negeri Gorontalo

Pesan ini dikirim otomatis, mohon tidak dibalas.
```

The konseli's **name is included**. This was reconsidered during the design conversation: an earlier
decision favoured a name-free message for privacy, but since every message arrives from the same
sending number, a message without a name is ambiguous — a konseli cannot tell whether it concerns
them. The name is the difference between a useful message and an unread one.

The approver is described as a counselor. In this system the counselor approves the ticket and the
konseli submits it; describing the konseli as the approver would be factually wrong.

Dates are formatted as natural Indonesian via Carbon's locale-aware translation (`l, j F Y` under the
`id` locale), producing e.g. `Senin, 5 Oktober 2026`. Verified working in this codebase: the `intl`
extension is installed and Carbon ships Indonesian translations, so no new dependency is required.
Locale is applied per-message rather than by changing the application locale, so the rest of the
application's date handling is unaffected.

No reply is expected and none is handled. There is no webhook, so a reply would be received by the
gateway and never surface in the system. The closing line states this to the recipient.

### Provider integration

Fonnte, an unofficial gateway driven by a paired WhatsApp account. Send endpoint is
`POST https://api.fonnte.com/send`, authenticated with the token in an `Authorization` header **with
no `Bearer` prefix**. The target is sent as a string, never as a number.

The gateway device was verified live during design: connected, on the Free plan, with a monthly quota
of 1000 messages and zero messages sent to date.

HTTP calls use a 5-second timeout with **no retries**. A retry would delay the counselor's approval
response for the sake of a message that is not part of the transaction.

This is a **premium-rate unofficial gateway with real operational risks**, accepted knowingly:

- The Free plan appends a `sent via fonnte` watermark to messages and disconnects devices frequently
  by design. The plan is being upgraded to Regular.
- The Free plan does **not** send a disconnect webhook, so a dropped device is invisible until
  messages start failing.
- The quota is **monthly**, not daily. Exhaustion means messages are discarded with an
  `insufficient quota` response and are not stored for later delivery.
- Device selection is by token, not by request parameter. Sending with multiple comma-separated
  tokens distributes targets randomly, so exactly one token is configured.
- The sending number is fixed to the paired WhatsApp account and **cannot** be set server-side — there
  is no sender parameter in the API. The configuration change is therefore paired with a clearly
  commented location in the code identifying where the account swap happens.

### Phone number normalization

Normalization happens in the service layer at the point of sending. Database values are left in
whatever shape the registration form produced.

Normalization strips spaces, dashes, dots and parentheses, removes a leading `+`, converts a leading
`0` to the country code `62`, and leaves an already-prefixed `62` number untouched. A value containing
no usable digits yields no send, logged as a warning — deliberately distinct from a gateway failure,
so a skipped send can be told apart from a rejected one.

The gateway would itself convert a leading `0`, but not a `+62` form, so normalizing explicitly keeps
one code path rather than relying on undocumented gateway behaviour.

Backfill of existing records is a one-off manual operation. No new column is added and the frontend is
not modified, so the system does not depend on a single entry point staying correct.

`konselors.phone` is declared `NOT NULL` in its migration while its validation rule is
`sometimes|nullable`. This mismatch means registering a counselor without a phone number produces a
database error rather than a validation error. The column is made nullable to match the validation
rule: a counselor without a phone number stays fully usable and simply receives no WhatsApp. Making
the field required instead would block counselors from being created over a missing optional field.

The service layer is also the abstraction boundary, so that swapping gateway providers later touches
one component only.

### Configuration

- Gateway token and country code move into the services config. The existing implementation read the
  environment directly from a service, which silently returns `null` once config is cached — a latent
  production failure.
- An example environment file is created. None exists, so nothing currently documents that a gateway
  token is required at all.
- Message footer branding goes into a dedicated message config file, so rebranding the unit does not
  require touching code.

### Failure semantics

Delivery is best-effort and never transactional. A gateway failure must not roll back or block a
legitimate schedule change.

Concretely: session creation, rescheduling and cancellation all commit regardless of send outcome.
Notification is attempted after the transaction commits, so a gateway call can never hold a database
transaction open. Every failure is logged with the target number, the event, the gateway's stated
reason and the raw response, so "did this konseli receive the message?" is answerable.

The existing schedule email is a synchronous notification and an SMTP failure would propagate as an
exception. Left as-is: pre-existing behaviour, out of scope.

### Explicitly rejected: persistence and idempotency

An earlier design included a message-history table supporting an idempotency key, quota tracking and
delivery auditing. This was **deliberately removed** during the design conversation, with these
consequences knowingly accepted:

- **No idempotency.** Duplicate messages are possible if the approval action is triggered twice, or if
  the connection drops after the gateway has already accepted the message. A konseli may receive two
  identical messages. No cache-based guard is used either — the decision is to send with no
  duplicate-prevention mechanism.
- **No delivery history.** No record of what was sent, to whom, or when. Answering "did this konseli
  get the message?" means reading logs.
- **No quota tracking.** Exhausting the monthly quota means messages are discarded with an
  `insufficient quota` response, and nothing surfaces or predicts that condition.
- **No UI verification.** A "send test message" admin control was considered and rejected; testing is
  done from the command line instead.

### Test seam

**One seam: the HTTP boundary.** Every test drives the feature through the public API endpoints
(`POST` for creation, `PUT` for reschedule and cancel) with the outbound HTTP layer faked, asserting
on the request that would have been sent to the gateway.

This is the highest available seam and deliberately the only one. Testing at the service or gateway
client level would assert on internal method calls and break under refactoring that does not change
behaviour. Going through the endpoints also picks up the route, form request validation and model
relationships for free — which is where the phone fields and schedule field names actually live.

This seam additionally guards a real hazard in the existing codebase: the frontend and backend use
different field names for the same schedule data, and the translation happens in the frontend model
layer. An endpoint-level test pins the contract that must hold regardless of how the frontend maps
its form values.

### Modules modified

- **Gateway client service** — rewritten. Reads configuration instead of raw environment access,
  applies normalization, enforces the timeout, returns a structured success flag with the failure
  reason rather than a bare boolean, and logs failures with diagnostic detail.
- **Session service** — gains notification on creation, plus notification on reschedule and
  cancellation via change detection. Existing creation behaviour otherwise preserved.
- **Schedule notification class** — gains a WhatsApp variant of the existing notification, and the
  existing salutation bug is fixed: it greets from a `nama` attribute that does not exist on the user
  model (which uses `name`), so every schedule email currently opens with an empty salutation.
- **Configuration** — gateway entries added to the services config; a message config file created; an
  example environment file created.
- **Schema** — the counselor phone column is made nullable.

### Frontend

No changes. The frontend already sends correctly-named schedule fields through its model layer, and no
new UI is being introduced.

## Testing Decisions

A good test here asserts **what the recipient would observe** — the outbound request body — and
nothing about how the code is arranged internally. If a test would still pass after replacing the
message-building code with a different implementation producing the same text, it is a good test. A
test asserting "the gateway client was called with these arguments" is not, because it couples to the
current structure.

**No prior art exists.** The backend has only the two stock Laravel example tests and no
application-level tests; the frontend has two scaffold placeholder tests. These will be the first real
tests in the project, so they set the pattern and should be written to be copied.

### Modules tested

Exercised through the HTTP seam:

- **Phone normalization** — the highest-value target, being pure logic with many branches and a
  silent-failure mode. Cover each accepted form (`08…`, `628…`, `+62…`, and a number containing
  spaces and dashes) asserting the exact normalized string sent. Cover the malformed case asserting
  no send is attempted and a warning is logged.
- **Outbound request shape** — assert the authorization header carries the token with no `Bearer`
  prefix, that the target is a string, and that the country code is passed. This pins the contract
  with a provider whose documentation is explicit about these details.
- **Message content** — assert the konseli's name, the schedule values and the Indonesian-formatted
  date appear in the sent message. The date assertion guards against a locale regression that would
  silently produce English dates.
- **Change detection** — assert a reschedule sends, an unrelated field edit sends nothing, and a
  transition to `selesai` sends nothing. Most likely behaviour to regress unnoticed, because a false
  positive spams real students.
- **Cancellation** — assert the cancellation message is distinct from the approval message.
- **Failure isolation** — with a gateway response reporting failure, assert the session is still
  created and persisted. This is the load-bearing guarantee of the best-effort design.
- **Approval email salutation** — assert the schedule email greets the konseli by name, pinning the
  `nama` versus `name` fix.

## Out of Scope

- **No frontend changes whatsoever.** No new UI, no send-test button, no settings page.
- **No message-history table.** Delivery history is not persisted.
- **No idempotency or duplicate suppression.** Accepted as a known consequence.
- **No quota monitoring, alerting or dashboard.** Remaining quota is not tracked; exhaustion is silent.
- **No provider webhook or inbound message handling.** Replies are not received, stored, or acted on.
  No auto-reply.
- **No message templates, localisation framework or blade mail templates.** Message text is a single
  config-backed string.
- **No scheduled cleanup or retention policy.** Logs follow the application's existing log rotation.
- **No provider swap.** Fonnte stays. The service-layer boundary exists to make a future swap cheap,
  but the work itself is not scheduled.
- **No fix for the synchronous email failure mode.** A schedule email that fails to send still
  propagates as an error; only the WhatsApp path was made failure-tolerant.
- **No consent-capture flow** for WhatsApp messaging. The gateway's reach-out time-lock and WhatsApp's
  anti-spam measures are known risks, accepted.
- **No scheduled reminders** before a session, and none on the day of a session.
- **No change to how sessions, tickets or counselors are created** beyond the phone-nullability fix.

## Further Notes

**Secrets.** The gateway token was shared in a working conversation during design and must be treated
as exposed. Rotate it in the gateway dashboard before deploying, and store the replacement only in the
environment file, which is already excluded from version control. No example environment file, config
file or committed source may contain a real token.

**Two silent-failure modes to be aware of in operation.** The first is the monthly quota: when
exhausted, messages are discarded with an `insufficient quota` response and are not queued for later
delivery, so notifications stop for the rest of the month with nothing surfacing it. The second is a
dropped gateway device, which on the Free plan raises no notification at all. Both become visible
only as delivery failures in the logs. This is the direct cost of rejecting the monitoring and
quota-tracking options.

**The sending number is a personal number.** It is visible to every konseli and counselor who receives
a message, and is subject to being banned by WhatsApp, which would halt all notifications until a
device is reconnected. Accepted as temporary. When swapping to an institutional number, the
configuration and the commented change-point in the code are the only two things that need to change.

**Scope boundary worth flagging for later.** The counselor side of this flow currently has no
notification of any kind when a session is created against them — nothing tells them a ticket approval
landed on their plate. That gap exists independently of WhatsApp and was consciously left alone here,
on the basis that this feature notifies the party waiting for an answer rather than the party who gave
it.

**A pre-existing latent failure exists in the schedule email.** Its salutation reads from an attribute
the user model does not have, so every schedule email currently greets with an empty name. Fixed as
part of this work because it is in the same notification path and is a one-line change, but not part
of the WhatsApp feature itself.
