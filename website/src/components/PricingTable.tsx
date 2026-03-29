import React, {useEffect, useState} from "react";
import clsx from "clsx";
import Link from "@docusaurus/Link";
import styles from "./PricingTable.module.css";

interface PriceAmount {
  currency: string;
  formatted_amount: string;
}

interface MarketingFeature {
  name: string;
}

interface PriceData {
  name: string;
  description: string;
  price: PriceAmount[];
  marketing_features: MarketingFeature[];
}

interface ApiResponse {
  result: boolean;
  response: PriceData[];
}

function Price({price}: {price: PriceData}): React.JSX.Element {
  return (
    <div className={clsx("col col--4")} style={{padding: "1rem"}}>
      <div className={styles.feature}>
        <div className="text--center padding-horiz--md">
          <h1>{price.name}</h1>

          {price.price.map((p) => (
            <h2 key={p.currency} style={{margin: 0}}>
              {p.formatted_amount} / month
              <br />
            </h2>
          ))}

          <p>{price.description}</p>
          <Link
            style={{marginBottom: "1rem"}}
            className="button button--secondary"
            href="https://dash.adam-rms.com"
          >
            Start Trial
          </Link>
        </div>
        <div className="padding-horiz--md">
          <ul>
            {price.marketing_features.map((feature, idx) => (
              <li key={idx}>{feature.name}</li>
            ))}
          </ul>
        </div>
      </div>
    </div>
  );
}

export default function PricingTable(): React.JSX.Element {
  const [prices, setPrices] = useState<PriceData[]>([]);
  useEffect(() => {
    fetch("https://dash.adam-rms.com/api/instances/billing/getPrices.php")
      .then((res) => {
        return res.json();
      })
      .then((data: ApiResponse) => {
        if (data.result && data.response.length > 0) {
          setPrices(data.response);
        }
      });
  }, []);
  return (
    <section
      className={[
        clsx("hero hero--primary", styles.heroBanner),
        styles.features,
      ].join(" ")}
    >
      <div className="container">
        <div className="row text--center">
          <div className="col col--12">
            <h1 className="hero__title">Pricing</h1>
            <p className="hero__subtitle">
              AdamRMS is currently offered as a paid hosted solution and as a{" "}
              <Link to="/docs/v1/hosting/intro">free self-hosted solution</Link>.
              We are able to offer discounts to educational institutions -
              sign-up for a trial and{" "}
              <Link to="/support">get in touch for a quote</Link>.
            </p>
          </div>
        </div>
        {prices.length === 0 ? (
          <p className="hero__subtitle">
            Loading latest pricing information...
          </p>
        ) : (
          <div className="row">
            {prices.map((price, idx) => (
              <Price key={idx} price={price} />
            ))}
          </div>
        )}
        <div className="row">
          <div className="col col--12">
            <p className="hero__subtitle text--right">
              Pricing is charged per business, not per user. Need More of
              anything? <Link to="/support">Contact us for a quote</Link>
            </p>
          </div>
        </div>
      </div>
    </section>
  );
}
