import { IonMenu, IonMenuToggle } from "@ionic/react";
import { useLocation } from "react-router-dom";
import {
  faCube,
  faList,
  IconDefinition,
} from "@fortawesome/free-solid-svg-icons";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import StyledIonContent from "./components/StyledIonContent";
import StyledIonList from "./components/StyledIonList";
import StyledIonItem from "./components/StyledIonItem";
import StyledIonLabel from "./components/StyledIonLabel";
import BrandImage from "./components/BrandImage";
import BrandContainer from "./components/BrandContainer";
import BrandText from "./components/BrandText";

interface AppPage {
  url: string;
  icon: IconDefinition;
  title: string;
}

//Add new pages to this array
const appPages: AppPage[] = [
  {
    title: "Assets",
    url: "/assets/",
    icon: faCube,
  },
  {
    title: "Projects",
    url: "/projects/",
    icon: faList,
  },
];

const Menu: React.FC = () => {
  const location = useLocation();

  return (
    <IonMenu contentId="main" type="overlay">
      <StyledIonContent>
        <BrandContainer>
          <BrandImage src="https://cdn.adam-rms.com/img/logoicon.png" />
          <BrandText>AdamRMS</BrandText>
        </BrandContainer>
        <StyledIonList id="adamRMS-menu-list">
          {appPages.map((appPage, index) => {
            return (
              <IonMenuToggle key={index} autoHide={false}>
                <StyledIonItem
                  className={
                    location.pathname === appPage.url ? "selected" : ""
                  }
                  routerLink={appPage.url}
                  routerDirection="none"
                  lines="none"
                  detail={false}
                >
                  <StyledIonLabel slot="start">
                    <FontAwesomeIcon icon={appPage.icon} size="2x" />
                  </StyledIonLabel>
                  <StyledIonLabel>{appPage.title}</StyledIonLabel>
                </StyledIonItem>
              </IonMenuToggle>
            );
          })}
        </StyledIonList>
      </StyledIonContent>
    </IonMenu>
  );
};

export default Menu;
