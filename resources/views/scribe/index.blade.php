<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="IE=edge,chrome=1" http-equiv="X-UA-Compatible">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>YAAMS API Documentation</title>

    <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset("/vendor/scribe/css/theme-default.style.css") }}" media="screen">
    <link rel="stylesheet" href="{{ asset("/vendor/scribe/css/theme-default.print.css") }}" media="print">

    <script src="https://cdn.jsdelivr.net/npm/lodash@4.17.10/lodash.min.js"></script>

    <link rel="stylesheet"
          href="https://unpkg.com/@highlightjs/cdn-assets@11.6.0/styles/obsidian.min.css">
    <script src="https://unpkg.com/@highlightjs/cdn-assets@11.6.0/highlight.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jets/0.14.1/jets.min.js"></script>

    <style id="language-style">
        /* starts out as display none and is replaced with js later  */
                    body .content .bash-example code { display: none; }
                    body .content .javascript-example code { display: none; }
                    body .content .php-example code { display: none; }
            </style>

    <script>
        var tryItOutBaseUrl = "http://localhost:8000";
        var useCsrf = Boolean();
        var csrfUrl = "/sanctum/csrf-cookie";
    </script>
    <script src="{{ asset("/vendor/scribe/js/tryitout-5.11.0.js") }}"></script>

    <script src="{{ asset("/vendor/scribe/js/theme-default-5.11.0.js") }}"></script>

</head>

<body data-languages="[&quot;bash&quot;,&quot;javascript&quot;,&quot;php&quot;]">

<a href="#" id="nav-button">
    <span>
        MENU
        <img src="{{ asset("/vendor/scribe/images/navbar.png") }}" alt="navbar-image"/>
    </span>
</a>
<div class="tocify-wrapper">
    
            <div class="lang-selector">
                                            <button type="button" class="lang-button" data-language-name="bash">bash</button>
                                            <button type="button" class="lang-button" data-language-name="javascript">javascript</button>
                                            <button type="button" class="lang-button" data-language-name="php">php</button>
                    </div>
    
    <div class="search">
        <input type="text" class="search" id="input-search" placeholder="Search">
    </div>

    <div id="toc">
                    <ul id="tocify-header-introduction" class="tocify-header">
                <li class="tocify-item level-1" data-unique="introduction">
                    <a href="#introduction">Introduction</a>
                </li>
                            </ul>
                    <ul id="tocify-header-authenticating-requests" class="tocify-header">
                <li class="tocify-item level-1" data-unique="authenticating-requests">
                    <a href="#authenticating-requests">Authenticating requests</a>
                </li>
                            </ul>
                    <ul id="tocify-header-account" class="tocify-header">
                <li class="tocify-item level-1" data-unique="account">
                    <a href="#account">Account</a>
                </li>
                                    <ul id="tocify-subheader-account" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="account-GETapi-v1-user">
                                <a href="#account-GETapi-v1-user">Current user</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-aircraft" class="tocify-header">
                <li class="tocify-item level-1" data-unique="aircraft">
                    <a href="#aircraft">Aircraft</a>
                </li>
                                    <ul id="tocify-subheader-aircraft" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="aircraft-GETapi-v1-airlines--airline_id--aircraft">
                                <a href="#aircraft-GETapi-v1-airlines--airline_id--aircraft">List fleet</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="aircraft-POSTapi-v1-airlines--airline_id--aircraft">
                                <a href="#aircraft-POSTapi-v1-airlines--airline_id--aircraft">Add an aircraft</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="aircraft-GETapi-v1-airlines--airline_id--aircraft--id-">
                                <a href="#aircraft-GETapi-v1-airlines--airline_id--aircraft--id-">Show an aircraft</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-airlines" class="tocify-header">
                <li class="tocify-item level-1" data-unique="airlines">
                    <a href="#airlines">Airlines</a>
                </li>
                                    <ul id="tocify-subheader-airlines" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="airlines-GETapi-v1-airlines">
                                <a href="#airlines-GETapi-v1-airlines">List airlines</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="airlines-POSTapi-v1-airlines">
                                <a href="#airlines-POSTapi-v1-airlines">Found a new airline</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="airlines-GETapi-v1-airlines--id-">
                                <a href="#airlines-GETapi-v1-airlines--id-">Show an airline</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-flights" class="tocify-header">
                <li class="tocify-item level-1" data-unique="flights">
                    <a href="#flights">Flights</a>
                </li>
                                    <ul id="tocify-subheader-flights" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="flights-GETapi-v1-airlines--airline_id--flights">
                                <a href="#flights-GETapi-v1-airlines--airline_id--flights">List my flights</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="flights-POSTapi-v1-airlines--airline_id--flights">
                                <a href="#flights-POSTapi-v1-airlines--airline_id--flights">File a PIREP</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="flights-GETapi-v1-airlines--airline_id--flights-review">
                                <a href="#flights-GETapi-v1-airlines--airline_id--flights-review">List the review queue</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="flights-POSTapi-v1-flights--flight_id--accept">
                                <a href="#flights-POSTapi-v1-flights--flight_id--accept">Accept a PIREP</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="flights-POSTapi-v1-flights--flight_id--reject">
                                <a href="#flights-POSTapi-v1-flights--flight_id--reject">Reject a PIREP</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-instance" class="tocify-header">
                <li class="tocify-item level-1" data-unique="instance">
                    <a href="#instance">Instance</a>
                </li>
                                    <ul id="tocify-subheader-instance" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="instance-GETapi-v1-info">
                                <a href="#instance-GETapi-v1-info">Instance info</a>
                            </li>
                                                                        </ul>
                            </ul>
            </div>

    <ul class="toc-footer" id="toc-footer">
                    <li style="padding-bottom: 5px;"><a href="{{ route("scribe.postman") }}">View Postman collection</a></li>
                            <li style="padding-bottom: 5px;"><a href="{{ route("scribe.openapi") }}">View OpenAPI spec</a></li>
                <li><a href="http://github.com/knuckleswtf/scribe">Documentation powered by Scribe ✍</a></li>
    </ul>

    <ul class="toc-footer" id="last-updated">
        <li>Last updated: July 14, 2026</li>
    </ul>
</div>

<div class="page-wrapper">
    <div class="dark-box"></div>
    <div class="content">
        <h1 id="introduction">Introduction</h1>
<p>The YAAMS REST API (v1) for virtual airline management - airlines, fleet and PIREPs. All endpoints live under /api/v1 and return JSON.</p>
<aside>
    <strong>Base URL</strong>: <code>http://localhost:8000</code>
</aside>
<pre><code>This documentation covers the YAAMS REST API v1. Every endpoint is served under `/api/v1` and returns JSON.

Except for the public `GET /api/v1/info` endpoint, requests must be authenticated with a personal Sanctum bearer token (see the Authenticating section below).

