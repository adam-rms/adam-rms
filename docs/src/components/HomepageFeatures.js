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
    description: 'Keep track of late-paying clients',
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
];

function Feature({image, title, description}) {
  return (
    <div className={clsx('col col--4')}>
      <div className="text--center">
        <Image img={image} />
      </div>
      <div className="text--center padding-horiz--md">
        <h3>{title}</h3>
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
