{{-- Built-in Privacy Policy template, rendered as Markdown (not HTML) by
     App\Support\LegalPage::defaultPrivacy(). Admins can load it into the editor
     at /admin/legal and adapt it. Values are output raw on purpose: the result
     is Markdown, and LegalPage::html() strips any raw HTML anyway. --}}
> This is the default privacy policy shipped with YAAMS. It describes what the software stores. The operator of this instance is responsible for checking and completing it.

## 1. Controller

This service, **{!! $instanceName !!}**, is a virtual airline platform for flight simulation enthusiasts, run by its operator.

@if ($supportEmail)
For any privacy question or to exercise your rights, contact: <{!! $supportEmail !!}>
@else
The operator has not published a contact address yet.
@endif

## 2. What we process and why

**Account data** - your name, e-mail address and password (stored only as a one-way hash). If you enable two-factor authentication, an encrypted secret and recovery codes are stored too. We need this to provide your account (Art. 6(1)(b) GDPR).

**Airline and flight data** - your airline memberships and roles, and the flight reports (PIREPs) you file: flight numbers, aircraft, departure and arrival airports, times, fuel, route and remarks. This is the core purpose of the service (Art. 6(1)(b) GDPR). Members of your airline can see the flights you file.

**Screenshots** - flight-simulator screenshots you upload for aircraft. Uploaded images are re-encoded and their metadata (EXIF) is removed. They are visible to members of the airline.

**Notifications** - in-app notifications about your flights and memberships. E-mail notifications are only sent if you keep them enabled in your settings; security notices are always sent.

**API tokens** - if you create API tokens (e.g. for an ACARS client), we store their name, a hash of the token and when they were last used.

**Activity log** - for security and accountability we log actions such as logins, failed logins, filed and reviewed flights and changes to records, together with the account that performed them. Entries are deleted automatically after {{ $retentionDays }} days. Legal basis: our legitimate interest in operating the service securely (Art. 6(1)(f) GDPR).

**Last activity** - the time you were last active, used to show online status and instance statistics.

## 3. Cookies

We only use cookies that are strictly necessary for the service: a session cookie that keeps you logged in and a security token that protects forms against cross-site request forgery. There are no tracking, analytics or advertising cookies, so no consent banner is needed.

## 4. Recipients

Your data is not sold or shared for advertising. E-mails are delivered through the mail server configured by the operator, and the service may be hosted by a hosting provider acting on the operator's behalf.

## 5. Storage period

Your data is kept as long as your account exists. When you delete your account, your personal data is deleted. Activity log entries expire after {{ $retentionDays }} days.

## 6. Your rights

Under the GDPR you have the right to:

- **access** your data and receive a copy - you can download it yourself under *Settings - Privacy & data*
- **rectification** - you can change your name and e-mail in your profile settings
- **erasure** - you can delete your account under *Settings - Danger zone*
- **restriction** of processing and **objection** to processing based on legitimate interest
- **data portability** - the download is a machine-readable JSON file
- **lodge a complaint** with a data protection supervisory authority
