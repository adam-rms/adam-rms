import styled from "styled-components";
import {IonListHeader} from "@ionic/react";

const StyledIonListHeader = styled(IonListHeader)`
  ion-menu.md ion-list#inbox-list & {
    font-size: 22px;
    font-weight: 600;
    min-height: 20px;

    ion-menu.ios & {
      padding-left: 16px;
      padding-right: 16px;
    }

  }

  ion-menu.md ion-list#labels-list & {
    font-size: 16px;
    margin-bottom: 18px;
    color: #757575;
    min-height: 26px;
  }
  ion-menu.ios ion-list#labels-list & {
    margin-bottom: 8px;
  }
`;

export default StyledIonListHeader
