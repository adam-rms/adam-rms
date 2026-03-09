import React from "react";
import Layout from "@theme/Layout";

import useDocusaurusContext from "@docusaurus/useDocusaurusContext";
import PricingTable from "../components/PricingTable";

export default function Home() {
  const { siteConfig } = useDocusaurusContext();
  return (
    <Layout
      title={`${siteConfig.title} Pricing | Open Source Advanced Rental Management System for Theatre, AV & Broadcast`}
      description="AdamRMS is an advanced Rental Management System for Theatre, AV & Broadcast. Free & Open Source"
    >
      <main>
        <PricingTable />
      </main>
    </Layout>
  );
}