&lt;aside&gt;As you scroll, you'll see code examples for working with the API in different programming languages in the dark area to the right (or as part of the content on mobile).
You can switch the language used with the tabs at the top right (or from the nav menu at the top left on mobile).&lt;/aside&gt;</code></pre>

        <h1 id="authenticating-requests">Authenticating requests</h1>
<p>To authenticate requests, include an <strong><code>Authorization</code></strong> header with the value <strong><code>"Bearer {YOUR_AUTH_KEY}"</code></strong>.</p>
<p>All authenticated endpoints are marked with a <code>requires authentication</code> badge in the documentation below.</p>
<p>Create a personal API token from your account settings (<b>Settings &rarr; API tokens</b>) in the web UI, then send it as a bearer token: <code>Authorization: Bearer {YOUR_TOKEN}</code>. Tokens are managed with <a href="https://laravel.com/docs/sanctum">Laravel Sanctum</a>.</p>

        <h1 id="account">Account</h1>

    <p>The token owner's own account.</p>

                                <h2 id="account-GETapi-v1-user">Current user</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>"Who am I" - returns the account the API token belongs to, with its
airline memberships. Useful as a token sanity check for API clients.</p>

<span id="example-requests-GETapi-v1-user">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/user" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/user"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/user';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {YOUR_AUTH_KEY}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-user">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: 1,
        &quot;name&quot;: &quot;Homer Simpson&quot;,
        &quot;email&quot;: &quot;homer@test.com&quot;,
        &quot;airlines&quot;: [
            {
                &quot;id&quot;: 1,
                &quot;name&quot;: &quot;Example Virtual Airlines&quot;,
                &quot;prefix&quot;: &quot;EV&quot;,
                &quot;icaoCallsign&quot;: &quot;EVA&quot;,
                &quot;atcCallsign&quot;: &quot;EXAMPLE&quot;,
                &quot;unitIsLbs&quot;: false,
                &quot;requirePirepReview&quot;: true,
                &quot;locationContinuity&quot;: false,
                &quot;createdAt&quot;: &quot;2026-01-01T00:00:00.000000Z&quot;,
                &quot;updatedAt&quot;: &quot;2026-01-01T00:00:00.000000Z&quot;
            }
        ]
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-user" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-user"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-user"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-user" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-user">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-user" data-method="GET"
      data-path="api/v1/user"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-user', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-user"
                    onclick="tryItOut('GETapi-v1-user');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-user"
                    onclick="cancelTryOut('GETapi-v1-user');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-user"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/user</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-v1-user"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-user"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-user"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                <h1 id="aircraft">Aircraft</h1>

    <p>An airline's fleet. Aircraft are scoped to their airline - an aircraft that
does not belong to {airline} resolves as a 404.</p>

                                <h2 id="aircraft-GETapi-v1-airlines--airline_id--aircraft">List fleet</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>List the airline's aircraft.</p>

<span id="example-requests-GETapi-v1-airlines--airline_id--aircraft">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/airlines/16/aircraft" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/airlines/16/aircraft"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/airlines/16/aircraft';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {YOUR_AUTH_KEY}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-airlines--airline_id--aircraft">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: [
        {
            &quot;id&quot;: 7,
            &quot;registration&quot;: &quot;D-EXAM&quot;,
            &quot;manufacturer&quot;: &quot;Airbus&quot;,
            &quot;model&quot;: &quot;A320-200&quot;,
            &quot;currentLoc&quot;: &quot;EDDF&quot;,
            &quot;engineType&quot;: &quot;CFM56&quot;,
            &quot;satcom&quot;: false,
            &quot;winglets&quot;: true,
            &quot;selcal&quot;: &quot;AB-CD&quot;,
            &quot;hexCode&quot;: &quot;3C6444&quot;,
            &quot;msn&quot;: &quot;1234&quot;,
            &quot;mtow&quot;: 78000,
            &quot;mzfw&quot;: 62500,
            &quot;mlw&quot;: 66000,
            &quot;remarks&quot;: null,
            &quot;status&quot;: &quot;active&quot;,
            &quot;active&quot;: true,
            &quot;retiredAt&quot;: null,
            &quot;retiredReason&quot;: null,
            &quot;inServiceSince&quot;: &quot;2020-01-01&quot;,
            &quot;firstFlight&quot;: &quot;2019-11-15&quot;,
            &quot;createdAt&quot;: &quot;2026-01-01T00:00:00.000000Z&quot;,
            &quot;updatedAt&quot;: &quot;2026-01-01T00:00:00.000000Z&quot;
        }
    ]
}</code>
 </pre>
            <blockquote>
            <p>Example response (403):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;You are not a member of this airline.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-airlines--airline_id--aircraft" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-airlines--airline_id--aircraft"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-airlines--airline_id--aircraft"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-airlines--airline_id--aircraft" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-airlines--airline_id--aircraft">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-airlines--airline_id--aircraft" data-method="GET"
      data-path="api/v1/airlines/{airline_id}/aircraft"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-airlines--airline_id--aircraft', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-airlines--airline_id--aircraft"
                    onclick="tryItOut('GETapi-v1-airlines--airline_id--aircraft');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-airlines--airline_id--aircraft"
                    onclick="cancelTryOut('GETapi-v1-airlines--airline_id--aircraft');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-airlines--airline_id--aircraft"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/airlines/{airline_id}/aircraft</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-v1-airlines--airline_id--aircraft"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-airlines--airline_id--aircraft"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-airlines--airline_id--aircraft"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>airline_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="airline_id"                data-endpoint="GETapi-v1-airlines--airline_id--aircraft"
               value="16"
               data-component="url">
    <br>
<p>The ID of the airline. Example: <code>16</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>airline</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="airline"                data-endpoint="GETapi-v1-airlines--airline_id--aircraft"
               value="1"
               data-component="url">
    <br>
<p>The airline ID. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="aircraft-POSTapi-v1-airlines--airline_id--aircraft">Add an aircraft</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Add an aircraft to the airline's fleet. Requires the "add aircraft"
permission and membership of the airline. Permission, membership,
normalization and the duplicate-registration rule are enforced by
StoreAircraftRequest.</p>

