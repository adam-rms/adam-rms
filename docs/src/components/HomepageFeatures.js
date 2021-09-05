import React from 'react';
import clsx from 'clsx';
import styles from './HomepageFeatures.module.css';
import Image from '@theme/IdealImage';
const FeatureList = [
  {
    title: 'Asset Management',
    image: require('./../../static/img/features/assetManagement.png'),
    description: 'Keep track of assets across multiple sites and events, preventing clashing hires',
  },
  {
    title: 'Project Management',
    image: require('./../../static/img/features/projectManagement.png'),
    description: 'Track and manage crewing, assets, finance, files and invoices for events',
  },
  {
    title: 'Asset Details',
    image: require('./../../static/img/features/asset.png'),
    description: 'Manage every detail of an asset, with everything from barcodes to purchase receipts',
  },
  {
    title: 'Dashboard & Calendar',
    image: require('./../../static/img/features/calendarDash.png'),
    description: 'Keep track of a busy organizational schedule, as well as monitoring your key stats',
  },
  {
    title: 'Maintenance',
    image: require('./../../static/img/features/maintenance.png'),
    description: 'Raise, manage and track jobs for when things go wrong, as well as managing compliance such as PAT and LOLER',
  },
  {
    title: 'Ledger',
    image: require('./../../static/img/features/ledger.png'),
    description: 'Keep track of late-paying clients and issue highly-customizable and configurable invoices, including support for part-payment.',
  },
  {
    title: 'Permissions',
    image: require('./../../static/img/features/permissions.png'),
    description: 'Full security suite to allow granular control over which staff access which areas - perfect for when freelancers need access to certain elements of your instance',
  },
  {
    title: 'Categories & Groups',
    image: require('./../../static/img/features/customCategories.png'),
    description: 'Create and filter custom categories and groups of assets, including smart email notifications for groups you\'re keeping an eye on',
  },
  {
    title: 'Training',
    image: require('./../../static/img/features/training.jpg'),
    description: 'Training and certification suite to track employee accreditation and courses, including platform for creating interactive online modules and in-person courses as well as combining the two in hybrid training.',
  },
  {
    title: 'CMS',
    image: require('./../../static/img/features/cms-pages.jpg'),
    description: 'Innovative Content Management System integrated with permissions and visibility controls, as well as sub-pages and a WYSIWYG page designer.',
  },
  {
    title: 'Recruitment',
    image: require('./../../static/img/features/crew-recruitment.jpg'),
    description: 'Internal recruitment platform, allowing crew to self-assign to roles on a first-come-first-serve basis, or to answer questions to be considered for a role. Supports multiple people per role as well as CV file uploads and questionnaires.',
  },
  {
    title: 'Public Sites',
    image: require('./../../static/img/features/public-sites.jpg'),
    description: 'Highly configurable public facing websites to showcase your equipment stock, reducing enquiries, including optionally displaying availability and pricing. Also includes CMS integration to add custom pages and customize the site extensively. Custom white-label subdomains are supported with SSL/HTTPs',
  },
];

function Feature({image, title, description}) {
  return (
    <div className={clsx('col col--4')}>
      <div className="text--center">
        <Image style={{ "margin-bottom": 5 }} img={image} />
      </div>
      <div className="text--center padding-horiz--md">
        <h2>{title}</h2>
        <p>{description}</p>
      </div>
    </div>
  );
}

export default function HomepageFeatures() {
  return (
    <section className={styles.features}>
      <div className="container">
        <div className="row">
          {FeatureList.map((props, idx) => (
            <Feature key={idx} {...props} />
          ))}
        </div>
      </div>
    </section>
  );
}
