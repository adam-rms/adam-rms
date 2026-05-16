import { themes as prismThemes } from "prism-react-renderer";
import type { Config } from "@docusaurus/types";
import type * as Preset from "@docusaurus/preset-classic";
import fs from "node:fs";

const production = process.env.CONTEXT === "production"; // Cloudflare Workers set environment variable "CONTEXT" to "production"/"deploy-preview"
const apiSpecExists = fs.existsSync("./static/apiDocs.yaml");

// Ensure api-docs directory exists for the content-docs plugin
if (!fs.existsSync("./api-docs")) {
  fs.mkdirSync("./api-docs", { recursive: true });
}

// Microsoft Clarity project ID for adam-rms.com — hardcoded as this website is
// only ever deployed by the AdamRMS team, not by self-hosters.
const CLARITY_PROJECT_ID = "ree0pnhyh9";

const config: Config = {
  title: "AdamRMS",
  // Inject Clarity on production builds only so that development previews do not
  // pollute analytics data.
  headTags: production
    ? [
        {
          tagName: "script",
          attributes: { type: "text/javascript" },
          innerHTML: `(function(c,l,a,r,i,t,y){c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);})(window,document,"clarity","script","${CLARITY_PROJECT_ID}");`,
        },
      ]
    : [],
  tagline:
    "AdamRMS is a free, open source advanced Rental Management System for Theatre, AV & Broadcast",
  url: "https://adam-rms.com",
  baseUrl: "/",
  onBrokenLinks: "warn",
  noIndex: !production,
  favicon: "img/favicon.ico",
  markdown: {
    hooks: {
      onBrokenMarkdownLinks: "warn",
    },
  },
  organizationName: "adam-rms",
  projectName: "adam-rms",
  plugins: [
    "@docusaurus/plugin-ideal-image",
    "docusaurus-plugin-sass",
    ...(apiSpecExists
      ? [
          [
            "docusaurus-plugin-openapi-docs",
            {
              id: "openapi",
              docsPluginId: "api",
              config: {
                api: {
                  specPath: "./static/apiDocs.yaml",
                  outputDir: "api-docs",
                  sidebarOptions: {
                    groupPathsBy: "tag",
                  },
                },
              },
            },
          ],
        ]
      : []),
    [
      "@docusaurus/plugin-content-docs",
      {
        id: "api",
        path: "api-docs",
        routeBasePath: "api",
        sidebarPath: "./api-sidebars.ts",
        docItemComponent: "@theme/ApiItem",
      },
    ],
  ],
  themes: ["docusaurus-theme-openapi-docs"],
  themeConfig: {
    navbar: {
      title: "AdamRMS",
      logo: {
        alt: "The AdamRMS Logo",
        src: "img/logoicon.svg",
        srcDark: "img/logoicon-white.svg",
      },
      items: [
        {
          label: "Pricing",
          to: "/pricing",
        },
        {
          type: "doc",
          docId: "user-guide/intro",
          position: "left",
          label: "User Guide",
        },
        {
          label: "Support",
          to: "/support",
        },
        {
          type: "doc",
          docId: "hosting/intro",
          position: "left",
          label: "Self Hosting",
        },
        {
          type: "doc",
          docId: "contributor/intro",
          position: "left",
          label: "Contributing",
        },
        {
          href: "/api/adamrms-api",
          label: "API Documentation",
        },
        {
          label: "Environment",
          to: "/environment",
        },
        {
          href: "https://dash.adam-rms.com",
          label: "Login",
          position: "right",
        },
      ],
    },
    footer: {
      style: "dark",
      copyright: `Copyright © 2019-${new Date().getFullYear()} Bithell Studios Ltd. <a href="/legal">Terms</a>`,
    },
    prism: {
      theme: prismThemes.github,
      darkTheme: prismThemes.dracula,
    },
    image: "img/banner.jpg",
    ...(!production && {
      announcementBar: {
        id: "dev_build",
        content:
          'This is a draft version of our website, to view the current version please visit <a href="https://adam-rms.com/">adam-rms.com</a>',
        backgroundColor: "#fafbfc",
        textColor: "#091E42",
        isCloseable: false,
      },
    }),
    colorMode: {
      defaultMode: "light",
      disableSwitch: false,
      respectPrefersColorScheme: true,
    },
    ...(process.env.AGOLIA_API_KEY &&
      process.env.AGOLIA_INDEX &&
      process.env.AGOLIA_APP_ID && {
        algolia: {
          appId: process.env.AGOLIA_APP_ID,
          apiKey: process.env.AGOLIA_API_KEY,
          indexName: process.env.AGOLIA_INDEX,
          contextualSearch: true,
          searchParameters: {},
          disableUserPersonalization: true,
        },
      }),
  } satisfies Preset.ThemeConfig,
  presets: [
    [
      "@docusaurus/preset-classic",
      {
        docs: {
          sidebarPath: "./sidebars.ts",
          editUrl: "https://github.com/adam-rms/adam-rms/edit/main/website/",
          editCurrentVersion: true,
          showLastUpdateTime: true,
          showLastUpdateAuthor: true,
          versions: {
            current: {
              label: "v1",
              path: "v1",
            },
          },
        },
        blog: {
          showReadingTime: true,
        },
        theme: {
          customCss: "./src/css/custom.css",
        },
      } satisfies Preset.Options,
    ],
  ],
};

export default config;
