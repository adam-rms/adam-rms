import { faQuestionCircle } from "@fortawesome/free-solid-svg-icons";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import {
  IonAvatar,
  IonCard,
  IonCardTitle,
  IonImg,
  IonInfiniteScroll,
  IonInfiniteScrollContent,
  IonItem,
  IonLabel,
  IonList,
  IonRefresher,
  IonRefresherContent,
} from "@ionic/react";
import styled from "styled-components";
import { useContext, useEffect } from "react";
import { AssetTypeContext } from "../../contexts/asset/AssetTypeContext";
import Page from "../../components/Page";

/**
 * Asset Type List Page
 * Lists all asset types for a business
 */
const AssetTypeList = () => {
  const { AssetTypes, refreshAssetTypes, getMoreAssets } =
    useContext(AssetTypeContext);

  function doRefresh(event: CustomEvent) {
    refreshAssetTypes().then(() => {
      event.detail.complete();
    });
  }

  function loadData(event: any) {
    getMoreAssets().then(() => {
      event.target.complete();
    });
  }

  //Get data from API
  useEffect(() => {
    refreshAssetTypes();
  }, []);

  if (AssetTypes) {
    return (
      <Page title="Asset List">
        <IonRefresher slot="fixed" onIonRefresh={doRefresh}>
          <IonRefresherContent />
        </IonRefresher>
        <IonCard>
          <IonList>
            {AssetTypes.assets.map((item: IAssetTypeData) => {
              return (
                <IonItem
                  key={item.assetTypes_id}
                  routerLink={"/assets/" + item.assetTypes_id}
                >
                  <ThumbnailContainer>
                    {item.thumbnails.length > 0 && (
                      <IonAvatar slot="start">
                        <IonImg
                          src={item.thumbnails[0].url}
                          alt={item.assetTypes_name}
                        />
                      </IonAvatar>
                    )}
                    {item.thumbnails.length == 0 && (
                      <FontAwesomeIcon icon={faQuestionCircle} size="2x" />
                    )}
                  </ThumbnailContainer>
                  <IonLabel>
                    <h2>{item.assetTypes_name}</h2>
                    <p>{item.assetCategories_name}</p>
                  </IonLabel>
                  <IonLabel slot="end">
                    <p>x{item.tags.length}</p>
                  </IonLabel>
                </IonItem>
              );
            })}
          </IonList>
          <IonInfiniteScroll onIonInfinite={loadData} threshold="100px">
            <IonInfiniteScrollContent
              loadingSpinner="bubbles"
              loadingText="Loading more assets..."
            />
          </IonInfiniteScroll>
        </IonCard>
      </Page>
    );
  } else {
    //If there isn't an asset, refresh context to see if that helps
    refreshAssetTypes();
    //If there is still no assets, it's probably a network issue
    return (
      <Page title="Asset List">
        <IonRefresher slot="fixed" onIonRefresh={doRefresh}>
          <IonRefresherContent />
        </IonRefresher>
        <IonCard>
          <IonCardTitle>No Assets found</IonCardTitle>
        </IonCard>
      </Page>
    );
  }
};

const ThumbnailContainer = styled.div`
  margin: 10px;
`;

export default AssetTypeList;