<span id="example-requests-POSTapi-v1-airlines--airline_id--aircraft">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/airlines/16/aircraft" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"registration\": \"D-EXAM\",
    \"manufacturer\": \"Airbus\",
    \"model\": \"A320-200\",
    \"engine_type\": \"CFM56\",
    \"satcom\": false,
    \"winglets\": true,
    \"selcal\": \"AB-CD\",
    \"hex_code\": \"3C6444\",
    \"msn\": \"1234\",
    \"mtow\": 78000,
    \"mzfw\": 62500,
    \"mlw\": 66000,
    \"remarks\": \"Delivered new.\",
    \"current_loc\": \"EDDF\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/airlines/16/aircraft"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "registration": "D-EXAM",
    "manufacturer": "Airbus",
    "model": "A320-200",
    "engine_type": "CFM56",
    "satcom": false,
    "winglets": true,
    "selcal": "AB-CD",
    "hex_code": "3C6444",
    "msn": "1234",
    "mtow": 78000,
    "mzfw": 62500,
    "mlw": 66000,
    "remarks": "Delivered new.",
    "current_loc": "EDDF"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/airlines/16/aircraft';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {YOUR_AUTH_KEY}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'json' =&gt; [
            'registration' =&gt; 'D-EXAM',
            'manufacturer' =&gt; 'Airbus',
            'model' =&gt; 'A320-200',
            'engine_type' =&gt; 'CFM56',
            'satcom' =&gt; false,
            'winglets' =&gt; true,
            'selcal' =&gt; 'AB-CD',
            'hex_code' =&gt; '3C6444',
            'msn' =&gt; '1234',
            'mtow' =&gt; 78000,
            'mzfw' =&gt; 62500,
            'mlw' =&gt; 66000,
            'remarks' =&gt; 'Delivered new.',
            'current_loc' =&gt; 'EDDF',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-airlines--airline_id--aircraft">
            <blockquote>
            <p>Example response (201):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: 7,
        &quot;registration&quot;: &quot;D-EXAM&quot;,
        &quot;manufacturer&quot;: &quot;Airbus&quot;,
        &quot;model&quot;: &quot;A320-200&quot;,
        &quot;currentLoc&quot;: &quot;EDDF&quot;,
        &quot;engineType&quot;: &quot;CFM56&quot;,
        &quot;satcom&quot;: false,
        &quot;winglets&quot;: true,
        &quot;selcal&quot;: &quot;AB-CD&quot;,
        &quot;hexCode&quot;: &quot;3C6444&quot;,
        &quot;msn&quot;: &quot;1234&quot;,
        &quot;mtow&quot;: 78000,
        &quot;mzfw&quot;: 62500,
        &quot;mlw&quot;: 66000,
        &quot;remarks&quot;: null,
        &quot;status&quot;: &quot;active&quot;,
        &quot;active&quot;: true,
        &quot;retiredAt&quot;: null,
        &quot;retiredReason&quot;: null,
        &quot;inServiceSince&quot;: null,
        &quot;firstFlight&quot;: null,
        &quot;createdAt&quot;: &quot;2026-07-11T12:00:00.000000Z&quot;,
        &quot;updatedAt&quot;: &quot;2026-07-11T12:00:00.000000Z&quot;
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (403):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;This action is unauthorized.&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (422):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;The registration field is required.&quot;,
    &quot;errors&quot;: {
        &quot;registration&quot;: [
            &quot;The registration field is required.&quot;
        ]
    }
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-v1-airlines--airline_id--aircraft" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-airlines--airline_id--aircraft"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-airlines--airline_id--aircraft"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-airlines--airline_id--aircraft" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-airlines--airline_id--aircraft">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-airlines--airline_id--aircraft" data-method="POST"
      data-path="api/v1/airlines/{airline_id}/aircraft"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-airlines--airline_id--aircraft', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-airlines--airline_id--aircraft"
                    onclick="tryItOut('POSTapi-v1-airlines--airline_id--aircraft');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-airlines--airline_id--aircraft"
                    onclick="cancelTryOut('POSTapi-v1-airlines--airline_id--aircraft');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-airlines--airline_id--aircraft"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/airlines/{airline_id}/aircraft</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="POSTapi-v1-airlines--airline_id--aircraft"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-airlines--airline_id--aircraft"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-airlines--airline_id--aircraft"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>airline_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="airline_id"                data-endpoint="POSTapi-v1-airlines--airline_id--aircraft"
               value="16"
               data-component="url">
    <br>
<p>The ID of the airline. Example: <code>16</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>airline</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="airline"                data-endpoint="POSTapi-v1-airlines--airline_id--aircraft"
               value="1"
               data-component="url">
    <br>
<p>The airline ID. Example: <code>1</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>registration</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="registration"                data-endpoint="POSTapi-v1-airlines--airline_id--aircraft"
               value="D-EXAM"
               data-component="body">
    <br>
<p>Tail number, e.g. D-EXAM (max 9 chars, uppercased). Example: <code>D-EXAM</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>manufacturer</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="manufacturer"                data-endpoint="POSTapi-v1-airlines--airline_id--aircraft"
               value="Airbus"
               data-component="body">
    <br>
<p>Airframe manufacturer, max 100 chars. Example: <code>Airbus</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>model</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="model"                data-endpoint="POSTapi-v1-airlines--airline_id--aircraft"
               value="A320-200"
               data-component="body">
    <br>
<p>Aircraft model, max 100 chars. Example: <code>A320-200</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>engine_type</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="engine_type"                data-endpoint="POSTapi-v1-airlines--airline_id--aircraft"
               value="CFM56"
               data-component="body">
    <br>
<p>Engine type, max 100 chars. Example: <code>CFM56</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>satcom</code></b>&nbsp;&nbsp;
<small>boolean</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <label data-endpoint="POSTapi-v1-airlines--airline_id--aircraft" style="display: none">
            <input type="radio" name="satcom"
                   value="true"
                   data-endpoint="POSTapi-v1-airlines--airline_id--aircraft"
                   data-component="body"             >
            <code>true</code>
        </label>
        <label data-endpoint="POSTapi-v1-airlines--airline_id--aircraft" style="display: none">
            <input type="radio" name="satcom"
                   value="false"
                   data-endpoint="POSTapi-v1-airlines--airline_id--aircraft"
                   data-component="body"             >
            <code>false</code>
        </label>
    <br>
<p>Whether the aircraft has SATCOM. Example: <code>false</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>winglets</code></b>&nbsp;&nbsp;
<small>boolean</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <label data-endpoint="POSTapi-v1-airlines--airline_id--aircraft" style="display: none">
            <input type="radio" name="winglets"
                   value="true"
                   data-endpoint="POSTapi-v1-airlines--airline_id--aircraft"
                   data-component="body"             >
            <code>true</code>
        </label>
        <label data-endpoint="POSTapi-v1-airlines--airline_id--aircraft" style="display: none">
            <input type="radio" name="winglets"
                   value="false"
                   data-endpoint="POSTapi-v1-airlines--airline_id--aircraft"
                   data-component="body"             >
            <code>false</code>
        </label>
    <br>
<p>Whether the aircraft has winglets. Example: <code>true</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>selcal</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="selcal"                data-endpoint="POSTapi-v1-airlines--airline_id--aircraft"
               value="AB-CD"
               data-component="body">
    <br>
<p>SELCAL code, format XX-XX. Example: <code>AB-CD</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>hex_code</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="hex_code"                data-endpoint="POSTapi-v1-airlines--airline_id--aircraft"
               value="3C6444"
               data-component="body">
    <br>
