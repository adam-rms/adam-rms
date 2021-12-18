import styled from "styled-components";
import {IonNote} from "@ionic/react";

const StyledIonNote = styled(IonNote)`
  display: inline-block;
  font-size: 16px;
  color: var(--ion-color-medium-shade);
  
  ion-menu.md & {
    margin-bottom: 30px;
  }
  ion-menu.md ion-list-header, ion-menu.md & {
    padding-left: 10px;
  }
  ion-menu.ios & {
    line-height: 24px;
    margin-bottom: 20px;
  }
  
  ion-menu.ios & {
    padding-left: 16px;
    padding-right: 16px;
  }

  ion-menu.ios & {
    margin-bottom: 8px;
  }
`

export default StyledIonNote;
