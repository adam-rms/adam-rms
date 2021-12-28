import {
  IonButton,
  IonCard,
  IonCardContent,
  IonCardHeader,
  IonCardSubtitle,
  IonCardTitle,
  IonCol,
  IonItem,
  IonRefresher,
  IonRefresherContent,
  IonRow,
} from "@ionic/react";
import { useContext, useEffect } from "react";
import { useParams } from "react-router";
import { ProjectDataContext } from "../../contexts/project/ProjectDataContext";
import Page from "../../components/Page";

const Project = () => {
  const { projectId } = useParams<{ projectId: string }>();
  const { projectData, refreshProjectData } = useContext(ProjectDataContext);

  const doRefresh = (event: CustomEvent) => {
    refreshProjectData(parseInt(projectId)).then(() => {
      event.detail.complete();
    });
  };

  //get individual project data
  useEffect(() => {
    refreshProjectData(parseInt(projectId));
  }, []);

  //Check project name
  let project_name = "AdamRMS Project";
  if (projectData.project && projectData.project.projects_name) {
    project_name = projectData.project.projects_name;
  }

  return (
    <Page title={project_name}>
      <IonRefresher slot="fixed" onIonRefresh={doRefresh}>
        <IonRefresherContent />
      </IonRefresher>

      {/* Project Data*/}
      <IonCard>
        <IonCardHeader>
          <IonCardTitle>
            {projectData.project.projects_description}
          </IonCardTitle>
        </IonCardHeader>
        <IonCardContent>
          <IonRow>
            <IonCol>
              <IonItem>
                <div className="container">
                  <IonCardSubtitle>Project Manager</IonCardSubtitle>
                  {projectData.project.projects_manager ? (
                    <IonCardTitle>
                      {projectData.project.users_name1}{" "}
                      {projectData.project.users_name2}
                    </IonCardTitle>
                  ) : (
                    <IonCardTitle>Unknown</IonCardTitle>
                  )}
                </div>
              </IonItem>
              {projectData.project.projectsTypes_config_client && (
                <IonItem>
                  <div className="container">
                    <IonCardSubtitle>Client</IonCardSubtitle>
                    {projectData.project.clients_id ? (
                      <IonCardTitle>
                        {projectData.project.clients_name}
                      </IonCardTitle>
                    ) : (
                      <IonCardTitle>Unknown</IonCardTitle>
                    )}
                  </div>
                </IonItem>
              )}
              {projectData.project.projectsTypes_config_venue && (
                <IonItem>
                  <div className="container">
                    <IonCardSubtitle>Venue</IonCardSubtitle>
                    {projectData.project.clients_id ? (
                      <IonCardTitle>
                        {projectData.project.locations_name}
                      </IonCardTitle>
                    ) : (
                      <IonCardTitle>Unknown</IonCardTitle>
                    )}
                  </div>
                </IonItem>
              )}
            </IonCol>
            <IonCol>
              <IonItem>
                <div className="container">
                  <IonCardSubtitle>Event Dates</IonCardSubtitle>
                  {projectData.project.projects_dates_use_start ? (
                    <IonCardTitle>
                      {projectData.project.projects_dates_use_start} -{" "}
                      {projectData.project.projects_dates_use_end}
                    </IonCardTitle>
                  ) : (
                    <IonCardTitle>Unknown</IonCardTitle>
                  )}
                </div>
              </IonItem>
              <IonItem>
                <div className="container">
                  <IonCardSubtitle>Dates assets in use</IonCardSubtitle>
                  {projectData.project.projects_dates_deliver_start ? (
                    <IonCardTitle>
                      {projectData.project.projects_dates_deliver_start} -{" "}
                      {projectData.project.projects_dates_deliver_end}
                    </IonCardTitle>
                  ) : (
                    <IonCardTitle>Unknown</IonCardTitle>
                  )}
                </div>
              </IonItem>
            </IonCol>
          </IonRow>
        </IonCardContent>
      </IonCard>

      <IonCard>
        <IonCardHeader>
          <IonCardTitle>Project Assets</IonCardTitle>
        </IonCardHeader>
        <IonCardContent>
          <IonButton
            routerLink={"/projects/" + projectId + "/assets"}
            expand="block"
          >
            View Project Assets
          </IonButton>
        </IonCardContent>
      </IonCard>

      <IonCard></IonCard>
    </Page>
  );
};

export default Project;