<p>Mode-S hex code, 6 hex chars. Example: <code>3C6444</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>msn</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="msn"                data-endpoint="POSTapi-v1-airlines--airline_id--aircraft"
               value="1234"
               data-component="body">
    <br>
<p>Manufacturer serial number, 1-6 digits. Example: <code>1234</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>mtow</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="mtow"                data-endpoint="POSTapi-v1-airlines--airline_id--aircraft"
               value="78000"
               data-component="body">
    <br>
<p>Max take-off weight (kg), 0-1000000. Example: <code>78000</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>mzfw</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="mzfw"                data-endpoint="POSTapi-v1-airlines--airline_id--aircraft"
               value="62500"
               data-component="body">
    <br>
<p>Max zero-fuel weight (kg), 0-1000000. Example: <code>62500</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>mlw</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="mlw"                data-endpoint="POSTapi-v1-airlines--airline_id--aircraft"
               value="66000"
               data-component="body">
    <br>
<p>Max landing weight (kg), 0-1000000. Example: <code>66000</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>remarks</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="remarks"                data-endpoint="POSTapi-v1-airlines--airline_id--aircraft"
               value="Delivered new."
               data-component="body">
    <br>
<p>Free-text remarks, max 1000 chars. Example: <code>Delivered new.</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>current_loc</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="current_loc"                data-endpoint="POSTapi-v1-airlines--airline_id--aircraft"
               value="EDDF"
               data-component="body">
    <br>
<p>ICAO code of the aircraft's current location (must exist in airports). Example: <code>EDDF</code></p>
        </div>
        </form>

                    <h2 id="aircraft-GETapi-v1-airlines--airline_id--aircraft--id-">Show an aircraft</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>The scoped route binding 404s any aircraft that does not belong to {airline}.</p>

<span id="example-requests-GETapi-v1-airlines--airline_id--aircraft--id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/airlines/16/aircraft/16" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/airlines/16/aircraft/16"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/airlines/16/aircraft/16';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {YOUR_AUTH_KEY}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-airlines--airline_id--aircraft--id-">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: 7,
        &quot;registration&quot;: &quot;D-EXAM&quot;,
        &quot;manufacturer&quot;: &quot;Airbus&quot;,
        &quot;model&quot;: &quot;A320-200&quot;,
        &quot;currentLoc&quot;: &quot;EDDF&quot;,
        &quot;engineType&quot;: &quot;CFM56&quot;,
        &quot;satcom&quot;: false,
        &quot;winglets&quot;: true,
        &quot;selcal&quot;: &quot;AB-CD&quot;,
        &quot;hexCode&quot;: &quot;3C6444&quot;,
        &quot;msn&quot;: &quot;1234&quot;,
        &quot;mtow&quot;: 78000,
        &quot;mzfw&quot;: 62500,
        &quot;mlw&quot;: 66000,
        &quot;remarks&quot;: null,
        &quot;status&quot;: &quot;active&quot;,
        &quot;active&quot;: true,
        &quot;retiredAt&quot;: null,
        &quot;retiredReason&quot;: null,
        &quot;inServiceSince&quot;: &quot;2020-01-01&quot;,
        &quot;firstFlight&quot;: &quot;2019-11-15&quot;,
        &quot;createdAt&quot;: &quot;2026-01-01T00:00:00.000000Z&quot;,
        &quot;updatedAt&quot;: &quot;2026-01-01T00:00:00.000000Z&quot;
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (403):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;You are not a member of this airline.&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (404):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;No query results for model [App\\Models\\Aircraft] 999&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-airlines--airline_id--aircraft--id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-airlines--airline_id--aircraft--id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-airlines--airline_id--aircraft--id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-airlines--airline_id--aircraft--id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-airlines--airline_id--aircraft--id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-airlines--airline_id--aircraft--id-" data-method="GET"
      data-path="api/v1/airlines/{airline_id}/aircraft/{id}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-airlines--airline_id--aircraft--id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-airlines--airline_id--aircraft--id-"
                    onclick="tryItOut('GETapi-v1-airlines--airline_id--aircraft--id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-airlines--airline_id--aircraft--id-"
                    onclick="cancelTryOut('GETapi-v1-airlines--airline_id--aircraft--id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-airlines--airline_id--aircraft--id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/airlines/{airline_id}/aircraft/{id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-v1-airlines--airline_id--aircraft--id-"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-airlines--airline_id--aircraft--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-airlines--airline_id--aircraft--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>airline_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="airline_id"                data-endpoint="GETapi-v1-airlines--airline_id--aircraft--id-"
               value="16"
               data-component="url">
    <br>
<p>The ID of the airline. Example: <code>16</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="id"                data-endpoint="GETapi-v1-airlines--airline_id--aircraft--id-"
               value="16"
               data-component="url">
    <br>
<p>The ID of the aircraft. Example: <code>16</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>airline</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="airline"                data-endpoint="GETapi-v1-airlines--airline_id--aircraft--id-"
               value="1"
               data-component="url">
    <br>
<p>The airline ID. Example: <code>1</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>aircraft</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="aircraft"                data-endpoint="GETapi-v1-airlines--airline_id--aircraft--id-"
               value="7"
               data-component="url">
    <br>
<p>The aircraft ID (must belong to the airline). Example: <code>7</code></p>
            </div>
                    </form>

                <h1 id="airlines">Airlines</h1>

    <p>Virtual airlines. Aircraft and flights are nested resources of an airline.</p>

                                <h2 id="airlines-GETapi-v1-airlines">List airlines</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-GETapi-v1-airlines">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/airlines?members_only=1" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/airlines"
);

const params = {
    "members_only": "1",
};
Object.keys(params)
    .forEach(key =&gt; url.searchParams.append(key, params[key]));

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/airlines';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {YOUR_AUTH_KEY}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'query' =&gt; [
            'members_only' =&gt; '1',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-airlines">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: [
        {
            &quot;id&quot;: 1,
            &quot;name&quot;: &quot;Example Virtual Airlines&quot;,
            &quot;prefix&quot;: &quot;EV&quot;,
            &quot;icaoCallsign&quot;: &quot;EVA&quot;,
            &quot;atcCallsign&quot;: &quot;EXAMPLE&quot;,
            &quot;unitIsLbs&quot;: false,
            &quot;requirePirepReview&quot;: true,
            &quot;locationContinuity&quot;: false,
            &quot;createdAt&quot;: &quot;2026-01-01T00:00:00.000000Z&quot;,
            &quot;updatedAt&quot;: &quot;2026-01-01T00:00:00.000000Z&quot;
        }
    ]
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-airlines" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-airlines"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-airlines"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-airlines" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-airlines">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-airlines" data-method="GET"
      data-path="api/v1/airlines"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-airlines', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-airlines"
                    onclick="tryItOut('GETapi-v1-airlines');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-airlines"
                    onclick="cancelTryOut('GETapi-v1-airlines');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-airlines"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/airlines</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-v1-airlines"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-airlines"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-airlines"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Query Parameters</b></h4>
                                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>members_only</code></b>&nbsp;&nbsp;
