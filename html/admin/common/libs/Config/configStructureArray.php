<?php
$configStructureArray = [
  "ROOTURL" => [
    "form" => [
      "type" => "url", // The type of field this is (secret, text, number, url, select)
      "default" => function () { // Default value for the text box
        return "https://adam-rms.com";
      },
      "name" => "Root URL", // The name of the field to be shown to the user
      "description" => "The root URL of the site, this is used for redirects and links in emails", // A description of the field to be shown to the user
      "group" => "General", // The group this field belongs to
      "required" => true, // Is this value required? Or can it be left blank
      "maxlength" => 255, // This is the maximum length of the string (if of string type)
      "minlength" => 10, // This is the minimum length of the string (if of string type)
      "options" => [], // An array of options that can be selected for a select dropdown
      "verifyMatch" => function ($value, $options) { // A filter which takes the value provided by the user, the options array in the config, and returns an array with the following keys: valid, value, error
        $checkedValue = filter_var($value, FILTER_VALIDATE_URL);
        if ($checkedValue !== false) return ["valid" => true, "value" => $checkedValue, "error" => null];
        else return ["valid" => false, "value" => null, "error" => "Invalid URL"];
      },
    ],
    "specialRequest" => false, // Should this value by downloaded for every single pageload? True is reccomended for values that are used by every single page or are used by twig, and so should be taken from the database in a bulk call to improve performance
    "default" => false, // Default value if one is not in the database (false to fail if not in database)
    "envFallback" => "bCMS__ROOTURL", // If the value isn't in the database, use this environment variable (false to not use one)
  ],
  "PROJECT_NAME" => [
    "form" => [
      "type" => "text",
      "default" => function () {
        return "AdamRMS";
      },
      "name" => "Whitelabel project name override",
      "description" => "What do you call AdamRMS within your organisation?",
      "group" => "Customisation",
      "required" => false,
      "maxlength" => 20,
      "minlength" => 2,
      "options" => [],
      "verifyMatch" => function ($value, $options) {
        $checkedValue = filter_var($value, FILTER_VALIDATE_REGEXP, ["options" => ["regexp" => "/^[a-zA-Z0-9_ ]+$/"]]);
        if ($checkedValue) return ["valid" => true, "value" => $checkedValue, "error" => null];
        else return ["valid" => false, "value" => null, "error" => "Invalid name"];
      }
    ],
    "specialRequest" => false,
    "default" => "AdamRMS",
    "envFallback" => false,
  ],
  "EMAILS_ENABLED" => [
    "form" => [
      "type" => "select",
      "default" => function () {
        return "Disabled";
      },
      "name" => "Email sending",
      "group" => "Email",
      "description" => "Should AdamRMS send emails to users? If this is enabled then a provider must be setup below.",
      "required" => true,
      "maxlength" => 255,
      "minlength" => 5,
      "options" => ["Enabled", "Disabled"],
      "verifyMatch" => function ($value, $options) {
        return ["valid" => in_array($value, $options), "value" => $value, "error" => in_array($value, $options) ? '' : "Invalid option selected"];
      }
    ],
    "specialRequest" => false,
    "default" => "Disabled",
    "envFallback" => false,
  ],
  "EMAILS_PROVIDER" => [
    "form" => [
      "type" => "select",
      "default" => function () {
        return "Sendgrid";
      },
      "name" => "Enable email sending",
      "group" => "Email",
      "description" => "How should AdamRMS send emails to users? This option is ignored if email sending is disabled.",
      "required" => true,
      "maxlength" => 255,
      "minlength" => 5,
      "options" => ["Sendgrid"],
      "verifyMatch" => function ($value, $options) {
        return ["valid" => in_array($value, $options), "value" => $value, "error" => in_array($value, $options) ? '' : "Invalid option selected"];
      }
    ],
    "specialRequest" => true,
    "default" => "Sendgrid",
    "envFallback" => false,
  ],
  "EMAILS_PROVIDERS_SENDGRID_APIKEY" => [
    "form" => [
      "type" => "secret",
      "default" => function () {
        return "";
      },
      "name" => "SendGrid API key",
      "group" => "Email",
      "description" => "If Sengrid is selected above, the SendGrid API key to use to send emails",
      "required" => false,
      "maxlength" => 255,
      "minlength" => 0,
      "options" => [],
      "verifyMatch" => function ($value, $options) {
        $checkedValue = filter_var($value, FILTER_VALIDATE_REGEXP, ["options" => ["regexp" => "/^[a-zA-Z0-9_]+$/"]]);
        if ($checkedValue) return ["valid" => true, "value" => $checkedValue, "error" => null];
        else return ["valid" => false, "value" => null, "error" => "Invalid SendGrid API key"];
      }
    ],
    "specialRequest" => true,
    "default" => false,
    "envFallback" => "bCMS__SendGridAPIKEY",
  ],
  "EMAILS_FROMEMAIL" => [
    "form" => [
      "type" => "email",
      "default" => function () {
        return "";
      },
      "name" => "From email address",
      "group" => "Email",
      "description" => "The email address to send emails from",
      "required" => false,
      "maxlength" => 255,
      "minlength" => 0,
      "options" => [],
      "verifyMatch" => function ($value, $options) {
        $checkedValue = filter_var($value, FILTER_VALIDATE_EMAIL);
        if ($checkedValue) return ["valid" => true, "value" => $checkedValue, "error" => null];
        else return ["valid" => false, "value" => "", "error" => "Invalid email address"];
      }
    ],
    "specialRequest" => true,
    "default" => false,
    "envFallback" => "bCMS__FROM_EMAIL",
  ],
  "ERRORS_PROVIDERS_SENTRY" => [
    "form" => [
      "type" => "secret",
      "default" => function () {
        return "";
      },
      "name" => "Sentry.io API key",
      "group" => "Error Handling",
      "description" => "The Sentry.io API key to use to send errors",
      "required" => false,
      "maxlength" => 255,
      "minlength" => 0,
      "options" => [],
      "verifyMatch" => function ($value, $options) {
        $checkedValue = filter_var($value, FILTER_VALIDATE_REGEXP, ["options" => ["regexp" => "/^[a-zA-Z0-9_]+$/"]]);
        if ($checkedValue) return ["valid" => true, "value" => $checkedValue, "error" => null];
        else return ["valid" => false, "value" => null, "error" => "Invalid Sentry.io API key"];
      }
    ],
    "specialRequest" => false,
    "default" => null,
    "envFallback" => "bCMS__SENTRYLOGIN",
  ],
  "nextHash" => [
    "form" => [
      "type" => "select",
      "default" => function () {
        return "sha256";
      },
      "name" => "Next password hashing algorithm",
      "group" => "Security & Login",
      "description" => "The hashing algorithm to use for new passwords. Changing this will not require users to change their passwords, but it will change the hashing algorithm the next time a given user changes their password.",
      "required" => true,
      "maxlength" => 6,
      "minlength" => 6,
      "options" => ["sha256", "sha512"],
      "verifyMatch" => function ($value, $options) {
        return ["valid" => in_array($value, $options), "value" => $value, "error" => in_array($value, $options) ? '' : "Invalid hashing algorithm"];
      }
    ],
    "specialRequest" => false,
    "default" => "sha256",
    "envFallback" => false,
  ],
  "USERGUIDEURL" => [
    "form" => [
      "type" => "url",
      "default" => function () {
        return "https://adam-rms.com/docs/v1/user-guide/";
      },
      "name" => "User guide URL",
      "group" => "Customisation",
      "description" => "The URL of the user guide, which is linked to from the help buttons",
      "required" => true,
      "maxlength" => 255,
      "minlength" => 0,
      "options" => [],
      "verifyMatch" => function ($value, $options) {
        $checkedValue = filter_var($value, FILTER_VALIDATE_URL);
        if ($checkedValue) return ["valid" => true, "value" => $checkedValue, "error" => null];
        else return ["valid" => false, "value" => "", "error" => "Invalid URL"];
      }
    ],
    "specialRequest" => false,
    "default" => "https://adam-rms.com/docs/v1/user-guide/",
    "envFallback" => false,
  ],
  "SUPPORTURL" => [
    "form" => [
      "type" => "url",
      "default" => function () {
        return "https://adam-rms.com/support/";
      },
      "name" => "Support URL",
      "group" => "Customisation",
      "description" => "The URL for links to the support page",
      "required" => false,
      "maxlength" => 255,
      "minlength" => 0,
      "options" => [],
      "verifyMatch" => function ($value, $options) {
        $checkedValue = filter_var($value, FILTER_VALIDATE_URL);
        if ($checkedValue) return ["valid" => true, "value" => $checkedValue, "error" => null];
        else return ["valid" => false, "value" => "", "error" => "Invalid URL"];
      }
    ],
    "specialRequest" => false,
    "default" => "https://adam-rms.com/support/",
    "envFallback" => false,
  ],
  "TermsOfServiceURL" => [
    "form" => [
      "type" => "url",
      "default" => function () {
        return null;
      },
      "name" => "Terms of service URL",
      "group" => "Customisation",
      "description" => "The URL to the terms of service page. This is linked to from the login page. If this is not set, the link will not be shown.",
      "required" => false,
      "maxlength" => 255,
      "minlength" => 0,
      "options" => [],
      "verifyMatch" => function ($value, $options) {
        $checkedValue = filter_var($value, FILTER_VALIDATE_URL);
        if ($checkedValue) return ["valid" => true, "value" => $checkedValue, "error" => null];
        else return ["valid" => false, "value" => "", "error" => "Invalid URL"];
      }
    ],
    "specialRequest" => false,
    "default" => null,
    "envFallback" => "bCMS__TOS_URL",
  ],
  "JWTKey" => [
    "form" => [
      "type" => "secret",
      "default" => function () {
        $characters = 'ABCDEFGHKMNOPQRSTUVWXYZ0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < 64; $i++) {
          $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
      },
      "name" => "JWT Key",
      "group" => "Security & Login",
      "description" => "The JWT key to use for signing JWTs. This should be a random value that you keep secret of 64 characters. If you are setting up AdamRMS for the first time, then the default generated value will be fine. Changing this later will invalidate all existing JWTs.",
      "required" => true,
      "maxlength" => 64,
      "minlength" => 64,
      "options" => [],
      "verifyMatch" => function ($value, $options) {
        $checkedValue = filter_var($value, FILTER_VALIDATE_REGEXP, ["options" => ["regexp" => "/^[A-Z0-9]+$/"]]);
        if ($checkedValue) return ["valid" => true, "value" => $checkedValue, "error" => null];
        else return ["valid" => false, "value" => null, "error" => "Invalid JWT key"];
      }
    ],
    "specialRequest" => false,
    "default" => false,
    "envFallback" => "bCMS__JWT",
  ],
  "TIMEZONE" => [
    "form" => [
      "type" => "select",
      "default" => function () {
        return "Europe/London";
      },
      "name" => "Timezone",
      "group" => "General",
      "description" => "The timezone to use for AdamRMS",
      "required" => true,
      "maxlength" => 1000,
      "minlength" => 1,
      "options" => DateTimeZone::listIdentifiers(DateTimeZone::ALL),
      "verifyMatch" => function ($value, $options) {
        return ["valid" => in_array($value, $options), "value" => $value, "error" => in_array($value, $options) ? '' : "Invalid timezone"];
      }
    ],
    "specialRequest" => false,
    "default" => "Europe/London",
    "envFallback" => false,
  ],
];
