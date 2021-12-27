import { faArrowRight, faStar } from "@fortawesome/free-solid-svg-icons";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import {
  IonCard,
  IonItem,
  IonLabel,
  IonList,
  IonRefresher,
  IonRefresherContent,
  IonTitle,
} from "@ionic/react";
import { useContext, useEffect } from "react";
import { ProjectContext } from "../../contexts/project/ProjectContext";
import Page from "../../components/Page";

/**
 * Project List Page
 * Lists all projects for a business
 */
const ProjectList = () => {
  const { projects, refreshProjects } = useContext(ProjectContext);

  const doRefresh = (event: CustomEvent) => {
    refreshProjects().then(() => {
      event.detail.complete();
    });
  };

  //Get data from API
  useEffect(() => {
    refreshProjects();
  }, []);

  //generate project list if there are projects
  let projectList;
  if (projects) {
    projectList = projects.map((item: IProject) => {
      return (
        <IonItem
          key={item.projects_id}
          routerLink={"/projects/" + item.projects_id}
        >
          <div slot="start">
            {item.thisProjectManager && (
              <FontAwesomeIcon icon="star" size="lg" />
            )}
            {!item.thisProjectManager && (
              <FontAwesomeIcon icon={["far", "circle"]} size="lg" />
            )}
          </div>
          <IonLabel>
            <h2>{item.projects_name}</h2>
            <p>{item.clients_name}</p>
          </IonLabel>
          <div slot="end">
            <FontAwesomeIcon icon="arrow-right" />
          </div>
        </IonItem>
      );
    });
  } else {
    projectList = (
      <IonItem>
        <IonTitle>No Projects Found</IonTitle>
      </IonItem>
    );
  }

  return (
    <Page title="Project List">
      <IonRefresher slot="fixed" onIonRefresh={doRefresh}>
        <IonRefresherContent />
      </IonRefresher>
      <IonCard>
        <IonList>{projectList}</IonList>
      </IonCard>
    </Page>
  );
};

export default ProjectList;