<small>boolean</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <label data-endpoint="GETapi-v1-airlines" style="display: none">
            <input type="radio" name="members_only"
                   value="1"
                   data-endpoint="GETapi-v1-airlines"
                   data-component="query"             >
            <code>true</code>
        </label>
        <label data-endpoint="GETapi-v1-airlines" style="display: none">
            <input type="radio" name="members_only"
                   value="0"
                   data-endpoint="GETapi-v1-airlines"
                   data-component="query"             >
            <code>false</code>
        </label>
    <br>
<p>Only return airlines the token owner belongs to. Defaults to all airlines. Example: <code>true</code></p>
            </div>
                </form>

                    <h2 id="airlines-POSTapi-v1-airlines">Found a new airline</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Requires the "add airlines" permission (enforced by StoreAirlineRequest).</p>

<span id="example-requests-POSTapi-v1-airlines">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/airlines" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"name\": \"Example Virtual Airlines\",
    \"prefix\": \"EV\",
    \"icao_callsign\": \"EVA\",
    \"atc_callsign\": \"EXAMPLE\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/airlines"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "Example Virtual Airlines",
    "prefix": "EV",
    "icao_callsign": "EVA",
    "atc_callsign": "EXAMPLE"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/airlines';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {YOUR_AUTH_KEY}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'json' =&gt; [
            'name' =&gt; 'Example Virtual Airlines',
            'prefix' =&gt; 'EV',
            'icao_callsign' =&gt; 'EVA',
            'atc_callsign' =&gt; 'EXAMPLE',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-airlines">
            <blockquote>
            <p>Example response (201):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: 2,
        &quot;name&quot;: &quot;Example Virtual Airlines&quot;,
        &quot;prefix&quot;: &quot;EV&quot;,
        &quot;icaoCallsign&quot;: &quot;EVA&quot;,
        &quot;atcCallsign&quot;: &quot;EXAMPLE&quot;,
        &quot;unitIsLbs&quot;: false,
        &quot;requirePirepReview&quot;: true,
        &quot;locationContinuity&quot;: false,
        &quot;createdAt&quot;: &quot;2026-01-01T00:00:00.000000Z&quot;,
        &quot;updatedAt&quot;: &quot;2026-01-01T00:00:00.000000Z&quot;
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (403):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;This action is unauthorized.&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (422):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;The prefix has already been taken.&quot;,
    &quot;errors&quot;: {
        &quot;prefix&quot;: [
            &quot;The prefix has already been taken.&quot;
        ]
    }
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-v1-airlines" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-airlines"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-airlines"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-airlines" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-airlines">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-airlines" data-method="POST"
      data-path="api/v1/airlines"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-airlines', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-airlines"
                    onclick="tryItOut('POSTapi-v1-airlines');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-airlines"
                    onclick="cancelTryOut('POSTapi-v1-airlines');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-airlines"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/airlines</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="POSTapi-v1-airlines"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-airlines"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-airlines"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="name"                data-endpoint="POSTapi-v1-airlines"
               value="Example Virtual Airlines"
               data-component="body">
    <br>
<p>Display name, max 50 chars, unique. Example: <code>Example Virtual Airlines</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>prefix</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="prefix"                data-endpoint="POSTapi-v1-airlines"
               value="EV"
               data-component="body">
    <br>
<p>Two-letter IATA-style prefix, unique, uppercased. Example: <code>EV</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>icao_callsign</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="icao_callsign"                data-endpoint="POSTapi-v1-airlines"
               value="EVA"
               data-component="body">
    <br>
<p>Three-letter ICAO callsign, unique, uppercased. Example: <code>EVA</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>atc_callsign</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="atc_callsign"                data-endpoint="POSTapi-v1-airlines"
               value="EXAMPLE"
               data-component="body">
    <br>
<p>Spoken ATC callsign, max 25 chars, unique. Example: <code>EXAMPLE</code></p>
        </div>
        </form>

                    <h2 id="airlines-GETapi-v1-airlines--id-">Show an airline</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-GETapi-v1-airlines--id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/airlines/16" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/airlines/16"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/airlines/16';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {YOUR_AUTH_KEY}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-airlines--id-">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: 1,
        &quot;name&quot;: &quot;Example Virtual Airlines&quot;,
        &quot;prefix&quot;: &quot;EV&quot;,
        &quot;icaoCallsign&quot;: &quot;EVA&quot;,
        &quot;atcCallsign&quot;: &quot;EXAMPLE&quot;,
        &quot;unitIsLbs&quot;: false,
        &quot;requirePirepReview&quot;: true,
        &quot;locationContinuity&quot;: false,
        &quot;createdAt&quot;: &quot;2026-01-01T00:00:00.000000Z&quot;,
        &quot;updatedAt&quot;: &quot;2026-01-01T00:00:00.000000Z&quot;
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (404):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;No query results for model [App\\Models\\Airline] 999&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-airlines--id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-airlines--id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-airlines--id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-airlines--id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-airlines--id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-airlines--id-" data-method="GET"
      data-path="api/v1/airlines/{id}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-airlines--id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-airlines--id-"
                    onclick="tryItOut('GETapi-v1-airlines--id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-airlines--id-"
                    onclick="cancelTryOut('GETapi-v1-airlines--id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-airlines--id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/airlines/{id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-v1-airlines--id-"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-airlines--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-airlines--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="id"                data-endpoint="GETapi-v1-airlines--id-"
               value="16"
               data-component="url">
    <br>
<p>The ID of the airline. Example: <code>16</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>airline</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="airline"                data-endpoint="GETapi-v1-airlines--id-"
               value="1"
               data-component="url">
    <br>
<p>The airline ID. Example: <code>1</code></p>
            </div>
                    </form>

                <h1 id="flights">Flights</h1>

    <p>PIREPs (pilot reports / flight logs) for an airline. Filing, listing, and -
for reviewers - the review queue plus accept/reject.</p>

                                <h2 id="flights-GETapi-v1-airlines--airline_id--flights">List my flights</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>List the authenticated pilot's own flights for a specific airline.</p>

<span id="example-requests-GETapi-v1-airlines--airline_id--flights">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/airlines/16/flights?status_id=2" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/airlines/16/flights"
);

