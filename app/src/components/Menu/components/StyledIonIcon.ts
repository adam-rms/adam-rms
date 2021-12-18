import styled from "styled-components";
import {IonIcon} from "@ionic/react";

const StyledIonIcon = styled(IonIcon)`
  ion-menu.md ion-item.selected & {
    color: var(--ion-color-primary);
  }

  ion-menu.md ion-item & {
    color: #616e7e;
  }

  ion-menu.ios ion-item & {
    font-size: 24px;
    color: #73849a;
  }

  ion-menu.ios ion-item .selected & {
    color: var(--ion-color-primary);
  }
`;

export default StyledIonIcon;
