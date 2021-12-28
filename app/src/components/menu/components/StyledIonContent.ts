import styled from "styled-components";
import { IonContent } from "@ionic/react";

const StyledIonContent = styled(IonContent)`
  --background: var(--ion-item-background, var(--ion-background-color, #fff));

  ion-menu.md & {
    --padding-start: 8px;
    --padding-end: 8px;
    --padding-top: 20px;
    --padding-bottom: 20px;
  }

  ion-menu.ios & {
    --padding-bottom: 20px;
  }
`;

export default StyledIonContent;
