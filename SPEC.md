# LastWillApp — Product Spec (v0.1 draft)

## 1. Problem

People accumulate assets and important information (bank accounts, digital
accounts, physical items, instructions) that the people close to them don't
know about — not necessarily secret, just never shared because of inertia.
If that person dies or disappears, this information is lost or hard to find
exactly when it's needed most.

LastWillApp is a "dead man's switch" style service: a user records this
information once, the app periodically checks that they're still alive and
reachable, and if they stop responding, it automatically delivers the
recorded information to pre-designated trusted contacts.

## 2. Actors

- **Willowner** — the person who owns the information and wants it delivered
  if something happens to them. Has an account.
- **Willreceiver** — a trusted contact designated by the willowner to receive
  the information. Exactly 2 per willowner (first and second priority). Does
  **not** need an account — identified by email, interacts via emailed
  magic links.

## 3. Core concepts & data model (conceptual, not final schema)

- **Owner** — account: email, auth credential, status (`active`, `at_risk`,
  `disappeared`), timestamps.
- **Record** — a single block of plain text per owner (not multiple
  records in v1 — one owner has exactly one text blob they keep up to
  date). Future: typed asset entries, file attachments, multiple records.
- **Willreceiver slot** — exactly 2 per owner, ranked (1st, 2nd), each just
  an email address (+ optional name). No account required.
- **CheckIn** — a scheduled prompt sent to the owner, with its outcome
  (confirmed / missed) and timestamp.
- **EscalationState** — tracks where in the disappearance/reveal pipeline an
  owner currently is (see state machine below).

## 4. Owner check-in lifecycle (state machine)

```
ACTIVE
  → every 30 days: send check-in email with confirmation link
  → owner clicks within window → stays ACTIVE, next check-in in 30 days
  → owner misses it → AT_RISK

AT_RISK
  → send a retry check-in email 7 days after the missed one
  → owner clicks → back to ACTIVE (next check-in in 30 days)
  → owner misses it → DAILY_RETRY

DAILY_RETRY
  → send one check-in email per day, for 7 consecutive days
  → owner clicks ANY of these → back to ACTIVE
  → owner misses all 7 → DISAPPEARED
```

So total unresponsive window before being declared disappeared: **7 days
(AT_RISK retry) + 7 days (daily retries) ≈ 14 days** after the original
missed monthly check-in.

Every check-in email contains a single-use, time-limited confirmation link.

## 5. Reveal / escalation lifecycle (after DISAPPEARED)

```
DISAPPEARED
  → email Willreceiver #1: link to a page to view/download all records,
    plus a "mark as received" action
  → receiver confirms within 3 days → DONE (receiver #1 acknowledged)
  → receiver does not confirm within 3 days → escalate to Willreceiver #2
       (receiver #1's link presumably still works if they come back later —
       TBD, see open questions)

Willreceiver #2 gets the same flow (view/download + mark as received),
same 3-day window tracked for completeness, but there's no #3 to escalate to
— if #2 also doesn't confirm, the info remains available via their link
  indefinitely (nothing further to escalate to).
```

## 6. Self-recovery

- At any point — including **after** reaching `DISAPPEARED` and even after
  a willreceiver has already viewed/downloaded the records — the owner can
  log back in and prove it's them (e.g. normal auth, possibly + extra
  verification given the stakes) to reset their status to `ACTIVE`.
- Recovery stops any further escalation (no more reveal emails go out) and
  resumes the normal 30-day check-in cycle.
