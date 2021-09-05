import React from 'react';
import clsx from 'clsx';
import Layout from '@theme/Layout';
import Link from '@docusaurus/Link';
import useDocusaurusContext from '@docusaurus/useDocusaurusContext';
import styles from './index.module.css';
import HomepageFeatures from '../components/HomepageFeatures';
import Image from '@theme/IdealImage';

import GooglePlayBadge from './../../static/img/storeIcons/google-play-badge.png';
import AppStoreBadge from './../../static/img/storeIcons/Download_on_the_App_Store_Badge_US-UK_RGB_blk_092917.png';


function HomepageHeader() {
  const {siteConfig} = useDocusaurusContext();
  return (
    <header className={clsx('hero hero--primary', styles.heroBanner)}>
      <div className="container">
        <h1 className="hero__title">{siteConfig.title}</h1>
        <p className="hero__subtitle">{siteConfig.tagline}</p>
        <div className={styles.buttons}>
          <Link
            href="https://play.google.com/store/apps/details?id=com.bstudios.adamrms&utm_source=webdashboard&utm_campaign=dashboardwidget&pcampaignid=pcampaignidMKT-Other-global-all-co-prtnr-py-PartBadge-Mar2515-1">
            <Image img={GooglePlayBadge} style={{ width: 150 }} />
          </Link>
          <Link
            href="https://apps.apple.com/us/app/id1519443182?utm_source=webdashboard&utm_campaign=dashboardwidget">
            <Image img={AppStoreBadge} style={{ width: 135 }}  />
          </Link>
        </div>
      </div>
    </header>
  );
}

function Pricing() {
  return (
    <header className={clsx('hero hero--primary', styles.heroBanner)}>
      <div className="container">
        <h1 className="hero__title">Pricing</h1>
        <p className="hero__subtitle">AdamRMS is currently offered as a cost-price hosted solution, by Bithell Studios Ltd, and as a free self-hosted solution using the code published on Github. Please note that the hosted solution is not currently taking on new customers.</p>
        <div className={styles.buttons}>
          <Link
            className="button button--secondary button--lg"
            href="https://github.com/">
            GitHub Repo
          </Link>
        </div>
      </div>
    </header>
  );
}


export default function Home() {
  const {siteConfig} = useDocusaurusContext();
  return (
    <Layout
      title={`${siteConfig.title} | Open Source Advanced Rental Management System for Theatre, AV & Broadcast`}
      description="AdamRMS is an advanced Rental Management System for Theatre, AV & Broadcast. Free & Open Source">
      <HomepageHeader />
      <main>
        <HomepageFeatures />
      </main>
      <Pricing />
    </Layout>
  );
}
