import React from "react";
import styles from "./Video.module.css";

export default function Video(): React.JSX.Element {
  return (
    <section className={styles.video}>
      <div className="text--center  padding-horiz--md">
        <iframe
          width="840"
          height="472"
          src="https://www.youtube-nocookie.com/embed/iBvYVlspz3E?si=dlyASx9xPjkQgWiM"
          title="YouTube video player"
          frameBorder="0"
          allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
          referrerPolicy="strict-origin-when-cross-origin"
          allowFullScreen
        ></iframe>
      </div>
    </section>
  );
}