- **Important nuance:** recovery cannot un-reveal information. If
  Willreceiver #1 already opened the download link before the owner
  recovered, that information was already exposed — recovery only prevents
  *further* exposure (e.g. stops #2 from being notified) and returns the
  account to normal operation going forward.

## 7. Functional requirements

### Owner-facing
- Sign up / log in.
- Create/edit their single text record at any time while not `DISAPPEARED`.
  The record input is accompanied by an explicit, hard-to-miss warning:
  **do not store actual secrets here** (passwords, bank account numbers,
  PINs, etc.) — because this content is reachable via emailed links, and
  email accounts can be compromised. Instead, the record should describe
  *where* to find things (e.g. "the password list is in the top drawer of
  my desk" / "see the file `passwords.kdbx` on my home PC, master password
  is taped under the keyboard" / "ask my brother, he knows the safe combo")
  — i.e. point to a location or person reachable by a willreceiver in
  person, not a secret reachable by anyone who reads this email.
- Add/edit the 2 willreceiver email addresses.
- Receive check-in emails per the state machine in §4; click to confirm.
- See their own current status in-app (last check-in, next check-in due,
  current state — `ACTIVE` / `AT_RISK` / `DAILY_RETRY` / `DISAPPEARED`).
  The app reflects the same state the emails do, and the owner can check
  in from inside the app (not only via the emailed link) to return to
  `ACTIVE` from any non-disappeared state.
- Recover from `DISAPPEARED` by logging in normally (v1: standard login is
  sufficient; 2FA for this specific action is planned as a future
  hardening step, not required for v1).

### Willreceiver-facing
- Receive an email with a secure link (no account needed).
- View/download the owner's records via that link.
- Mark receipt as confirmed (stops escalation to the next receiver).

### System
- Scheduler to send check-in emails per the state machine.
- Scheduler to send escalation/reveal emails per §5.
- Secure, single-use/expiring tokens for both check-in links and
  willreceiver reveal links.
- Audit trail: log every check-in sent/confirmed/missed and every reveal
  event, for the owner's own visibility and for dispute resolution.

## 8. Non-functional requirements

Since the stated intent is to eventually launch to real users, these matter
from day one even if the MVP implementation is simple:

- **Data sensitivity**: the product is explicitly designed so the record
  should *not* contain actual secrets (see the owner-facing warning in
  §7) — it should contain pointers to where secrets live, not the secrets
  themselves. This meaningfully lowers the blast radius of a compromise,
  but doesn't eliminate it (a pointer like "ask my brother" or "see the
  file on my home PC" is still personal/sensitive info). Encrypt record
  content at rest regardless, and minimize what's logged, as defense in
  depth — don't rely solely on the warning to keep secrets out.
- **Email deliverability**: this entire product fails if check-in emails
  land in spam. Needs a reputable transactional email provider and
  SPF/DKIM/DMARC set up correctly.
- **Token security**: check-in and reveal links must be unguessable,
  single-use or expiring, and not leak record content if forwarded/cached.
- **Account security**: since a compromised owner account exposes all
  their records to an attacker, consider stronger auth (e.g. not just a
  password) even in early versions.
- **Reliability of the scheduler**: missed check-ins have real
  consequences (premature reveal) or false negatives (never revealing when
  it should) — the job that sends check-ins/escalations must be monitored.
- **Legal disclaimer**: this is explicitly *not* a legal will / estate
  planning service — just an information dead-man's-switch. Should be
  stated clearly in the product (ToS/footer), to avoid implying legal
  authority it doesn't have.
- **Data export/deletion**: for eventual real users, expect to need
  account deletion and data export (privacy regulation hygiene).

## 9. Explicitly out of scope for v1

- File attachments / structured asset schemas (text only for now, per
  owner's answer — may come later).
- More than 2 willreceivers.
- Legal document generation (this is not a will-drafting tool).
- Any payment/billing.

## 10. Resolved decisions (from v0.1 review)

1. **Willreceiver #1's link stays valid indefinitely** after the 3-day
   window lapses and #2 is notified — no revocation, since there's no
   reason to lock out a late-but-genuine receiver once in `DISAPPEARED`.
2. **Self-recovery auth**: normal login is sufficient in v1. 2FA (or other
   stronger verification specifically for recovering from `DISAPPEARED`)
   is a planned future hardening step, not a v1 requirement.
3. **Email tone**, by stage:
   - Monthly check-in: *not* casual — it's a regular, expected email, but
     still communicates real stakes (this keeps your info from being
     shared) without alarm.
   - Daily retry (`DAILY_RETRY`): urgent/emergency tone — explicitly
     communicates that this is the last stage before escalation.
   - Willreceiver emails: neutral and factual — informs them that the
     willowner had set up this service so information would reach them if
     the willowner missed their check-ins, without presuming death or
     being alarmist.
4. **Timezones are handled explicitly** — all check-in/escalation windows
   are computed in a stored timezone (likely the owner's), not naively in
   UTC or server-local time.
5. **In-app status visibility**: the owner's current state (`ACTIVE` /
   `AT_RISK` / `DAILY_RETRY` / `DISAPPEARED`) is always visible in the app,
   and checking in can be done either via the emailed link or directly in
   the app.

## 11. Remaining open questions

- Exact wording/subject lines for each email stage (can draft once we're
  building, not a blocker for the stack discussion).
- Whether recovering from `DISAPPEARED` should notify the willreceivers
  that a reveal was a false alarm (e.g. "owner is fine, disregard") —
  seems considerate but not yet decided.

---

Next step per the agreed plan: discuss tech stack (step 2) before any
building starts.
