import styled from "styled-components";
import {IonList} from "@ionic/react";

const StyledIonList = styled(IonList)`
  ion-menu.md & {
    padding: 20px 0;
  }
  ion-menu.md &#inbox-list {
    border-bottom: 1px solid var(--ion-color-step-150, #d7d8da);
  }
  ion-menu.ios & {
    padding: 20px 0 0 0;
  }
`;

export default StyledIonList;
