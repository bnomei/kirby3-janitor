<?php
http_response_code(503); // Service Unavailable
/*
The Web server (running the Web site) is currently unable
to handle the HTTP request due to a temporary overloading or
maintenance of the server. The implication is that this is
a temporary condition which will be alleviated after some delay.
*/
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Maintenance</title>
  <style>
    body {
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";
      padding: 10%;
      text-align: center;
      line-height: 1.5em;
    }
    a {
      color: inherit;
    }
    a:hover,
    a:focus {
      color: #000;
    }
    p {
      max-width: 30em;
      margin: 0 auto;
    }
    .notice {
      font-weight: bold;
    }
    .admin-advice {
      font-size: .8em;
      font-style: italic;
      color: #999;
      padding-top: 3rem;
    }
  </style>
</head>
<body>
  <p class="notice">
    <?= t('janitor.maintenance.notice', 'This page is currently in maintenance.') ?>
  </p>
</body>
</html>