const params = {
    "status_id": "2",
};
Object.keys(params)
    .forEach(key =&gt; url.searchParams.append(key, params[key]));

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/airlines/16/flights';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {YOUR_AUTH_KEY}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'query' =&gt; [
            'status_id' =&gt; '2',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-airlines--airline_id--flights">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: [
        {
            &quot;id&quot;: 42,
            &quot;callsign&quot;: &quot;421&quot;,
            &quot;flightNumber&quot;: &quot;421&quot;,
            &quot;fullFlightNumber&quot;: &quot;EV421&quot;,
            &quot;fullIcaoCallsign&quot;: &quot;EVA421&quot;,
            &quot;departureIcao&quot;: &quot;EDDF&quot;,
            &quot;arrivalIcao&quot;: &quot;EGLL&quot;,
            &quot;cruiseAltitude&quot;: 36000,
            &quot;blockOff&quot;: &quot;2026-07-11 10:00:00&quot;,
            &quot;blockOn&quot;: &quot;2026-07-11 11:30:00&quot;,
            &quot;duration&quot;: &quot;01:30&quot;,
            &quot;burnedFuel&quot;: 4200,
            &quot;route&quot;: &quot;SOVAT UL610 KONAN&quot;,
            &quot;onlineNetwork&quot;: 1,
            &quot;status&quot;: {
                &quot;id&quot;: 2,
                &quot;name&quot;: &quot;Accepted&quot;
            },
            &quot;remarks&quot;: null,
            &quot;rejectionRemarks&quot;: null,
            &quot;createdAt&quot;: &quot;2026-07-11T11:35:00.000000Z&quot;,
            &quot;updatedAt&quot;: &quot;2026-07-11T11:40:00.000000Z&quot;
        }
    ]
}</code>
 </pre>
            <blockquote>
            <p>Example response (403):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;You are not a member of this airline.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-airlines--airline_id--flights" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-airlines--airline_id--flights"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-airlines--airline_id--flights"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-airlines--airline_id--flights" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-airlines--airline_id--flights">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-airlines--airline_id--flights" data-method="GET"
      data-path="api/v1/airlines/{airline_id}/flights"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-airlines--airline_id--flights', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-airlines--airline_id--flights"
                    onclick="tryItOut('GETapi-v1-airlines--airline_id--flights');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-airlines--airline_id--flights"
                    onclick="cancelTryOut('GETapi-v1-airlines--airline_id--flights');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-airlines--airline_id--flights"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/airlines/{airline_id}/flights</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-v1-airlines--airline_id--flights"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-airlines--airline_id--flights"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-airlines--airline_id--flights"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>airline_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="airline_id"                data-endpoint="GETapi-v1-airlines--airline_id--flights"
               value="16"
               data-component="url">
    <br>
<p>The ID of the airline. Example: <code>16</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>airline</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="airline"                data-endpoint="GETapi-v1-airlines--airline_id--flights"
               value="1"
               data-component="url">
    <br>
<p>The airline ID. Example: <code>1</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>Query Parameters</b></h4>
                                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>status_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="status_id"                data-endpoint="GETapi-v1-airlines--airline_id--flights"
               value="2"
               data-component="query">
    <br>
<p>Filter by flight status: 1 = Pending, 2 = Accepted, 3 = Rejected. Example: <code>2</code></p>
            </div>
                </form>

                    <h2 id="flights-POSTapi-v1-airlines--airline_id--flights">File a PIREP</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Submit a new PIREP for the airline. Membership, airport/aircraft
existence and the location-continuity rule are enforced by
StoreFlightRequest. If the airline requires review the flight is created
Pending (status 1) and reviewers are notified; otherwise it is Accepted
(status 2) immediately.</p>

<span id="example-requests-POSTapi-v1-airlines--airline_id--flights">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/airlines/16/flights" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"flightnumber\": 421,
    \"departure_icao\": \"EDDF\",
    \"arrival_icao\": \"EGLL\",
    \"aircraft_id\": 7,
    \"callsign\": \"421\",
    \"crzalt\": 36000,
    \"blockoff\": \"2026-07-11 10:00:00\",
    \"blockon\": \"2026-07-11 11:30:00\",
    \"burned_fuel\": 4200,
    \"route\": \"SOVAT UL610 KONAN\",
    \"online_network_id\": 1,
    \"remarks\": \"Smooth flight.\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/airlines/16/flights"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "flightnumber": 421,
    "departure_icao": "EDDF",
    "arrival_icao": "EGLL",
    "aircraft_id": 7,
    "callsign": "421",
    "crzalt": 36000,
    "blockoff": "2026-07-11 10:00:00",
    "blockon": "2026-07-11 11:30:00",
    "burned_fuel": 4200,
    "route": "SOVAT UL610 KONAN",
    "online_network_id": 1,
    "remarks": "Smooth flight."
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/airlines/16/flights';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {YOUR_AUTH_KEY}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'json' =&gt; [
            'flightnumber' =&gt; 421,
            'departure_icao' =&gt; 'EDDF',
            'arrival_icao' =&gt; 'EGLL',
            'aircraft_id' =&gt; 7,
            'callsign' =&gt; '421',
            'crzalt' =&gt; 36000,
            'blockoff' =&gt; '2026-07-11 10:00:00',
            'blockon' =&gt; '2026-07-11 11:30:00',
            'burned_fuel' =&gt; 4200.0,
            'route' =&gt; 'SOVAT UL610 KONAN',
            'online_network_id' =&gt; 1,
            'remarks' =&gt; 'Smooth flight.',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-airlines--airline_id--flights">
            <blockquote>
            <p>Example response (201):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: 42,
        &quot;callsign&quot;: &quot;421&quot;,
        &quot;flightNumber&quot;: &quot;421&quot;,
        &quot;fullFlightNumber&quot;: &quot;EV421&quot;,
        &quot;fullIcaoCallsign&quot;: &quot;EVA421&quot;,
        &quot;departureIcao&quot;: &quot;EDDF&quot;,
        &quot;arrivalIcao&quot;: &quot;EGLL&quot;,
        &quot;cruiseAltitude&quot;: 36000,
        &quot;blockOff&quot;: &quot;2026-07-11 10:00:00&quot;,
        &quot;blockOn&quot;: &quot;2026-07-11 11:30:00&quot;,
        &quot;duration&quot;: &quot;01:30&quot;,
        &quot;burnedFuel&quot;: 4200,
        &quot;route&quot;: &quot;SOVAT UL610 KONAN&quot;,
        &quot;onlineNetwork&quot;: 1,
        &quot;status&quot;: {
            &quot;id&quot;: 1,
            &quot;name&quot;: &quot;Pending&quot;
        },
        &quot;remarks&quot;: null,
        &quot;rejectionRemarks&quot;: null,
        &quot;createdAt&quot;: &quot;2026-07-11T11:35:00.000000Z&quot;,
        &quot;updatedAt&quot;: &quot;2026-07-11T11:35:00.000000Z&quot;
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (403):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;This action is unauthorized.&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (422):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;The departure icao field is required.&quot;,
    &quot;errors&quot;: {
        &quot;departure_icao&quot;: [
            &quot;The departure icao field is required.&quot;
        ]
    }
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-v1-airlines--airline_id--flights" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-airlines--airline_id--flights"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-airlines--airline_id--flights"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-airlines--airline_id--flights" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-airlines--airline_id--flights">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-airlines--airline_id--flights" data-method="POST"
      data-path="api/v1/airlines/{airline_id}/flights"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-airlines--airline_id--flights', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-airlines--airline_id--flights"
                    onclick="tryItOut('POSTapi-v1-airlines--airline_id--flights');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-airlines--airline_id--flights"
                    onclick="cancelTryOut('POSTapi-v1-airlines--airline_id--flights');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-airlines--airline_id--flights"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/airlines/{airline_id}/flights</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="POSTapi-v1-airlines--airline_id--flights"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-airlines--airline_id--flights"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-airlines--airline_id--flights"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>airline_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="airline_id"                data-endpoint="POSTapi-v1-airlines--airline_id--flights"
               value="16"
               data-component="url">
    <br>
