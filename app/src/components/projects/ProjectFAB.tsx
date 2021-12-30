import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { IonFab, IonFabButton, IonFabList } from "@ionic/react";

const ProjectFab = () => {
  return (
    <IonFab vertical="bottom" horizontal="end" slot="fixed">
      <IonFabButton color="light">
        <FontAwesomeIcon icon="shopping-basket" />
      </IonFabButton>
      <IonFabList side="start">
        <IonFabButton routerLink="/scanner/">
          <FontAwesomeIcon icon="plus" />
        </IonFabButton>
      </IonFabList>
    </IonFab>
  );
};

export default ProjectFab;
