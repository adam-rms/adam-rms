import { IonApp, IonRouterOutlet, IonSplitPane } from "@ionic/react";
import { IonReactRouter } from "@ionic/react-router";
import Menu from "./components/Menu/Menu";
import { Routes } from "./pages/Route";
import Contexts from "./contexts/Context";
import React from "react";

/* Core CSS required for Ionic components to work properly */
import "@ionic/react/css/core.css";

/* Basic CSS for apps built with Ionic */
import "@ionic/react/css/normalize.css";
import "@ionic/react/css/structure.css";
import "@ionic/react/css/typography.css";

/* Optional CSS utils that can be commented out */
import "@ionic/react/css/padding.css";
import "@ionic/react/css/float-elements.css";
import "@ionic/react/css/text-alignment.css";
import "@ionic/react/css/text-transformation.css";
import "@ionic/react/css/flex-utils.css";
import "@ionic/react/css/display.css";
import GlobalStyle from "./theme/GlobalStyle";
import IonicTheme from "./theme/IonicTheme";

/*Font Awesome */
import { library } from "@fortawesome/fontawesome-svg-core";
import { fas } from "@fortawesome/free-solid-svg-icons";
import { fab } from "@fortawesome/free-brands-svg-icons";
import { far } from "@fortawesome/free-regular-svg-icons";

//setup Font Awesome icons
library.add(fab, far, fas);

const App: React.FC = () => {
  return (
    <>
      <GlobalStyle />
      <IonicTheme />
      <IonApp>
        <Contexts>
          <IonReactRouter>
            <IonSplitPane contentId="main">
              <Menu />
              <IonRouterOutlet id="main">
                <Routes />
              </IonRouterOutlet>
            </IonSplitPane>
          </IonReactRouter>
        </Contexts>
      </IonApp>
    </>
  );
};

export default App;
