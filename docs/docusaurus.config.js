const lightCodeTheme = require('prism-react-renderer/themes/github');
const darkCodeTheme = require('prism-react-renderer/themes/dracula');

const production = process.env.CONTEXT === 'production'; //Netlify/Cloudflare Pages set environment variable "CONTEXT" to "production"/"deploy-preview"

/** @type {import('@docusaurus/types').DocusaurusConfig} */
module.exports = {
  title: 'AdamRMS',
  tagline: 'AdamRMS is a free, open source advanced Rental Management System for Theatre, AV & Broadcast',
  url: 'https://adam-rms.com',
  baseUrl: '/',
  onBrokenLinks: 'warn',
  noIndex: !production,
  onBrokenMarkdownLinks: 'warn',
  favicon: 'img/favicon.ico',
  organizationName: 'bstudios',
  projectName: 'adam-rms',
  plugins: ['@docusaurus/plugin-ideal-image'],
  themeConfig: {
    navbar: {
      title: 'AdamRMS',
      logo: {
        alt: 'The AdamRMS Logo',
        src: 'img/logoicon.svg',
        srcDark: 'img/logoicon-white.svg',
      },
      items: [
        {
          type: 'doc',
          docId: 'tutorial/intro',
          position: 'left',
          label: 'User Guide',
        },
        {
          type: 'doc',
          docId: 'api/intro',
          position: 'left',
          label: 'API Documentation',
        },
        { 
          to: '/blog',
          label: 'Updates & News',
          position: 'left'
        },
        {
          href: 'https://dash.adam-rms.com',
          label: 'Login',
          position: 'right',
        },
        {
          type: 'docsVersionDropdown',
          position: 'right',
        },
      ],
    },
    footer: {
      style: 'dark',
      links: [
        {
          title: 'Docs',
          items: [
            {
              label: 'User Guide',
              to: '/docs/tutorial/intro',
            },
            {
              label: 'API Documentation',
              to: '/docs/api/intro',
            },
            {
              label: 'Service Status',
              href: 'https://status.adam-rms.com/',
            }
          ],
        },
        {
          title: 'Repos',
          items: [
            {
              label: 'Dashboard & API',
              href: 'https://github.com/bstudios/adam-rms',
            },
            {
              label: 'Mobile App',
              href: 'https://github.com/bstudios/adam-rms-app',
            },
            {
              label: 'Deployment',
              href: 'https://github.com/bstudios/adam-rms-deployment',
            },
          ],
        },
        {
          title: 'More',
          items: [
            {
              label: 'Support',
              to: '/support',
            },
            {
              label: 'Privacy & Terms',
              to: '/legal',
            },
            {
              label: 'Bithell Studios Ltd',
              href: 'https://bithell.studio/',
            },
          ],
        },
      ],
      copyright: `Copyright © 2019-${new Date().getFullYear()} Bithell Studios Ltd.`,
    },
    prism: {
      theme: lightCodeTheme,
      darkTheme: darkCodeTheme,
    },
    image: 'img/banner.jpg',
    ...(!production) && { announcementBar: {
      id: 'dev_build', // Any value that will identify this message.
      content:
        'This is a draft version of our website, to view the current version please visit <a href="https://adam-rms.com/">adam-rms.com</a>',
      backgroundColor: '#fafbfc', // Defaults to `#fff`.
      textColor: '#091E42', // Defaults to `#000`.
      isCloseable: false
    } },
    colorMode: {
      defaultMode: 'light',
      disableSwitch: false,
      // using user system preferences, instead of the hardcoded defaultMode
      respectPrefersColorScheme: true,
    },
    ...(process.env.AGOLIA_API_KEY && process.env.AGOLIA_INDEX) && { algolia: {
      apiKey: process.env.AGOLIA_API_KEY,
      indexName: process.env.AGOLIA_INDEX,
      contextualSearch: true,
      searchParameters: {},
      disableUserPersonalization: true
    } },
  },
  presets: [
    [
      '@docusaurus/preset-classic',
      {
        docs: {
          sidebarPath: require.resolve('./sidebars.js'),
          editUrl: 'https://github.com/bstudios/adam-rms/edit/v1/docs/',
          includeCurrentVersion: !production,
          showLastUpdateTime: true,
          showLastUpdateAuthor: true,
        },
        blog: {
          showReadingTime: true
        },
        theme: {
          customCss: require.resolve('./src/css/custom.css'),
        },
      },
    ],
  ],
};
