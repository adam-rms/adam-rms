import React from "react";
import Layout from "@theme/Layout";

import PricingTable from "../components/PricingTable";

export default function Home(): React.JSX.Element {
  return (
    <Layout
      title="Pricing"
      description="AdamRMS pricing: a paid hosted service, or free to self-host. Discounts are available for educational institutions."
    >
      <main>
        <PricingTable />
      </main>
    </Layout>
  );
}
