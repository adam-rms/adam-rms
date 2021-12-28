import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import {
  IonButton,
  IonCard,
  IonCardContent,
  IonCardHeader,
  IonCardSubtitle,
  IonCardTitle,
  IonCol,
  IonGrid,
  IonItem,
  IonLabel,
  IonList,
  IonRefresher,
  IonRefresherContent,
  IonRow,
  IonTitle,
} from "@ionic/react";
import { useContext } from "react";
import { useParams } from "react-router";
import AssetItem from "../../components/assets/AssetItem";
import Page from "../../components/Page";
import { ProjectDataContext } from "../../contexts/project/ProjectDataContext";
import { MassFormatter, MoneyFormatter } from "../../utilities/Formatters";

export interface IProjectAssets {
  assets: [IAssetTypeData];
  totals: {
    status: string;
    discountPrice: {
      amount: string;
      currency: string;
    };
    price: {
      amount: string;
      currency: string;
    };
    mass: number;
  };
}

const ProjectAssets = () => {
  const { projectId } = useParams<{ projectId: string }>();
  const { projectData, refreshProjectData } = useContext(ProjectDataContext);

  function doRefresh(event: CustomEvent) {
    refreshProjectData(parseInt(projectId));
    event.detail.complete();
  }

  //Generate Project Assets
  const assets: JSX.Element[] = [];
  if (
    projectData.FINANCIALS &&
    projectData.FINANCIALS.assetsAssigned &&
    Object.keys(projectData.FINANCIALS.assetsAssigned).length > 0
  ) {
    for (const [key, value] of Object.entries(
      projectData.FINANCIALS.assetsAssigned,
    )) {
      if (value) {
        const typedValue = value as IProjectAssets;
        //append list to main asset list
        assets.push(
          <IonCard key={key}>
            <IonCardHeader>
              <IonGrid>
                <IonRow>
                  <IonCol>
                    <IonCardTitle>
                      {typedValue.assets[0].assetTypes_name}
                    </IonCardTitle>
                  </IonCol>
                </IonRow>
                <IonRow>
                  <IonCol size="3">
                    <h3>Assets</h3>
                  </IonCol>
                  <IonCol size="2">
                    <h3>Mass</h3>
                  </IonCol>
                  <IonCol size="2">
                    <h3>Price</h3>
                  </IonCol>
                  <IonCol size="3">
                    <h3>Discounted Price</h3>
                  </IonCol>
                </IonRow>
              </IonGrid>
            </IonCardHeader>
            <IonCardContent>
              <IonList>
                <IonItem>
                  <IonGrid>
                    <IonRow>
                      <IonCol size="3">
                        <IonLabel>
                          {typedValue.assets.length} asset
                          {typedValue.assets.length > 1 ? "s" : ""}
                        </IonLabel>
                      </IonCol>
                      <IonCol size="2">
                        {MassFormatter(typedValue.totals.mass)}
                      </IonCol>
                      <IonCol size="2">
                        {MoneyFormatter(
                          typedValue.totals.price.currency,
                          typedValue.totals.price.amount,
                        )}
                      </IonCol>
                      <IonCol size="2">
                        {MoneyFormatter(
                          typedValue.totals.discountPrice.currency,
                          typedValue.totals.discountPrice.amount,
                        )}
                      </IonCol>
                      <IonCol size="3">
                        <IonButton
                          routerLink={"/assets/" + key}
                          className="ion-margin-end ion-float-end"
                        >
                          View Asset
                          <FontAwesomeIcon
                            icon="arrow-right"
                            className="ion-margin-start ion-float-end"
                          />
                        </IonButton>
                      </IonCol>
                    </IonRow>
                  </IonGrid>
                </IonItem>
                {
                  //generate list of individual assets
                  typedValue.assets.map((item: any) => {
                    return (
                      <AssetItem
                        key={item.assets_id}
                        AssetTypeId={key}
                        item={item}
                      />
                    );
                  })
                }
              </IonList>
            </IonCardContent>
          </IonCard>,
        );
      }
    }
  } else {
    //If there are no assets, refresh context to see if they've not been fetched yet
    refreshProjectData(parseInt(projectId));
    //If there are still no assets, there actually aren't any
    assets.push(
      <IonCard key="NoAssets">
        <IonCardHeader>
          <IonCardTitle>No Assets Assigned to this Project</IonCardTitle>
        </IonCardHeader>
      </IonCard>,
    );
  }

  return (
    <Page title="Project Assets">
      <IonRefresher slot="fixed" onIonRefresh={doRefresh}>
        <IonRefresherContent />
      </IonRefresher>
      {assets}
    </Page>
  );
};

export default ProjectAssets;