<p>The ID of the airline. Example: <code>16</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>airline</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="airline"                data-endpoint="POSTapi-v1-airlines--airline_id--flights"
               value="1"
               data-component="url">
    <br>
<p>The airline ID. Example: <code>1</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>flightnumber</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="flightnumber"                data-endpoint="POSTapi-v1-airlines--airline_id--flights"
               value="421"
               data-component="body">
    <br>
<p>Numeric flight number, 1-4 digits. Example: <code>421</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>departure_icao</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="departure_icao"                data-endpoint="POSTapi-v1-airlines--airline_id--flights"
               value="EDDF"
               data-component="body">
    <br>
<p>Departure airport ICAO (must exist; uppercased). Example: <code>EDDF</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>arrival_icao</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="arrival_icao"                data-endpoint="POSTapi-v1-airlines--airline_id--flights"
               value="EGLL"
               data-component="body">
    <br>
<p>Arrival airport ICAO (must exist; uppercased). Example: <code>EGLL</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>aircraft_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="aircraft_id"                data-endpoint="POSTapi-v1-airlines--airline_id--flights"
               value="7"
               data-component="body">
    <br>
<p>ID of an active aircraft owned by the airline. Example: <code>7</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>callsign</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="callsign"                data-endpoint="POSTapi-v1-airlines--airline_id--flights"
               value="421"
               data-component="body">
    <br>
<p>Radio callsign, 1-4 digits optionally followed by up to 2 letters. Example: <code>421</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>crzalt</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="crzalt"                data-endpoint="POSTapi-v1-airlines--airline_id--flights"
               value="36000"
               data-component="body">
    <br>
<p>Cruise altitude in feet, max 50000. Example: <code>36000</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>blockoff</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="blockoff"                data-endpoint="POSTapi-v1-airlines--airline_id--flights"
               value="2026-07-11 10:00:00"
               data-component="body">
    <br>
<p>Block-off time (UTC), format Y-m-d H:i:s. Example: <code>2026-07-11 10:00:00</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>blockon</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="blockon"                data-endpoint="POSTapi-v1-airlines--airline_id--flights"
               value="2026-07-11 11:30:00"
               data-component="body">
    <br>
<p>Block-on time (UTC), format Y-m-d H:i:s. Example: <code>2026-07-11 11:30:00</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>burned_fuel</code></b>&nbsp;&nbsp;
<small>number</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="burned_fuel"                data-endpoint="POSTapi-v1-airlines--airline_id--flights"
               value="4200"
               data-component="body">
    <br>
<p>Fuel burned. Example: <code>4200</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>route</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="route"                data-endpoint="POSTapi-v1-airlines--airline_id--flights"
               value="SOVAT UL610 KONAN"
               data-component="body">
    <br>
<p>Filed route string. Example: <code>SOVAT UL610 KONAN</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>online_network_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="online_network_id"                data-endpoint="POSTapi-v1-airlines--airline_id--flights"
               value="1"
               data-component="body">
    <br>
<p>Online network ID (must exist in online_networks). Example: <code>1</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>remarks</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="remarks"                data-endpoint="POSTapi-v1-airlines--airline_id--flights"
               value="Smooth flight."
               data-component="body">
    <br>
<p>Optional remarks (letters, digits, spaces, . , -). Example: <code>Smooth flight.</code></p>
        </div>
        </form>

                    <h2 id="flights-GETapi-v1-airlines--airline_id--flights-review">List the review queue</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>List pending PIREPs (status 1) for an airline. Requires the per-airline
Dispatcher or Manager role.</p>

<span id="example-requests-GETapi-v1-airlines--airline_id--flights-review">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/airlines/16/flights/review" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/airlines/16/flights/review"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/airlines/16/flights/review';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {YOUR_AUTH_KEY}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-airlines--airline_id--flights-review">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: [
        {
            &quot;id&quot;: 43,
            &quot;callsign&quot;: &quot;422&quot;,
            &quot;flightNumber&quot;: &quot;422&quot;,
            &quot;fullFlightNumber&quot;: &quot;EV422&quot;,
            &quot;fullIcaoCallsign&quot;: &quot;EVA422&quot;,
            &quot;departureIcao&quot;: &quot;EGLL&quot;,
            &quot;arrivalIcao&quot;: &quot;EDDF&quot;,
            &quot;cruiseAltitude&quot;: 37000,
            &quot;blockOff&quot;: &quot;2026-07-11 13:00:00&quot;,
            &quot;blockOn&quot;: &quot;2026-07-11 14:25:00&quot;,
            &quot;duration&quot;: &quot;01:25&quot;,
            &quot;burnedFuel&quot;: 4100,
            &quot;route&quot;: &quot;DET L6 KONAN&quot;,
            &quot;onlineNetwork&quot;: 1,
            &quot;status&quot;: {
                &quot;id&quot;: 1,
                &quot;name&quot;: &quot;Pending&quot;
            },
            &quot;remarks&quot;: null,
            &quot;rejectionRemarks&quot;: null,
            &quot;createdAt&quot;: &quot;2026-07-11T14:30:00.000000Z&quot;,
            &quot;updatedAt&quot;: &quot;2026-07-11T14:30:00.000000Z&quot;
        }
    ]
}</code>
 </pre>
            <blockquote>
            <p>Example response (403):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;You do not have permission to review flights for this airline.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-airlines--airline_id--flights-review" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-airlines--airline_id--flights-review"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-airlines--airline_id--flights-review"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-airlines--airline_id--flights-review" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-airlines--airline_id--flights-review">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-airlines--airline_id--flights-review" data-method="GET"
      data-path="api/v1/airlines/{airline_id}/flights/review"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-airlines--airline_id--flights-review', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-airlines--airline_id--flights-review"
                    onclick="tryItOut('GETapi-v1-airlines--airline_id--flights-review');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-airlines--airline_id--flights-review"
                    onclick="cancelTryOut('GETapi-v1-airlines--airline_id--flights-review');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-airlines--airline_id--flights-review"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/airlines/{airline_id}/flights/review</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-v1-airlines--airline_id--flights-review"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-airlines--airline_id--flights-review"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-airlines--airline_id--flights-review"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>airline_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="airline_id"                data-endpoint="GETapi-v1-airlines--airline_id--flights-review"
               value="16"
               data-component="url">
    <br>
