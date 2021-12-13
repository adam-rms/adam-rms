import { IonContent, IonImg, IonItem, IonLabel, IonList, IonMenu, IonMenuToggle } from '@ionic/react';

import { useLocation } from 'react-router-dom';
import { faCube, IconDefinition } from "@fortawesome/free-solid-svg-icons";
import './Menu.css';
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";

interface AppPage {
  url: string;
  icon: IconDefinition;
  title: string;
}

const appPages: AppPage[] = [
  {
    title: 'Assets',
    url: '/assets/',
    icon: faCube,
  }
];

const Menu: React.FC = () => {
  const location = useLocation();

  return (
    <IonMenu contentId="main" type="overlay">
      <IonContent>
        <div className="brand-container">
          <IonImg class="brand-image" src="https://cdn.adam-rms.com/img/logoicon.png"></IonImg>
          <p className="brand-text">AdamRMS</p>
        </div>
        <IonList id="adamRMS-menu-list">
          {appPages.map((appPage, index) => {
            return (
              <IonMenuToggle key={index} autoHide={false}>
                <IonItem className={location.pathname === appPage.url ? 'selected' : ''} routerLink={appPage.url} routerDirection="none" lines="none" detail={false}>
                  <IonLabel slot="start"><FontAwesomeIcon icon={appPage.icon} size="2x" /></IonLabel>
                  <IonLabel>{appPage.title}</IonLabel>
                </IonItem>
              </IonMenuToggle>
            );
          })}
        </IonList>
      </IonContent>
    </IonMenu>
  );
};

export default Menu;
