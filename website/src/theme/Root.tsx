import React, { useState, useEffect, type ReactNode } from "react";
import styles from "./Root.module.css";

const CONSENT_STORAGE_KEY = "adam-rms-analytics-consent";

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
    setMounted(true);

    // Honour the Global Privacy Control signal for all visitors, regardless of
    // region, without prompting the user.
    if (
      (navigator as Navigator & { globalPrivacyControl?: boolean })
        .globalPrivacyControl === true
    ) {
      signalClarityConsent(false);
      setConsent("rejected");
      return;
    }

    const raw = localStorage.getItem(CONSENT_STORAGE_KEY);
    const stored: ConsentValue | null =
      raw === "accepted" || raw === "rejected" ? raw : null;
    if (stored) {
      // Replay the stored decision so Clarity can act on it for this page load.
      signalClarityConsent(stored === "accepted");
      setConsent(stored);
      return;
    }

    // No stored preference — ask Clarity whether consent is required for this
    // visitor. Clarity enforces consent mode by default for EEA, UK, and
    // Switzerland users. When active and no decision has been made yet, Clarity
    // reports analytics_storage as "DENIED". For all other regions (where
    // consent is not legally required) the status is absent/null, so we do not
    // show the banner.
    const w = window as Window & {
      clarity?: (cmd: string, ...args: unknown[]) => void;
    };
    if (typeof w.clarity !== "function") return;
    w.clarity(
      "metadata",
      (
        _data: unknown,
        _upgrade: unknown,
        consentStatus: { analytics_storage?: string } | null | undefined,
      ) => {
        if (consentStatus?.analytics_storage === "DENIED") {
          setNeedsBanner(true);
        }
      },
      false,
      true,
      true,
    );
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
