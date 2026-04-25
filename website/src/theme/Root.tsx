import React, { useState, useEffect, type ReactNode } from "react";
import styles from "./Root.module.css";

const CONSENT_STORAGE_KEY = "adam-rms-analytics-consent";

// IANA timezone identifiers for the EEA, UK, and Switzerland where GDPR /
// UK GDPR / nFADP requires explicit opt-in before analytics cookies are set.
// Falls back to showing the banner if timezone detection fails.
const EEA_UK_SWISS_TIMEZONES = new Set([
  // Austria
  "Europe/Vienna",
  // Belgium
  "Europe/Brussels",
  // Bulgaria
  "Europe/Sofia",
  // Cyprus
  "Europe/Nicosia", "Asia/Nicosia",
  // Czech Republic
  "Europe/Prague",
  // Denmark
  "Europe/Copenhagen",
  // Estonia
  "Europe/Tallinn",
  // Finland (incl. Åland Islands)
  "Europe/Helsinki", "Europe/Mariehamn",
  // France
  "Europe/Paris",
  // Germany
  "Europe/Berlin", "Europe/Busingen",
  // Greece
  "Europe/Athens",
  // Hungary
  "Europe/Budapest",
  // Iceland (EEA non-EU)
  "Atlantic/Reykjavik",
  // Ireland
  "Europe/Dublin",
  // Italy
  "Europe/Rome",
  // Latvia
  "Europe/Riga",
  // Liechtenstein (EEA non-EU)
  "Europe/Vaduz",
  // Lithuania
  "Europe/Vilnius",
  // Luxembourg
  "Europe/Luxembourg",
  // Malta
  "Europe/Malta",
  // Netherlands
  "Europe/Amsterdam",
  // Norway (EEA non-EU) + Svalbard
  "Europe/Oslo", "Arctic/Longyearbyen",
  // Poland
  "Europe/Warsaw",
  // Portugal (incl. Azores & Madeira)
  "Europe/Lisbon", "Atlantic/Azores", "Atlantic/Madeira",
  // Romania
  "Europe/Bucharest",
  // Slovakia
  "Europe/Bratislava",
  // Slovenia
  "Europe/Ljubljana",
  // Spain (incl. Ceuta & Canary Islands)
  "Europe/Madrid", "Africa/Ceuta", "Atlantic/Canary",
  // Sweden
  "Europe/Stockholm",
  // Switzerland (nFADP)
  "Europe/Zurich",
  // United Kingdom (UK GDPR)
  "Europe/London",
  // Faroe Islands (Denmark)
  "Atlantic/Faroe",
]);

// Returns true when the visitor's browser timezone suggests they are in a
// jurisdiction where explicit cookie consent is legally required.
// Errs on the side of showing the banner when detection fails.
function requiresExplicitConsent(): boolean {
  try {
    const tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
    return EEA_UK_SWISS_TIMEZONES.has(tz);
  } catch {
    return true;
  }
}

type ConsentValue = "accepted" | "rejected";

// Clarity Consent API v2 — informs Clarity of the user's decision.
// Clarity is already loaded via headTags; this call just signals consent status.
function signalClarityConsent(granted: boolean): void {
  if (typeof window === "undefined") return;
  const w = window as Window & {
    clarity?: (cmd: string, payload: Record<string, string>) => void;
  };
  if (typeof w.clarity !== "function") return;
  const value = granted ? "granted" : "denied";
  w.clarity("consentv2", {
    ad_Storage: value,
    analytics_Storage: value,
  });
}

export default function Root({ children }: { children: ReactNode }): JSX.Element {
  // Use `null` as "not yet determined" so the banner never renders during SSR
  // (which would cause a React hydration mismatch).
  const [mounted, setMounted] = useState(false);
  const [consent, setConsent] = useState<ConsentValue | null>(null);
  const [needsBanner, setNeedsBanner] = useState(false);

  useEffect(() => {
    // Honour the Global Privacy Control signal for all visitors, regardless of
    // region, without prompting the user.
    if (
      (navigator as Navigator & { globalPrivacyControl?: boolean })
        .globalPrivacyControl === true
    ) {
      signalClarityConsent(false);
      setConsent("rejected");
      setMounted(true);
      return;
    }

    // The consent banner is only required for visitors from the EEA, UK, or
    // Switzerland. For all other regions Clarity operates under its default
    // behaviour and no banner is shown.
    if (!requiresExplicitConsent()) {
      setMounted(true);
      return;
    }

    setNeedsBanner(true);

    const raw = localStorage.getItem(CONSENT_STORAGE_KEY);
    const stored: ConsentValue | null =
      raw === "accepted" || raw === "rejected" ? raw : null;
    if (stored === "accepted" || stored === "rejected") {
      // Replay the stored decision so Clarity can act on it for this page load.
      signalClarityConsent(stored === "accepted");
      setConsent(stored);
    }
    // If stored is null the consent state stays null → banner is shown.

    setMounted(true);
  }, []);

  const handleAccept = () => {
    localStorage.setItem(CONSENT_STORAGE_KEY, "accepted");
    setConsent("accepted");
    signalClarityConsent(true);
  };

  const handleReject = () => {
    localStorage.setItem(CONSENT_STORAGE_KEY, "rejected");
    setConsent("rejected");
    signalClarityConsent(false);
  };

  return (
    <>
      {children}
      {mounted && needsBanner && consent === null && (
        <div className={styles.banner} role="dialog" aria-label="Cookie consent">
          <p className={styles.text}>
            We use <strong>Microsoft Clarity</strong> (analytics &amp; session
            recordings) to improve our site. You can accept or reject this use of
            analytics cookies.{" "}
            <a href="/legal#microsoft-clarity-analytics--session-recording">
              Learn more
            </a>
            .
          </p>
          <div className={styles.buttons}>
            <button
              className={styles.reject}
              onClick={handleReject}
              type="button"
            >
              Reject
            </button>
            <button
              className={styles.accept}
              onClick={handleAccept}
              type="button"
            >
              Accept
            </button>
          </div>
        </div>
      )}
    </>
  );
}