<p>The ID of the airline. Example: <code>16</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>airline</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="airline"                data-endpoint="GETapi-v1-airlines--airline_id--flights-review"
               value="1"
               data-component="url">
    <br>
<p>The airline ID. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="flights-POSTapi-v1-flights--flight_id--accept">Accept a PIREP</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Mark a pending PIREP as Accepted (status 2) and notify the pilot.
Requires the "review flight" permission for the flight's airline.</p>

<span id="example-requests-POSTapi-v1-flights--flight_id--accept">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/flights/16/accept" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/flights/16/accept"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/flights/16/accept';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {YOUR_AUTH_KEY}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-flights--flight_id--accept">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: 43,
        &quot;callsign&quot;: &quot;422&quot;,
        &quot;flightNumber&quot;: &quot;422&quot;,
        &quot;fullFlightNumber&quot;: &quot;EV422&quot;,
        &quot;departureIcao&quot;: &quot;EGLL&quot;,
        &quot;arrivalIcao&quot;: &quot;EDDF&quot;,
        &quot;status&quot;: {
            &quot;id&quot;: 2,
            &quot;name&quot;: &quot;Accepted&quot;
        },
        &quot;rejectionRemarks&quot;: null
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (403):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;This action is unauthorized.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-v1-flights--flight_id--accept" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-flights--flight_id--accept"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-flights--flight_id--accept"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-flights--flight_id--accept" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-flights--flight_id--accept">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-flights--flight_id--accept" data-method="POST"
      data-path="api/v1/flights/{flight_id}/accept"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-flights--flight_id--accept', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-flights--flight_id--accept"
                    onclick="tryItOut('POSTapi-v1-flights--flight_id--accept');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-flights--flight_id--accept"
                    onclick="cancelTryOut('POSTapi-v1-flights--flight_id--accept');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-flights--flight_id--accept"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/flights/{flight_id}/accept</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="POSTapi-v1-flights--flight_id--accept"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-flights--flight_id--accept"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-flights--flight_id--accept"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>flight_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="flight_id"                data-endpoint="POSTapi-v1-flights--flight_id--accept"
               value="16"
               data-component="url">
    <br>
<p>The ID of the flight. Example: <code>16</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>flight</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="flight"                data-endpoint="POSTapi-v1-flights--flight_id--accept"
               value="43"
               data-component="url">
    <br>
<p>The flight ID. Example: <code>43</code></p>
            </div>
                    </form>

                    <h2 id="flights-POSTapi-v1-flights--flight_id--reject">Reject a PIREP</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Mark a PIREP as Rejected (status 3) and notify the pilot. If location
continuity is on and the flight was still pending, the aircraft is moved
back to the flight's departure. Requires the "review flight" permission.</p>

<span id="example-requests-POSTapi-v1-flights--flight_id--reject">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/flights/16/reject" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"rejection_remarks\": \"Cruise altitude above aircraft ceiling.\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/flights/16/reject"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "rejection_remarks": "Cruise altitude above aircraft ceiling."
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/flights/16/reject';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {YOUR_AUTH_KEY}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'json' =&gt; [
            'rejection_remarks' =&gt; 'Cruise altitude above aircraft ceiling.',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-flights--flight_id--reject">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: 43,
        &quot;callsign&quot;: &quot;422&quot;,
        &quot;flightNumber&quot;: &quot;422&quot;,
        &quot;fullFlightNumber&quot;: &quot;EV422&quot;,
        &quot;departureIcao&quot;: &quot;EGLL&quot;,
        &quot;arrivalIcao&quot;: &quot;EDDF&quot;,
        &quot;status&quot;: {
            &quot;id&quot;: 3,
            &quot;name&quot;: &quot;Rejected&quot;
        },
        &quot;rejectionRemarks&quot;: &quot;Cruise altitude above aircraft ceiling.&quot;
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (403):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;This action is unauthorized.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-v1-flights--flight_id--reject" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-flights--flight_id--reject"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-flights--flight_id--reject"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-flights--flight_id--reject" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-flights--flight_id--reject">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-flights--flight_id--reject" data-method="POST"
      data-path="api/v1/flights/{flight_id}/reject"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-flights--flight_id--reject', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-flights--flight_id--reject"
                    onclick="tryItOut('POSTapi-v1-flights--flight_id--reject');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-flights--flight_id--reject"
                    onclick="cancelTryOut('POSTapi-v1-flights--flight_id--reject');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-flights--flight_id--reject"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/flights/{flight_id}/reject</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="POSTapi-v1-flights--flight_id--reject"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-flights--flight_id--reject"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-flights--flight_id--reject"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>flight_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="flight_id"                data-endpoint="POSTapi-v1-flights--flight_id--reject"
               value="16"
               data-component="url">
    <br>
<p>The ID of the flight. Example: <code>16</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>flight</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="flight"                data-endpoint="POSTapi-v1-flights--flight_id--reject"
               value="43"
               data-component="url">
    <br>
<p>The flight ID. Example: <code>43</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>rejection_remarks</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="rejection_remarks"                data-endpoint="POSTapi-v1-flights--flight_id--reject"
               value="Cruise altitude above aircraft ceiling."
               data-component="body">
    <br>
<p>Reason shown to the pilot. Example: <code>Cruise altitude above aircraft ceiling.</code></p>
        </div>
        </form>

                <h1 id="instance">Instance</h1>

    <p>Public metadata about this YAAMS instance.</p>

                                <h2 id="instance-GETapi-v1-info">Instance info</h2>

<p>
</p>

<p>Public instance metadata for API clients (e.g. ACARS "connect to your VA"
setup screens). Unauthenticated by design - exposes only what the public
landing page already shows.</p>

<span id="example-requests-GETapi-v1-info">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/info" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/info"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/info';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-info">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;name&quot;: &quot;Example Virtual Airlines&quot;,
        &quot;version&quot;: &quot;1.1.0&quot;,
        &quot;apiVersion&quot;: &quot;v1&quot;,
        &quot;supportEmail&quot;: &quot;ops@example.com&quot;,
        &quot;features&quot;: {
            &quot;registration&quot;: true,
            &quot;userAirlineCreation&quot;: false
        }
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-info" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-info"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-info"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-info" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-info">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-info" data-method="GET"
      data-path="api/v1/info"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-info', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-info"
                    onclick="tryItOut('GETapi-v1-info');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-info"
                    onclick="cancelTryOut('GETapi-v1-info');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-info"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/info</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-info"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-info"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

            

        
    </div>
    <div class="dark-box">
                    <div class="lang-selector">
                                                        <button type="button" class="lang-button" data-language-name="bash">bash</button>
                                                        <button type="button" class="lang-button" data-language-name="javascript">javascript</button>
                                                        <button type="button" class="lang-button" data-language-name="php">php</button>
                            </div>
            </div>
</div>
</body>
</html>
